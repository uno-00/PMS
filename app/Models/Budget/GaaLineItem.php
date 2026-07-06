<?php

namespace App\Models\Budget;

use App\Models\Concerns\HasUuid;
use App\Models\Settings\Department;
use App\Models\Settings\Division;
use App\Models\Settings\FundSource;
use App\Models\Settings\Pap;
use App\Models\Settings\UacsCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaaLineItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'gaa_id', 'line_no', 'department_id', 'division_id', 'pap_id',
        'uacs_code_id', 'fund_source_id', 'description', 'amount',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function gaa(): BelongsTo
    {
        return $this->belongsTo(GeneralAppropriationsAct::class, 'gaa_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function pap(): BelongsTo
    {
        return $this->belongsTo(Pap::class);
    }

    public function uacsCode(): BelongsTo
    {
        return $this->belongsTo(UacsCode::class, 'uacs_code_id');
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class);
    }
}
