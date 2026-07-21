<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Streams a document from the local "documents" disk via a signed,
 * time-limited URL. In production (S3 disk) DocumentStorageService issues
 * a native presigned S3 URL instead and this route is not used.
 */
class DocumentDownloadController extends Controller
{
    public function __invoke(Request $request, Document $document)
    {
        abort_unless($request->hasValidSignature(), 403, 'This download link has expired.');

        $disk = Storage::disk($document->disk);

        abort_unless($disk->exists($document->path), 404, 'Document not found.');

        return $disk->download($document->path, $document->original_filename);
    }
}
