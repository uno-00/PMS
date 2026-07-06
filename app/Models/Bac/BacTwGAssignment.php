<?php

namespace App\Models\Bac;

use App\Enums\TwGCategory;
use App\Enums\TwGDesignationType;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacTwGAssignment extends Model
{
    use HasUuid;

    protected $table = 'bac_twg_assignments';

    protected $fillable = [
        'bac_member_id',
        'category',
        'designation_type',
    ];

    protected function casts(): array
    {
        return [
            'category' => TwGCategory::class,
            'designation_type' => TwGDesignationType::class,
        ];
    }

    public function bacMember(): BelongsTo
    {
        return $this->belongsTo(BacMember::class);
    }
}
