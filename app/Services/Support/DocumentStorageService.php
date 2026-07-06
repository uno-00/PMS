<?php

namespace App\Services\Support;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Single choke point for every file upload in the system. Centralizing
 * storage here means every module (PPMP, APP, PR, CAF, BAC, PhilGEPS,
 * bids, awards, NTP, purchase orders, reports) gets the same folder
 * convention, versioning, checksum, and signed-URL behaviour for free,
 * whether the underlying disk is local (dev) or S3 (staging/production).
 */
class DocumentStorageService
{
    public function disk(): string
    {
        return config('filesystems.documents_disk', 'documents');
    }

    /**
     * Store an uploaded file against a documentable model, automatically
     * incrementing the version number within its category.
     */
    public function store(
        UploadedFile $file,
        Model $documentable,
        string $module,
        string $category = 'attachment',
        ?User $uploader = null,
        array $metadata = []
    ): Document {
        $nextVersion = 1 + (int) Document::query()
            ->where('documentable_type', $documentable->getMorphClass())
            ->where('documentable_id', $documentable->getKey())
            ->where('category', $category)
            ->max('version');

        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = sprintf('v%d_%s_%s.%s', $nextVersion, $category, $safeName ?: Str::random(8), $extension);

        $path = sprintf('%s/%s/%s', trim($module, '/'), $documentable->getKey(), $filename);

        Storage::disk($this->disk())->putFileAs(
            dirname($path),
            $file,
            basename($path),
            ['visibility' => 'private']
        );

        return Document::query()->create([
            'documentable_type' => $documentable->getMorphClass(),
            'documentable_id' => $documentable->getKey(),
            'module' => $module,
            'category' => $category,
            'disk' => $this->disk(),
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'version' => $nextVersion,
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'uploaded_by' => $uploader?->id,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Store raw generated content (e.g. a rendered PDF) as a document.
     */
    public function storeGenerated(
        string $contents,
        string $filename,
        Model $documentable,
        string $module,
        string $category,
        ?User $uploader = null,
        array $metadata = []
    ): Document {
        $nextVersion = 1 + (int) Document::query()
            ->where('documentable_type', $documentable->getMorphClass())
            ->where('documentable_id', $documentable->getKey())
            ->where('category', $category)
            ->max('version');

        $path = sprintf('%s/%s/v%d_%s', trim($module, '/'), $documentable->getKey(), $nextVersion, $filename);

        Storage::disk($this->disk())->put($path, $contents, ['visibility' => 'private']);

        return Document::query()->create([
            'documentable_type' => $documentable->getMorphClass(),
            'documentable_id' => $documentable->getKey(),
            'module' => $module,
            'category' => $category,
            'disk' => $this->disk(),
            'path' => $path,
            'original_filename' => $filename,
            'mime_type' => 'application/pdf',
            'size' => strlen($contents),
            'version' => $nextVersion,
            'checksum' => hash('sha256', $contents),
            'uploaded_by' => $uploader?->id,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Time-limited signed URL for downloading a document. Uses the native
     * S3 presigned URL when available, and falls back to a signed
     * application route for local-disk development.
     */
    public function temporaryUrl(Document $document, ?int $minutes = null): string
    {
        $minutes ??= (int) config('filesystems.document_signed_url_minutes', 15);
        $disk = Storage::disk($document->disk);

        if (method_exists($disk, 'temporaryUrl') && config("filesystems.disks.{$document->disk}.driver") === 's3') {
            return $disk->temporaryUrl($document->path, now()->addMinutes($minutes));
        }

        return URL::temporarySignedRoute(
            'documents.download',
            now()->addMinutes($minutes),
            ['document' => $document->id]
        );
    }

    public function delete(Document $document): bool
    {
        Storage::disk($document->disk)->delete($document->path);

        return (bool) $document->delete();
    }

    public function contents(Document $document): string
    {
        return Storage::disk($document->disk)->get($document->path);
    }
}
