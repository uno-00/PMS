<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Settings\ApprovalRouting;
use App\Models\Settings\CostCenter;
use App\Models\Settings\Department;
use App\Models\Settings\Division;
use App\Models\Settings\FundSource;
use App\Models\Settings\Holiday;
use App\Models\Settings\ModeOfProcurement;
use App\Models\Settings\Office;
use App\Models\Settings\Pap;
use App\Models\Settings\ProcurementThreshold;
use App\Models\Settings\UacsCode;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * A single, declarative CRUD surface for every "master data" table that
 * drives the rest of the system: organizational units, UACS/PAP/fund
 * codes, modes of procurement, procurement thresholds, approval routing,
 * and the holiday calendar. Keeping these editable here (instead of
 * hardcoded) is what lets thresholds/approval routes stay configurable
 * per RA 12009 without a code deployment.
 */
class ReferenceDataTab extends Component
{
    use InteractsWithTableFilters;

    #[Url(as: 'entity')]
    public string $entity = 'departments';

    /** @var array<string, string> */
    public array $columnFilters = [];

    public array $form = [];

    public ?string $editingId = null;

    public bool $showFormModal = false;

    public function mount(): void
    {
        Gate::authorize('settings.manage');

        if (! array_key_exists($this->entity, $this->entities())) {
            $this->entity = 'departments';
        }
    }

    public function selectEntity(string $entity): void
    {
        $this->entity = $entity;
        $this->resetFilters();
        $this->closeModal();
    }

    public function resetFilters(): void
    {
        $this->columnFilters = [];
        $this->reset('dateFrom', 'dateTo');
    }

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->form = collect($this->entities()[$this->entity]['fields'])
            ->map(fn ($field) => $field['type'] === 'boolean' ? true : '')
            ->all();
        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function openEdit(string $id): void
    {
        $config = $this->entities()[$this->entity];
        $model = $config['model']::findOrFail($id);

        $this->editingId = $id;
        $this->form = collect($config['fields'])->mapWithKeys(function ($field, $key) use ($model) {
            $value = $model->{$key};

            if ($field['type'] === 'date' && $value) {
                $value = $value->toDateString();
            }

            return [$key => $value ?? ''];
        })->all();
        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
        $this->editingId = null;
        $this->form = [];
    }

    public function save(): void
    {
        Gate::authorize('settings.manage');

        $config = $this->entities()[$this->entity];
        $this->validate($this->rulesFor($config['fields']));

        $payload = collect($this->form)->map(function ($value, $key) use ($config) {
            $type = $config['fields'][$key]['type'];

            if ($type === 'boolean') {
                return (bool) $value;
            }

            if ($value === '' && ($config['fields'][$key]['nullable'] ?? false)) {
                return null;
            }

            return $value;
        })->all();

        if ($this->editingId) {
            $config['model']::findOrFail($this->editingId)->update($payload);
            session()->flash('status', $config['label'].' entry updated.');
        } else {
            $config['model']::query()->create($payload);
            session()->flash('status', $config['label'].' entry created.');
        }

        $this->closeModal();
    }

    public function delete(string $id): void
    {
        Gate::authorize('settings.manage');

        $config = $this->entities()[$this->entity];
        $config['model']::findOrFail($id)->delete();

        session()->flash('status', $config['label'].' entry removed.');
    }

    protected function rulesFor(array $fields): array
    {
        return collect($fields)->mapWithKeys(function ($field, $key) {
            $nullable = $field['nullable'] ?? false;

            $rule = match ($field['type']) {
                'number' => $nullable ? 'nullable|integer' : 'required|integer',
                'decimal' => $nullable ? 'nullable|numeric' : 'required|numeric|min:0',
                'date' => $nullable ? 'nullable|date' : 'required|date',
                'boolean' => 'boolean',
                'textarea' => 'nullable|string',
                default => $nullable ? 'nullable|string|max:255' : 'required|string|max:255',
            };

            return ["form.{$key}" => $rule];
        })->all();
    }

