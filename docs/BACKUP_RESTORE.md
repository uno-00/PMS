# Backup and Restore Guide

## 1. What needs to be backed up

| Component | Contents | Backup mechanism |
|---|---|---|
| MySQL / RDS database | All transactional data, audit trail, workflow history | RDS automated snapshots + `mysqldump` exports |
| S3 document bucket | Uploaded GAA files, PPMP/APP/PR/CAF/BAC/PhilGEPS/bid/award/NTP/PO attachments | S3 versioning (built-in) + cross-region replication (optional) |
| Application `.env` / secrets | Config, credentials | AWS Secrets Manager / SSM (versioned automatically) |

## 2. Database backups

### 2.1 Managed (RDS) — recommended for production

RDS automated backups are enabled by `--backup-retention-period 14` at
instance creation (see `docs/AWS_DEPLOYMENT.md` §4). This gives:

- Continuous incremental backups with point-in-time restore to any second
  within the retention window.
- Automatic daily snapshots retained for 14 days (raise to 35 for stricter
  COA requirements).

Take a **manual snapshot** before every schema-changing release:

```bash
aws rds create-db-snapshot \
  --db-instance-identifier pms-procurement \
  --db-snapshot-identifier pms-pre-release-$(date +%Y%m%d%H%M)
```

### 2.2 Logical backups (self-managed MySQL / Docker)

A scheduled dump, uploaded to S3 with its own lifecycle/retention:

```bash
#!/bin/sh
# docker/scripts/backup-db.sh — run via cron or the "scheduler" container
STAMP=$(date +%Y%m%d-%H%M%S)
docker compose exec -T mysql mysqldump \
  -u root -p"$DB_ROOT_PASSWORD" --single-transaction --routines --triggers \
  "$DB_DATABASE" | gzip > "/backups/pms-${STAMP}.sql.gz"

aws s3 cp "/backups/pms-${STAMP}.sql.gz" \
  "s3://pms-procurement-backups/database/pms-${STAMP}.sql.gz"
```

The **Settings > Database Backup** tab persists the schedule/target
configuration (`SystemSetting` group `backup`) so this can also be driven
from within the app by wiring a scheduled command that shells out to the
script above using those settings, without a code deploy to change the
schedule.

### 2.3 Restore

```bash
# RDS point-in-time restore
aws rds restore-db-instance-to-point-in-time \
  --source-db-instance-identifier pms-procurement \
  --target-db-instance-identifier pms-procurement-restored \
  --restore-time 2026-07-01T03:15:00Z

# Logical restore (self-managed)
gunzip < pms-20260701-031500.sql.gz | \
  docker compose exec -T mysql mysql -u root -p"$DB_ROOT_PASSWORD" "$DB_DATABASE"
```

After any restore, run `php artisan migrate --force` to apply any
migrations that postdate the snapshot, then spot-check
`workflow_histories` and `activity_log` counts against the pre-incident
baseline to confirm audit-trail continuity.

## 3. Document (S3) backups

- **Versioning** is enabled on the bucket (`docs/AWS_DEPLOYMENT.md` §2.1),
  so every overwrite/delete is recoverable:

  ```bash
  aws s3api list-object-versions --bucket pms-procurement-documents --prefix pr/
  aws s3api get-object --bucket pms-procurement-documents --key pr/<uuid>/<file> \
    --version-id <version-id> restored-file
  ```

- For disaster recovery, enable **Cross-Region Replication (CRR)** to a
  secondary region bucket with the same lifecycle policy.
- The `NoncurrentVersionExpiration` lifecycle rule (§2.3 of the AWS guide)
  caps how long deleted/overwritten versions are retained (90 days by
  default) — tune to your agency's records retention schedule.

## 4. Local/Docker volume backups

If not using S3 in a given environment, the named Docker volumes
(`mysql-data`, `storage-data`) can be snapshotted directly:

```bash
docker run --rm -v pms-procurement_mysql-data:/data -v "$PWD":/backup \
  alpine tar czf /backup/mysql-data-$(date +%Y%m%d).tar.gz -C /data .
```

## 5. Restore drill checklist

Run this at least quarterly:

1. Restore the latest snapshot into a scratch RDS instance / scratch DB.
2. Point a disposable copy of the app at it (`.env.testing`-style overrides).
3. Run `php artisan migrate --force` and `php artisan test`.
4. Verify: can log in as `superadmin@pms.gov.ph`, can open a Purchase
   Order, `Reports & Analytics` renders, `Audit Trail` shows historical
   entries.
5. Tear down the scratch instance. Record the drill date/result in your
   agency's ISMS/COA compliance log.
