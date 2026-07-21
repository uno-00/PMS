<?php

namespace App\Livewire\Portal;

use App\Services\Supplier\BidderService;
use App\Support\PasswordPolicy;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal-guest')]
class Register extends Component
{
    // Company / business information
    public string $company_name = '';

    public string $business_type = 'sole_proprietorship';

    public string $tin = '';

    public string $contact_person = '';

    public string $phone = '';

    public string $address = '';

    // Account
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(BidderService $service): void
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'business_type' => 'required|string',
            'tin' => 'nullable|string|max:20',
            'contact_person' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', PasswordPolicy::rule()],
        ]);

        $bidder = $service->register([
            'company_name' => $this->company_name,
            'business_type' => $this->business_type,
            'tin' => $this->tin,
            'contact_person' => $this->contact_person,
            'phone' => $this->phone,
            'address' => $this->address,
        ], [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ]);

        Auth::login($bidder->user);
        session()->regenerate();

        session()->flash('status', 'Registration successful! Please upload your eligibility documents (PhilGEPS registration, Mayor\'s Permit, Tax Clearance, SEC/DTI, PCAB) for verification.');

        $this->redirect(route('bidder.profile'), navigate: false);
    }

    public function render()
    {
        return view('livewire.portal.register');
    }
}
