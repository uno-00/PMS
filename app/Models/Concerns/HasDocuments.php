<?php

namespace App\Models\Concerns;

use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasDocuments
{
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->latest('version');
    }

    public function documentsInCategory(string $category): MorphMany
    {
        return $this->documents()->where('category', $category);
    }

    public function latestDocument(string $category = 'attachment'): ?Document
    {
        return $this->documentsInCategory($category)->first();
    }
}
