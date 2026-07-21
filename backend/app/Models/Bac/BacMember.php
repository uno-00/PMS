<?php

namespace App\Models\Bac;

use App\Enums\BacRosterRole;
use App\Enums\TwGCategory;
use App\Enums\TwGDesignationType;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BacMember extends Model
{
    use HasAuditLog, HasUuid;

    protected $fillable = [
        'user_id',
        'bac_role',
        'designation',
        'is_active',
        'term_start',
        'term_end',
    ];

    protected function casts(): array
    {
        return [
            'bac_role' => BacRosterRole::class,
            'is_active' => 'boolean',
            'term_start' => 'date',
            'term_end' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function twgAssignments(): HasMany
    {
        return $this->hasMany(BacTwGAssignment::class);
    }

    /** @return array<int, array{category: TwGCategory, designation: TwGDesignationType}> */
    public function twgAssignmentsSummary(): array
    {
        return $this->twgAssignments
            ->map(fn (BacTwGAssignment $assignment) => [
                'category' => $assignment->category,
                'designation' => $assignment->designation_type,
            ])
            ->all();
    }

    public function twgDesignationFor(TwGCategory $category): ?TwGDesignationType
    {
        $assignment = $this->twgAssignments->first(
            fn (BacTwGAssignment $row) => $row->category === $category
        );

        return $assignment?->designation_type;
    }

    /** @return array<int, TwGCategory> */
    public function twgCategories(): array
    {
        return $this->twgAssignments
            ->map(fn (BacTwGAssignment $assignment) => $assignment->category)
            ->all();
    }

    public function hasTwGCategory(TwGCategory $category): bool
    {
        return $this->twgAssignments->contains(
            fn (BacTwGAssignment $assignment) => $assignment->category === $category
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTwGCategory($query, TwGCategory|string $category)
    {
        $value = $category instanceof TwGCategory ? $category->value : $category;

        return $query->whereHas('twgAssignments', fn ($q) => $q->where('category', $value));
    }
}
