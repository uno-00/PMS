<?php

namespace App\Livewire\Portal;

use App\Models\Supplier\Bidder;
use App\Services\Supplier\BidderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public Bidder $bidder;

    public string $company_name = '';

    public string $business_type = '';

    public string $philgeps_registration_no = '';

    public string $philgeps_registration_expiry = '';

    public string $mayor_permit_no = '';

    public string $mayor_permit_expiry = '';

    public string $tax_clearance_no = '';

    public string $tax_clearance_expiry = '';

    public string $sec_dti_registration_no = '';

    public string $pcab_license_no = '';

    public string $pcab_license_expiry = '';

    public string $tin = '';

    public string $contact_person = '';

    public string $phone = '';

    public string $address = '';

    public string $activeUploadCategory = '';

    public $documentFile = null;

    public function mount(): void
    {
        $bidder = Auth::user()->bidder;
        abort_unless($bidder, 403, 'No supplier profile linked to this account.');
        Gate::authorize('update', $bidder);

        $this->bidder = $bidder;
        $this->fill($bidder->only([
            'company_name', 'business_type', 'philgeps_registration_no', 'mayor_permit_no',
            'tax_clearance_no', 'sec_dti_registration_no', 'pcab_license_no', 'tin', 'contact_person', 'phone', 'address',
        ]));
        $this->philgeps_registration_expiry = optional($bidder->philgeps_registration_expiry)->toDateString() ?? '';
        $this->mayor_permit_expiry = optional($bidder->mayor_permit_expiry)->toDateString() ?? '';
        $this->tax_clearance_expiry = optional($bidder->tax_clearance_expiry)->toDateString() ?? '';
        $this->pcab_license_expiry = optional($bidder->pcab_license_expiry)->toDateString() ?? '';
    }

    public function save(): void
    {
        Gate::authorize('update', $this->bidder);

        $this->validate([
            'company_name' => 'required|string|max:255',
            'business_type' => 'required|string',
            'philgeps_registration_no' => 'nullable|string|max:255',
            'philgeps_registration_expiry' => 'nullable|date',
            'mayor_permit_no' => 'nullable|string|max:255',
            'mayor_permit_expiry' => 'nullable|date',
            'tax_clearance_no' => 'nullable|string|max:255',
            'tax_clearance_expiry' => 'nullable|date',
            'sec_dti_registration_no' => 'nullable|string|max:255',
            'pcab_license_no' => 'nullable|string|max:255',
            'pcab_license_expiry' => 'nullable|date',
            'tin' => 'nullable|string|max:20',
            'contact_person' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $this->bidder->update([
            'company_name' => $this->company_name,
            'business_type' => $this->business_type,
            'philgeps_registration_no' => $this->philgeps_registration_no,
            'philgeps_registration_expiry' => $this->philgeps_registration_expiry ?: null,
            'mayor_permit_no' => $this->mayor_permit_no,
            'mayor_permit_expiry' => $this->mayor_permit_expiry ?: null,
            'tax_clearance_no' => $this->tax_clearance_no,
            'tax_clearance_expiry' => $this->tax_clearance_expiry ?: null,
            'sec_dti_registration_no' => $this->sec_dti_registration_no,
            'pcab_license_no' => $this->pcab_license_no,
            'pcab_license_expiry' => $this->pcab_license_expiry ?: null,
            'tin' => $this->tin,
            'contact_person' => $this->contact_person,
            'phone' => $this->phone,
            'address' => $this->address,
        ]);

        session()->flash('status', 'Company profile updated.');
    }

    public function uploadDocument(string $category, BidderService $service): void
    {
        Gate::authorize('update', $this->bidder);

        $this->activeUploadCategory = $category;
        $this->validate(['documentFile' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240']);

        $service->uploadEligibilityDocument($this->bidder, $category, $this->documentFile, Auth::user());

        $this->reset('documentFile', 'activeUploadCategory');
        session()->flash('status', 'Document uploaded. It will be reviewed by the BAC Secretariat.');
        $this->bidder->refresh();
    }

    public function render()
    {
        $this->bidder->load('documents');

        return view('livewire.portal.profile')
            ->layout('components.layouts.portal', ['title' => 'Company Profile']);
    }
}
