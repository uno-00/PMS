<?php

namespace App\Services\Supplier;

use App\Models\Supplier\Bidder;
use App\Models\User;
use App\Services\Support\DocumentStorageService;
use App\Support\Roles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Phase 9: Bidder/Supplier Portal registration and eligibility document
 * management (PhilGEPS registration, Mayor's Permit, Tax Clearance,
 * SEC/DTI, PCAB).
 */
class BidderService
{
    public function __construct(protected DocumentStorageService $documents) {}

    public function register(array $companyAttributes, array $accountAttributes): Bidder
    {
        return DB::transaction(function () use ($companyAttributes, $accountAttributes) {
            $user = User::query()->create([
                'name' => $accountAttributes['name'],
                'email' => $accountAttributes['email'],
                'password' => Hash::make($accountAttributes['password']),
                'is_active' => true,
                'must_change_password' => false,
            ]);
            $user->assignRole(Roles::BIDDER);

            return Bidder::query()->create(array_merge($companyAttributes, [
                'user_id' => $user->id,
                'email' => $accountAttributes['email'],
                'status' => 'pending',
            ]));
        });
    }

    public function uploadEligibilityDocument(Bidder $bidder, string $category, UploadedFile $file, User $uploader): void
    {
        abort_unless(array_key_exists($category, Bidder::DOCUMENT_CATEGORIES), 422, 'Unknown eligibility document category.');

        $this->documents->store($file, $bidder, 'bids', $category, $uploader);
    }

    public function verify(Bidder $bidder, User $verifier): Bidder
    {
        $bidder->update(['status' => 'verified']);

        return $bidder->fresh();
    }

    public function suspend(Bidder $bidder, string $remarks): Bidder
    {
        $bidder->update(['status' => 'suspended', 'remarks' => $remarks]);

        return $bidder->fresh();
    }
}
