<?php

namespace Database\Seeders;

use App\Enums\BacRosterRole;
use App\Enums\TwGCategory;
use App\Enums\TwGDesignationType;
use App\Models\Bac\BacMember;
use App\Models\User;
use App\Services\Bac\BacMemberService;
use App\Support\Roles;
use Illuminate\Database\Seeder;

class BacMemberSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(BacMemberService::class);
        $bacDivisionId = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->value('division_id');

        $this->seedOfficialBac($service);
        $this->seedTwGPersonnel($service, $bacDivisionId);
    }

    protected function seedOfficialBac(BacMemberService $service): void
    {
        $officials = [
            [
                'email' => 'bac.chair@pms.gov.ph',
                'bac_role' => BacRosterRole::Chairperson,
                'designation' => 'BAC Chairperson',
            ],
            [
                'email' => 'bac.secretariat@pms.gov.ph',
                'bac_role' => BacRosterRole::Secretariat,
                'designation' => 'BAC Secretariat',
            ],
        ];

        foreach ($officials as $official) {
            $user = User::query()->where('email', $official['email'])->first();

            if (! $user || BacMember::query()->where('user_id', $user->id)->exists()) {
                continue;
            }

            $service->create([
                'user_id' => $user->id,
                'bac_role' => $official['bac_role']->value,
                'designation' => $official['designation'],
                'is_active' => true,
                'twg_assignments' => [],
            ]);
        }
    }

    protected function seedTwGPersonnel(BacMemberService $service, ?string $bacDivisionId): void
    {
        $twgRoster = [
            TwGCategory::Infrastructure->value => [
                TwGDesignationType::Primary->value => [
                    'email' => 'twg.infra.primary@pms.gov.ph',
                    'name' => 'Engr. Angela Cruz',
                    'designation' => 'TWG Primary — Infrastructure',
                ],
                TwGDesignationType::Alternate->value => [
                    'email' => 'twg.infra.alt@pms.gov.ph',
                    'name' => 'Engr. Ricardo Santos',
                    'designation' => 'TWG Alternate — Infrastructure',
                ],
            ],
            TwGCategory::Equipment->value => [
                TwGDesignationType::Primary->value => [
                    'email' => 'twg.equipment.primary@pms.gov.ph',
                    'name' => 'Engr. Paolo Mendez',
                    'designation' => 'TWG Primary — Equipment',
                ],
                TwGDesignationType::Alternate->value => [
                    'email' => 'twg.equipment.alt@pms.gov.ph',
                    'name' => 'Engr. Miguel Torres',
                    'designation' => 'TWG Alternate — Equipment',
                ],
            ],
            TwGCategory::Services->value => [
                TwGDesignationType::Primary->value => [
                    'email' => 'twg.services.primary@pms.gov.ph',
                    'name' => 'Dr. Elena Villareal',
                    'designation' => 'TWG Primary — Services',
                ],
                TwGDesignationType::Alternate->value => [
                    'email' => 'twg.services.alt@pms.gov.ph',
                    'name' => 'Dr. Carmen Lozada',
                    'designation' => 'TWG Alternate — Services',
                ],
            ],
        ];

        $legacyMember = User::query()->where('email', 'bac.member@pms.gov.ph')->first();
        if ($legacyMember) {
            BacMember::query()->where('user_id', $legacyMember->id)->delete();
        }

        foreach ($twgRoster as $category => $slots) {
            foreach ($slots as $designationType => $profile) {
                $user = User::query()->firstOrCreate(
                    ['email' => $profile['email']],
                    [
                        'name' => $profile['name'],
                        'password' => UserSeeder::DEFAULT_PASSWORD,
                        'is_active' => true,
                        'must_change_password' => true,
                        'email_verified_at' => now(),
                        'division_id' => $bacDivisionId,
                    ]
                );

                $user->syncRoles([Roles::BAC_MEMBER]);

                $member = BacMember::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'bac_role' => BacRosterRole::Member->value,
                        'designation' => $profile['designation'],
                        'is_active' => true,
                    ]
                );

                $member->update([
                    'designation' => $profile['designation'],
                    'is_active' => true,
                ]);

                $service->assignTwGSlot($member, $category, $designationType);
            }
        }
    }
}
