<?php

namespace App\Livewire\Settings;

use App\Models\Settings\AgencyProfile;
use App\Support\Roles;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class AgencyProfileTab extends Component
{
    use WithFileUploads;

    public string $profileId = '';

    public string $name = '';

    public string $acronym = '';

    public string $agency_code = '';

    public string $address = '';

    public string $region = '';

    public string $tin = '';

    public string $head_of_agency = '';

    public string $hope_position = '';

    public string $bac_chairperson = '';

    public string $website = '';

    public string $contact_email = '';

    public string $contact_phone = '';

    public string $philgeps_organization_id = '';

    public $logo = null;

    public $loginBackground = null;

    public bool $removeLoginBackground = false;

    public function mount(): void
    {
        Gate::authorize('settings.manage');

        $profile = AgencyProfile::query()->first()
            ?? AgencyProfile::query()->create(['name' => config('app.name')]);

        $this->profileId = $profile->id;
        $this->fill(collect($profile->only([
            'name', 'acronym', 'agency_code', 'address', 'region', 'tin', 'head_of_agency',
            'hope_position', 'bac_chairperson', 'website', 'contact_email', 'contact_phone', 'philgeps_organization_id',
        ]))->map(fn ($value) => $value ?? '')->all());
    }

    public function save(): void
    {
        Gate::authorize('settings.manage');

        $this->validate([
            'name' => 'required|string|max:255',
            'acronym' => 'nullable|string|max:50',
            'agency_code' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'region' => 'nullable|string|max:100',
            'tin' => 'nullable|string|max:20',
            'head_of_agency' => 'nullable|string|max:255',
            'hope_position' => 'nullable|string|max:255',
            'bac_chairperson' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'philgeps_organization_id' => 'nullable|string|max:100',
            'logo' => 'nullable|image|max:2048',
            'loginBackground' => 'nullable|image|max:5120',
        ]);

        $profile = $this->profile();

        $data = $this->only([
            'name', 'acronym', 'agency_code', 'address', 'region', 'tin', 'head_of_agency',
            'hope_position', 'bac_chairperson', 'website', 'contact_email', 'contact_phone', 'philgeps_organization_id',
        ]);

        if ($this->logo) {
            if ($profile->logo_path) {
                Storage::disk('public')->delete($profile->logo_path);
            }

            $data['logo_path'] = $this->logo->store('agency', 'public');
        }

        if ($this->canManageLoginBackground()) {
            if ($this->removeLoginBackground && $profile->login_background_path) {
                Storage::disk('public')->delete($profile->login_background_path);
                $data['login_background_path'] = null;
            } elseif ($this->loginBackground) {
                if ($profile->login_background_path) {
                    Storage::disk('public')->delete($profile->login_background_path);
                }

                $data['login_background_path'] = $this->loginBackground->store('agency/login-backgrounds', 'public');
            }
        }

        $profile->update($data);
        AgencyProfile::resetCached();
        $this->reset('logo', 'loginBackground', 'removeLoginBackground');

        session()->flash('status', 'Agency profile updated.');

        $this->skipRender();

        $this->redirect(route('settings.index', ['tab' => 'profile']), navigate: false);
    }

    protected function profile(): AgencyProfile
    {
        return AgencyProfile::query()->findOrFail($this->profileId);
    }

    protected function canManageLoginBackground(): bool
    {
        return auth()->user()?->hasRole(Roles::SUPER_ADMIN) ?? false;
    }

    public function render()
    {
        $profile = $this->profile();
        $logoUrl = $profile->logoUrl();

        if ($this->logo) {
            try {
                $logoUrl = $this->logo->temporaryUrl();
            } catch (\Throwable) {
                // Fall back to the saved logo if the temp preview is unavailable.
            }
        }

        $loginBackgroundUrl = $profile->loginBackgroundUrl();

        if ($this->loginBackground) {
            try {
                $loginBackgroundUrl = $this->loginBackground->temporaryUrl();
            } catch (\Throwable) {
                // Fall back to the saved background if the temp preview is unavailable.
            }
        }

        return view('livewire.settings.agency-profile-tab', [
            'profile' => $profile,
            'logoUrl' => $logoUrl,
            'loginBackgroundUrl' => $loginBackgroundUrl,
            'canManageLoginBackground' => $this->canManageLoginBackground(),
        ]);
    }
}