    /**
     * @return array<string, array{label: string, model: class-string, fields: array}>
     */
    protected function entities(): array
    {
        return [
            'departments' => [
                'label' => 'Departments',
                'model' => Department::class,
                'fields' => [
                    'code' => ['type' => 'text', 'label' => 'Code'],
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'is_active' => ['type' => 'boolean', 'label' => 'Active'],
                ],
            ],
            'divisions' => [
                'label' => 'Divisions',
                'model' => Division::class,
                'fields' => [
                    'department_id' => ['type' => 'select', 'label' => 'Department', 'options' => 'departments'],
                    'code' => ['type' => 'text', 'label' => 'Code'],
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'chief_user_id' => ['type' => 'select', 'label' => 'Division Chief', 'options' => 'users', 'nullable' => true],
                    'is_active' => ['type' => 'boolean', 'label' => 'Active'],
                ],
            ],
            'offices' => [
                'label' => 'Offices',
                'model' => Office::class,
                'fields' => [
                    'division_id' => ['type' => 'select', 'label' => 'Division', 'options' => 'divisions'],
                    'code' => ['type' => 'text', 'label' => 'Code'],
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'is_active' => ['type' => 'boolean', 'label' => 'Active'],
                ],
            ],
            'cost-centers' => [
                'label' => 'Cost Centers',
                'model' => CostCenter::class,
                'fields' => [
                    'division_id' => ['type' => 'select', 'label' => 'Division', 'options' => 'divisions'],
                    'code' => ['type' => 'text', 'label' => 'Code'],
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'is_active' => ['type' => 'boolean', 'label' => 'Active'],
                ],
            ],
            'modes-of-procurement' => [
                'label' => 'Modes of Procurement',
                'model' => ModeOfProcurement::class,
                'fields' => [
                    'code' => ['type' => 'text', 'label' => 'Code'],
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                    'requires_bac' => ['type' => 'boolean', 'label' => 'Requires BAC'],
                    'requires_philgeps_posting' => ['type' => 'boolean', 'label' => 'Requires PhilGEPS Posting'],
                    'sort_order' => ['type' => 'number', 'label' => 'Sort Order'],
                    'is_active' => ['type' => 'boolean', 'label' => 'Active'],
                ],
            ],
            'fund-sources' => [
                'label' => 'Fund Sources',
                'model' => FundSource::class,
                'fields' => [
                    'code' => ['type' => 'text', 'label' => 'Code'],
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                    'is_active' => ['type' => 'boolean', 'label' => 'Active'],
                ],
            ],
            'uacs-codes' => [
                'label' => 'UACS Codes',
                'model' => UacsCode::class,
                'fields' => [
                    'code' => ['type' => 'text', 'label' => 'Code'],
                    'description' => ['type' => 'text', 'label' => 'Description'],
                    'expense_class' => ['type' => 'text', 'label' => 'Expense Class'],
                    'is_active' => ['type' => 'boolean', 'label' => 'Active'],
                ],
            ],
            'paps' => [
                'label' => 'Programs / Activities / Projects',
                'model' => Pap::class,
                'fields' => [
                    'type' => ['type' => 'select-static', 'label' => 'Type', 'options' => ['program' => 'Program', 'activity' => 'Activity', 'project' => 'Project']],
                    'parent_id' => ['type' => 'select', 'label' => 'Parent', 'options' => 'paps', 'nullable' => true],
                    'code' => ['type' => 'text', 'label' => 'Code'],
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                    'is_active' => ['type' => 'boolean', 'label' => 'Active'],
                ],
            ],
            'holidays' => [
                'label' => 'Holiday Calendar',
                'model' => Holiday::class,
                'fields' => [
                    'date' => ['type' => 'date', 'label' => 'Date'],
                    'name' => ['type' => 'text', 'label' => 'Name'],
                    'type' => ['type' => 'select-static', 'label' => 'Type', 'options' => ['regular' => 'Regular Holiday', 'special' => 'Special Non-Working Day']],
                    'is_active' => ['type' => 'boolean', 'label' => 'Active'],
                ],
            ],
            'thresholds' => [
                'label' => 'Procurement Thresholds',
                'model' => ProcurementThreshold::class,
                'fields' => [
                    'mode_of_procurement_id' => ['type' => 'select', 'label' => 'Mode of Procurement', 'options' => 'modes-of-procurement'],
                    'category' => ['type' => 'select-static', 'label' => 'Category', 'options' => ['goods' => 'Goods & Services', 'infrastructure' => 'Infrastructure', 'consulting' => 'Consulting Services']],
                    'min_amount' => ['type' => 'decimal', 'label' => 'Minimum Amount (₱)'],
                    'max_amount' => ['type' => 'decimal', 'label' => 'Maximum Amount (₱, blank = no cap)', 'nullable' => true],
                    'effective_date' => ['type' => 'date', 'label' => 'Effective Date'],
                    'is_active' => ['type' => 'boolean', 'label' => 'Active'],
                ],
            ],
            'approval-routing' => [
                'label' => 'Approval Routing',
                'model' => ApprovalRouting::class,
                'fields' => [
                    'document_type' => ['type' => 'select-static', 'label' => 'Document Type', 'options' => ['ppmp' => 'PPMP', 'purchase-request' => 'Purchase Request', 'caf' => 'CAF', 'gaa' => 'GAA']],
                    'step_key' => ['type' => 'text', 'label' => 'Step Key (e.g. division_chief)'],
                    'step_label' => ['type' => 'text', 'label' => 'Step Label'],
                    'sequence' => ['type' => 'number', 'label' => 'Sequence'],
                    'role_name' => ['type' => 'select', 'label' => 'Responsible Role', 'options' => 'roles'],
                    'is_active' => ['type' => 'boolean', 'label' => 'Active'],
                ],
            ],
        ];
    }

