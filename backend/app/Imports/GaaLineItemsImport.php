<?php

namespace App\Imports;

use App\Models\Settings\Department;
use App\Models\Settings\Division;
use App\Models\Settings\FundSource;
use App\Models\Settings\Pap;
use App\Models\Settings\UacsCode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;

/**
 * Parses the DBM GAA Excel Template.
 *
 * Expected columns (row 1 headings): department_code, division_code,
 * pap_code, uacs_code, fund_source_code, description, amount.
 *
 * Rather than failing hard on the first bad row, this collects every
 * row-level problem so the Budget Officer sees a complete validation
 * report (per the "Validate Budget" function) before anything is
 * persisted against the fiscal year.
 */
class GaaLineItemsImport implements SkipsEmptyRows, SkipsOnFailure, ToCollection, WithHeadingRow
{
    use Importable;

    public array $rows = [];

    public array $errors = [];

    public float $totalAmount = 0;

    protected array $departments;

    protected array $divisions;

    protected array $paps;

    protected array $uacs;

    protected array $fundSources;

    /** @var array<int, string> */
    protected array $codeFields = ['department_code', 'division_code', 'pap_code', 'uacs_code', 'fund_source_code'];

    public function __construct()
    {
        $this->departments = Department::query()->pluck('id', 'code')->all();
        $this->divisions = Division::query()->pluck('id', 'code')->all();
        $this->paps = Pap::query()->pluck('id', 'code')->all();
        $this->uacs = UacsCode::query()->pluck('id', 'code')->all();
        $this->fundSources = FundSource::query()->pluck('id', 'code')->all();
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $lineNo = $index + 2; // account for heading row
            $row = $this->normalizeRow($row);

            if ($this->isBlankRow($row)) {
                continue;
            }

            $rowErrors = $this->validateRow($row, $lineNo);

            if (! empty($rowErrors)) {
                $this->errors = array_merge($this->errors, $rowErrors);

                continue;
            }

            $amount = (float) $row['amount'];
            $this->totalAmount += $amount;

            $this->rows[] = [
                'line_no' => $lineNo,
                'department_id' => $this->departments[$row['department_code']] ?? null,
                'division_id' => $this->divisions[$row['division_code'] ?? ''] ?? null,
                'pap_id' => $this->paps[$row['pap_code']] ?? null,
                'uacs_code_id' => $this->uacs[$row['uacs_code']] ?? null,
                'fund_source_id' => $this->fundSources[$row['fund_source_code']] ?? null,
                'description' => $row['description'] ?? null,
                'amount' => $amount,
            ];
        }
    }

    protected function normalizeRow(Collection $row): Collection
    {
        return $row->map(function ($value, $key) {
            if (in_array($key, $this->codeFields, true)) {
                return $this->normalizeCode($value);
            }

            return is_string($value) ? trim($value) : $value;
        });
    }

    protected function normalizeCode(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return sprintf('%.0f', $value);
        }

        return trim((string) $value) ?: null;
    }

    protected function isBlankRow(Collection $row): bool
    {
        foreach (['department_code', 'pap_code', 'uacs_code', 'fund_source_code', 'amount'] as $field) {
            if (! blank($row[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }

    protected function validateRow(Collection $row, int $lineNo): array
    {
        $errors = [];

        foreach (['department_code', 'pap_code', 'uacs_code', 'fund_source_code', 'amount'] as $required) {
            if (blank($row[$required] ?? null)) {
                $errors[] = "Row {$lineNo}: missing required value for '{$required}'.";
            }
        }

        if (! blank($row['department_code'] ?? null) && ! isset($this->departments[$row['department_code']])) {
            $errors[] = "Row {$lineNo}: unknown department code '{$row['department_code']}'.";
        }

        if (! blank($row['division_code'] ?? null) && ! isset($this->divisions[$row['division_code']])) {
            $errors[] = "Row {$lineNo}: unknown division code '{$row['division_code']}'.";
        }

        if (! blank($row['pap_code'] ?? null) && ! isset($this->paps[$row['pap_code']])) {
            $errors[] = "Row {$lineNo}: unknown PAP code '{$row['pap_code']}'.";
        }

        if (! blank($row['uacs_code'] ?? null) && ! isset($this->uacs[$row['uacs_code']])) {
            $errors[] = "Row {$lineNo}: unknown UACS code '{$row['uacs_code']}'.";
        }

        if (! blank($row['fund_source_code'] ?? null) && ! isset($this->fundSources[$row['fund_source_code']])) {
            $errors[] = "Row {$lineNo}: unknown fund source code '{$row['fund_source_code']}'.";
        }

        if (isset($row['amount']) && ! is_numeric($row['amount'])) {
            $errors[] = "Row {$lineNo}: amount '{$row['amount']}' is not numeric.";
        } elseif (isset($row['amount']) && (float) $row['amount'] <= 0) {
            $errors[] = "Row {$lineNo}: amount must be greater than zero.";
        }

        return $errors;
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            foreach ($failure->errors() as $error) {
                $this->errors[] = "Row {$failure->row()}: {$error}";
            }
        }
    }

    public function isValid(): bool
    {
        return empty($this->errors) && ! empty($this->rows);
    }
}
