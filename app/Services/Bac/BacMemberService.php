<?php

namespace App\Services\Bac;

use App\Enums\BacRosterRole;
use App\Enums\TwGCategory;
use App\Enums\TwGDesignationType;
use App\Models\Bac\BacMember;
use App\Models\Bac\BacTwGAssignment;
use App\Models\User;
use App\Support\PasswordPolicy;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class BacMemberService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): BacMember
    {
        return DB::transaction(function () use ($data) {
            $user = $this->resolveUser($data, null);
            $this->assertUniqueRosterRole($data['bac_role'], null);

            $member = BacMember::query()->create([
                'user_id' => $user->id,
                'bac_role' => $data['bac_role'],
                'designation' => $data['designation'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'term_start' => $data['term_start'] ?? null,
                'term_end' => $data['term_end'] ?? null,
            ]);

            $this->syncTwGAssignments($member, $data['twg_assignments'] ?? []);
            $this->syncUserRole($user, $data['bac_role']);

            return $member->fresh(['user', 'twgAssignments']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(BacMember $member, array $data): BacMember
    {
        return DB::transaction(function () use ($member, $data) {
            $this->assertUniqueRosterRole($data['bac_role'], $member->id);

            $member->update([
                'bac_role' => $data['bac_role'],
                'designation' => $data['designation'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'term_start' => $data['term_start'] ?? null,
                'term_end' => $data['term_end'] ?? null,
            ]);

            $this->syncTwGAssignments($member, $data['twg_assignments'] ?? []);
            $this->syncUserRole($member->user, $data['bac_role']);

            return $member->fresh(['user', 'twgAssignments']);
        });
    }

    /** @param  array<string, string>  $assignments  category => primary|alternate|'' */
    public function syncTwGAssignments(BacMember $member, array $assignments): void
    {
        $desired = collect($assignments)
            ->mapWithKeys(function (string $type, string $category) {
                $categoryEnum = TwGCategory::tryFrom($category);
                $typeEnum = TwGDesignationType::tryFrom($type);

                if (! $categoryEnum || ! $typeEnum) {
                    return [];
                }

                return [$categoryEnum->value => $typeEnum];
            });

        $member->twgAssignments()
            ->whereNotIn('category', $desired->keys())
            ->delete();

        foreach ($desired as $categoryValue => $designationType) {
            BacTwGAssignment::query()
                ->where('category', $categoryValue)
                ->where('designation_type', $designationType->value)
                ->where('bac_member_id', '!=', $member->id)
                ->delete();

            BacTwGAssignment::query()->updateOrCreate(
                [
                    'bac_member_id' => $member->id,
                    'category' => $categoryValue,
                ],
                [
                    'designation_type' => $designationType->value,
                ]
            );
        }
    }

    public function assignTwGSlot(
        BacMember $member,
        TwGCategory|string $category,
        TwGDesignationType|string $designationType
    ): BacTwGAssignment {
        $categoryEnum = $category instanceof TwGCategory ? $category : TwGCategory::from($category);
        $typeEnum = $designationType instanceof TwGDesignationType
            ? $designationType
            : TwGDesignationType::from($designationType);

        $assignments = $member->twgAssignments()
            ->get()
            ->mapWithKeys(fn (BacTwGAssignment $assignment) => [
                $assignment->category->value => $assignment->designation_type->value,
            ])
            ->all();

        $assignments[$categoryEnum->value] = $typeEnum->value;

        $this->syncTwGAssignments($member, $assignments);

        return BacTwGAssignment::query()
            ->where('bac_member_id', $member->id)
            ->where('category', $categoryEnum->value)
            ->firstOrFail();
    }

    /**
     * @return array<string, array{primary: ?BacMember, alternate: ?BacMember}>
     */
    public function twgRosterByCategory(): array
    {
        $roster = [];

        foreach (TwGCategory::cases() as $category) {
            $roster[$category->value] = [
                'primary' => null,
                'alternate' => null,
            ];
        }

        $assignments = BacTwGAssignment::query()
            ->with(['bacMember.user'])
            ->whereHas('bacMember', fn ($q) => $q->active())
            ->get();

        foreach ($assignments as $assignment) {
            $key = $assignment->designation_type === TwGDesignationType::Primary ? 'primary' : 'alternate';
            $roster[$assignment->category->value][$key] = $assignment->bacMember;
        }

        return $roster;
    }

    public function syncUserRole(User $user, BacRosterRole|string $role): void
    {
        $role = $role instanceof BacRosterRole ? $role : BacRosterRole::from($role);

        $user->syncRoles([$role->spatieRole()]);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, BacMember> */
    public function twgMembersFor(TwGCategory|string $category)
    {
        $value = $category instanceof TwGCategory ? $category->value : $category;

        return BacMember::query()
            ->active()
            ->forTwGCategory($value)
            ->with('user')
            ->orderBy('bac_role')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveUser(array $data, ?string $excludeMemberId): User
    {
        if (! empty($data['create_new_user'])) {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'division_id' => $data['division_id'] ?? null,
                'is_active' => true,
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]);

            return $user;
        }

        $existingMember = BacMember::query()
            ->when($excludeMemberId, fn ($q) => $q->where('id', '!=', $excludeMemberId))
            ->where('user_id', $data['user_id'])
            ->exists();

        if ($existingMember) {
            throw ValidationException::withMessages([
                'user_id' => 'This user is already on the BAC roster.',
            ]);
        }

        return User::query()->findOrFail($data['user_id']);
    }

    protected function assertUniqueRosterRole(BacRosterRole|string $role, ?string $excludeMemberId): void
    {
        $role = $role instanceof BacRosterRole ? $role : BacRosterRole::from($role);

        if (! in_array($role, [BacRosterRole::Chairperson, BacRosterRole::Secretariat], true)) {
            return;
        }

        $conflict = BacMember::query()
            ->active()
            ->where('bac_role', $role->value)
            ->when($excludeMemberId, fn ($q) => $q->where('id', '!=', $excludeMemberId))
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'bac_role' => "An active {$role->label()} is already assigned. Deactivate the current holder first.",
            ]);
        }
    }

    /** @return array<int, \Illuminate\Validation\Rules\Password|string> */
    public static function passwordRules(bool $required = true): array
    {
        return $required
            ? ['required', PasswordPolicy::rule()]
            : [PasswordPolicy::rule()];
    }

    /** @return array<int, string> */
    public static function bacRoleValues(): array
    {
        return array_map(fn (BacRosterRole $role) => $role->value, BacRosterRole::cases());
    }

    public static function stripOtherBacRoles(User $user): void
    {
        foreach (Roles::bac() as $roleName) {
            if ($user->hasRole($roleName)) {
                $user->removeRole($roleName);
            }
        }
    }
}
