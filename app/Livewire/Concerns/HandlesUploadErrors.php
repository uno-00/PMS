<?php

namespace App\Livewire\Concerns;

use Illuminate\Validation\ValidationException;

/**
 * Livewire's default _uploadErrored() assumes JSON validation errors. When
 * PHP rejects an upload (size limit, tmp dir, etc.) the endpoint returns
 * HTML warnings and json_decode() fails with "array offset on null".
 */
trait HandlesUploadErrors
{
    public function _uploadErrored($name, $errorsInJson, $isMultiple): void
    {
        $this->dispatch('upload:errored', name: $name)->self();

        if (is_null($errorsInJson)) {
            throw ValidationException::withMessages([
                $name => $this->uploadFailureMessage($name),
            ]);
        }

        $errorsInJson = $isMultiple
            ? str_ireplace('files', $name, $errorsInJson)
            : str_ireplace('files.0', $name, $errorsInJson);

        $decoded = json_decode($errorsInJson, true);

        if (! is_array($decoded) || ! isset($decoded['errors'])) {
            throw ValidationException::withMessages([
                $name => $this->uploadFailureMessage($name),
            ]);
        }

        throw ValidationException::withMessages($decoded['errors']);
    }

    protected function uploadFailureMessage(string $name): string
    {
        $limit = \App\Support\UploadLimits::maxMegabytesLabel();

        return "The {$name} could not be uploaded. Use a file under {$limit} (current PHP limit). "
            .'If you are running locally with `php artisan serve`, restart using `composer dev` or raise upload_max_filesize and post_max_size in php.ini.';
    }
}