    protected function optionsFor(string $key): array
    {
        return match ($key) {
            'departments' => Department::query()->orderBy('name')->pluck('name', 'id')->all(),
            'divisions' => Division::query()->orderBy('name')->pluck('name', 'id')->all(),
            'paps' => Pap::query()->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))->orderBy('name')->pluck('name', 'id')->all(),
            'modes-of-procurement' => ModeOfProcurement::query()->orderBy('name')->pluck('name', 'id')->all(),
            'roles' => collect(Roles::internal())->combine(Roles::internal())->all(),
            'users' => User::query()->orderBy('name')->pluck('name', 'id')->all(),
            default => [],
        };
    }

    /** @param  array<string, array{type: string, label: string, options?: array|string, nullable?: bool}>  $fields */
    protected function applyColumnFilters(Builder $query, array $fields): Builder
    {
        foreach ($fields as $key => $field) {
            $value = trim($this->columnFilters[$key] ?? '');

            if ($value === '') {
                continue;
            }

            match ($field['type']) {
                'boolean' => $query->where($key, $value === '1'),
                'select', 'select-static' => $query->where($key, $value),
                'decimal', 'number' => $this->applyAmountFilter($query, $key, $value),
                'date' => $query->whereDate($key, $value),
                default => $query->where($key, 'like', '%'.$value.'%'),
            };
        }

        return $query;
    }

    public function render()
    {
        $config = $this->entities()[$this->entity];

        $query = $config['model']::query();
        $this->applyColumnFilters($query, $config['fields']);
        $this->applyCreatedAtFilter($query);

        $records = $query->latest('created_at')->get();

        $options = collect($config['fields'])
            ->filter(fn ($field) => $field['type'] === 'select')
            ->mapWithKeys(fn ($field, $key) => [$key => $this->optionsFor($field['options'])]);

        return view('livewire.settings.reference-data-tab', [
            'entities' => $this->entities(),
            'config' => $config,
            'records' => $records,
            'options' => $options,
        ]);
    }
}
