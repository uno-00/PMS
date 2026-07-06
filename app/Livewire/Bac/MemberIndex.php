<?php

namespace App\Livewire\Bac;

use App\Enums\BacRosterRole;
use App\Enums\TwGCategory;
use App\Enums\TwGDesignationType;
use App\Models\Bac\BacMember;
use App\Models\Settings\Division;
use App\Models\User;
use App\Services\Bac\BacMemberService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MemberIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $roleFilter = '';

    #[Url]
    public string $categoryFilter = '';

    public bool $showModal = false;

    public ?string $editingId = null;

    public bool $createNewUser = false;

    public string $user_id = '';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $bac_role = 'member';

    public string $designation = '';

    public bool $is_active = true;

    public ?string $term_start = null;

    public ?string $term_end = null;

    /** @var array<string, string> category => primary|alternate|'' */
    public array $twg_assignments = [
        'infrastructure' => '',
        'equipment' => '',
        'services' => '',
    ];

    public function mount(): void
    {
        Gate::authorize('bac-members.view');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        Gate::authorize('bac-members.create');
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(string $memberId): void
    {
        Gate::authorize('bac-members.edit');

        $member = BacMember::query()->with('twgAssignments')->findOrFail($memberId);

        $this->editingId = $member->id;
        $this->createNewUser = false;
        $this->user_id = $member->user_id;
        $this->name = $member->user->name;
        $this->email = $member->user->email;
        $this->password = '';
        $this->bac_role = $member->bac_role->value;
        $this->designation = $member->designation ?? '';
        $this->is_active = $member->is_active;
        $this->term_start = $member->term_start?->format('Y-m-d');
        $this->term_end = $member->term_end?->format('Y-m-d');

        foreach (TwGCategory::cases() as $category) {
            $this->twg_assignments[$category->value] = $member->twgDesignationFor($category)?->value ?? '';
        }

        $this->showModal = true;
    }

    public function save(BacMemberService $service): void
    {
        $isCreate = $this->editingId === null;
        Gate::authorize($isCreate ? 'bac-members.create' : 'bac-members.edit');

        $rules = [
            'bac_role' => ['required', Rule::in(BacMemberService::bacRoleValues())],
            'designation' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'term_start' => ['nullable', 'date'],
            'term_end' => ['nullable', 'date', 'after_or_equal:term_start'],
            'twg_assignments.*' => ['nullable', Rule::in(['', ...array_map(fn (TwGDesignationType $t) => $t->value, TwGDesignationType::cases())])],
        ];

        if ($this->createNewUser && $isCreate) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'email', 'max:255', 'unique:users,email'];
            $rules['password'] = BacMemberService::passwordRules();
        } else {
            $rules['user_id'] = [
                'required',
                'uuid',
                'exists:users,id',
                Rule::unique('bac_members', 'user_id')->ignore($this->editingId),
            ];
        }

        $this->validate($rules);

        $bacDivision = Division::query()->where('code', 'GSS-BAC')->value('id');

        $payload = [
            'bac_role' => $this->bac_role,
            'designation' => $this->designation ?: null,
            'is_active' => $this->is_active,
            'term_start' => $this->term_start ?: null,
            'term_end' => $this->term_end ?: null,
            'twg_assignments' => $this->twg_assignments,
            'create_new_user' => $this->createNewUser && $isCreate,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'division_id' => $bacDivision,
        ];

        if ($isCreate) {
            $service->create($payload);
            session()->flash('status', 'BAC member added to the roster.');
        } else {
            $member = BacMember::query()->findOrFail($this->editingId);
            $service->update($member, $payload);
            session()->flash('status', 'BAC member roster entry updated.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(string $memberId): void
    {
        Gate::authorize('bac-members.edit');

        $member = BacMember::query()->findOrFail($memberId);
        $member->update(['is_active' => ! $member->is_active]);

        session()->flash('status', $member->is_active ? 'BAC member activated.' : 'BAC member deactivated.');
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId', 'createNewUser', 'user_id', 'name', 'email', 'password',
            'designation', 'term_start', 'term_end',
        ]);
        $this->bac_role = BacRosterRole::Member->value;
        $this->is_active = true;
        $this->twg_assignments = [
            'infrastructure' => '',
            'equipment' => '',
            'services' => '',
        ];
    }

    public function render(BacMemberService $service)
    {
        $members = BacMember::query()
            ->with(['user.division', 'twgAssignments'])
            ->when($this->search !== '', function ($q) {
                $q->whereHas('user', function ($inner) {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->roleFilter !== '', fn ($q) => $q->where('bac_role', $this->roleFilter))
            ->when($this->categoryFilter !== '', fn ($q) => $q->forTwGCategory($this->categoryFilter))
            ->orderByRaw("CASE bac_role WHEN 'chairperson' THEN 1 WHEN 'secretariat' THEN 2 ELSE 3 END")
            ->orderBy('created_at')
            ->paginate(12);

        $availableUsers = User::query()
            ->active()
            ->whereDoesntHave('bacMember', fn ($q) => $q->when($this->editingId, fn ($inner) => $inner->where('id', '!=', $this->editingId)))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('livewire.bac.member-index', [
            'members' => $members,
            'twgRoster' => $service->twgRosterByCategory(),
            'availableUsers' => $availableUsers,
            'bacRoles' => BacRosterRole::cases(),
            'twgCategories' => TwGCategory::cases(),
            'twgDesignationTypes' => TwGDesignationType::cases(),
        ])->layout('components.layouts.app', ['title' => 'BAC Members & TWG']);
    }
}
