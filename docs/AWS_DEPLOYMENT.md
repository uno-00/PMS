# AWS Production Deployment Guide

This guide covers a straightforward, low-ops production topology suitable
for a single government agency deployment. It assumes the Docker image
described in `docs/DOCKER.md`.

## 1. Reference architecture

```
Route 53 (agency.gov.ph)
   │
   ▼
Application Load Balancer (HTTPS, ACM cert)
   │
   ▼
ECS Fargate service "pms-app"  ──┐  ECS Fargate service "pms-queue"
  (php-fpm + nginx sidecar,      │    (php artisan queue:work)
   auto-scaled 2-6 tasks)        │
   │                             │  ECS Fargate service "pms-scheduler"
   ▼                             │    (schedule:run loop, 1 task only)
RDS for MySQL 8.0 (Multi-AZ)  ◀──┘
   │
   ▼
S3 bucket "pms-procurement-documents" (private, versioned, SSE-KMS)
   │
ElastiCache Redis (cache/queue, optional but recommended at scale)
```

## 2. IAM & S3 setup

### 2.1 Create the private, versioned bucket

```bash
aws s3api create-bucket --bucket pms-procurement-documents \
  --region ap-southeast-1 \
  --create-bucket-configuration LocationConstraint=ap-southeast-1

aws s3api put-bucket-versioning --bucket pms-procurement-documents \
  --versioning-configuration Status=Enabled

aws s3api put-public-access-block --bucket pms-procurement-documents \
  --public-access-block-configuration \
  BlockPublicAcls=true,IgnorePublicAcls=true,BlockPublicPolicy=true,RestrictPublicBuckets=true

aws s3api put-bucket-encryption --bucket pms-procurement-documents \
  --server-side-encryption-configuration '{
    "Rules": [{"ApplyServerSideEncryptionByDefault": {"SSEAlgorithm": "AES256"}}]
  }'
```

### 2.2 Folder layout (created lazily by `DocumentStorageService`)

```
/fiscal-year/  /ppmp/  /app/  /pr/  /caf/  /bac/
/philgeps/     /bids/  /award/  /ntp/  /purchase-order/  /reports/
```

Each upload is stored as `{category}/{model-uuid}/{uuid}-{original-name}`
with metadata (`uploaded_by`, `document_category`, `original_filename`)
attached via `putObject` `Metadata`, and served exclusively through
15-minute (configurable) signed URLs — see `config/filesystems.php`
(`document_signed_url_minutes`).

### 2.3 Lifecycle rule (retention policy)

```json
{
  "Rules": [
    {
      "ID": "pms-retention",
      "Status": "Enabled",
      "Filter": {},
      "Transitions": [
        { "Days": 365, "StorageClass": "STANDARD_IA" },
        { "Days": 1825, "StorageClass": "GLACIER" }
      ],
      "NoncurrentVersionExpiration": { "NoncurrentDays": 90 }
    }
  ]
}
```

Apply with `aws s3api put-bucket-lifecycle-configuration --bucket pms-procurement-documents --lifecycle-configuration file://lifecycle.json`.
Government records retention (COA circulars) typically require 10 years for
financial documents; adjust the `Days` above to match your agency's records
disposition schedule — this is intentionally not hardcoded shorter than that.

### 2.4 IAM policy for the application's access key

Attach to a dedicated IAM user/role (`pms-app-runtime`), **not** an admin key:

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "PmsDocumentBucketAccess",
      "Effect": "Allow",
      "Action": [
        "s3:PutObject", "s3:GetObject", "s3:DeleteObject",
        "s3:ListBucket", "s3:GetObjectVersion"
      ],
      "Resource": [
        "arn:aws:s3:::pms-procurement-documents",
        "arn:aws:s3:::pms-procurement-documents/*"
      ]
    }
  ]
}
```

On ECS, prefer an **IAM Task Role** over static `AWS_ACCESS_KEY_ID`/`AWS_SECRET_ACCESS_KEY`
credentials in `.env` — the AWS SDK used by `league/flysystem-aws-s3-v3`
picks up the task role automatically when the keys are left blank.

## 3. `.env` (production)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://procurement.agency.gov.ph

DB_CONNECTION=mysql
DB_HOST=<rds-endpoint>
DB_DATABASE=pms_procurement
DB_USERNAME=pms_app
DB_PASSWORD=<secrets-manager>

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=<elasticache-endpoint>

DOCUMENTS_DISK_DRIVER=s3
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=pms-procurement-documents
AWS_SSE=AES256
# Leave AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY blank when using an ECS Task Role.

MAIL_MAILER=smtp
MAIL_HOST=email-smtp.ap-southeast-1.amazonaws.com   # SES SMTP endpoint
MAIL_PORT=587
```

Store all secrets in **AWS Secrets Manager** or **SSM Parameter Store** and
inject them as ECS task-definition secrets — never bake them into the image.

## 4. Database (RDS)

```bash
aws rds create-db-instance \
  --db-instance-identifier pms-procurement \
  --engine mysql --engine-version 8.0 \
  --db-instance-class db.t3.medium \
  --allocated-storage 100 --storage-type gp3 \
  --multi-az --backup-retention-period 14 \
  --master-username pms_admin --manage-master-user-password
```

- Enable **automated backups** (14+ day retention) and take a manual
  snapshot before every major release.
- Enable **Enhanced Monitoring** and **Performance Insights** for COA
  audit-readiness and query tuning.

## 5. Deploying the app (ECS Fargate)

1. Push the image built from the repo `Dockerfile` to ECR:

   ```bash
   aws ecr create-repository --repository-name pms-procurement
   docker build -t pms-procurement .
   docker tag pms-procurement:latest <account>.dkr.ecr.ap-southeast-1.amazonaws.com/pms-procurement:latest
   docker push <account>.dkr.ecr.ap-southeast-1.amazonaws.com/pms-procurement:latest
   ```

2. Create three ECS services from that same image, differing only by the
   `CONTAINER_ROLE` environment variable and desired task count:
   - `pms-app` (`CONTAINER_ROLE=app`, behind the ALB, target group health
     check on `GET /up`), auto-scaled on CPU/RPS, 2-6 tasks.
   - `pms-queue` (`CONTAINER_ROLE=queue`), 1-4 tasks, scaled on
     `ApproximateNumberOfMessagesVisible` (if using SQS) or CPU.
   - `pms-scheduler` (`CONTAINER_ROLE=scheduler`), **exactly 1 task** — do
     not scale this one, or scheduled jobs will run multiple times.
3. Point Route 53 + ACM-issued TLS certificate at the ALB.
4. Run the one-time bootstrap (first deploy only) or rely on the
   `app` container's own `migrate --force` on boot (see `docker/entrypoint.sh`).

## 6. Zero-downtime releases

Because `migrate --force` runs automatically on `app` container boot, ECS
rolling deployments are safe as long as migrations are backward-compatible
with the previous task's code (standard "expand/contract" migration
discipline — add nullable columns first, backfill, then drop old columns in
a follow-up release).

## 7. Observability

- Ship container logs to CloudWatch Logs (`awslogs` driver in the task
  definition).
- `php artisan pail` is dev-only; in production rely on `LOG_CHANNEL=stack`
  → CloudWatch, plus RDS/ALB/Target Group CloudWatch metrics and alarms
  (5xx rate, DB CPU, queue depth).
- See `docs/BACKUP_RESTORE.md` for the backup/restore runbook and
  `docs/SECURITY.md` for the full security posture.
