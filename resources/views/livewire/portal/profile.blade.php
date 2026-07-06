<div>
    <x-page-header title="Company Profile" subtitle="Business information and eligibility documents used to evaluate your bids." />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card title="Business Information" class="lg:col-span-2">
            <form wire:submit="save" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Company Name</label>
                    <input wire:model="company_name" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    @error('company_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Business Type</label>
                    <select wire:model="business_type" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <option value="sole_proprietorship">Sole Proprietorship</option>
                        <option value="partnership">Partnership</option>
                        <option value="corporation">Corporation</option>
                        <option value="cooperative">Cooperative</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">TIN</label>
                    <input wire:model="tin" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Contact Person</label>
                    <input wire:model="contact_person" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Phone</label>
                    <input wire:model="phone" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Address</label>
                    <textarea wire:model="address" rows="2" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"></textarea>
                </div>

                <hr class="sm:col-span-2 border-slate-200 dark:border-slate-800">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">PhilGEPS Registration No.</label>
                    <input wire:model="philgeps_registration_no" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">PhilGEPS Expiry</label>
                    <input wire:model="philgeps_registration_expiry" type="date" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Mayor's Permit No.</label>
                    <input wire:model="mayor_permit_no" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Mayor's Permit Expiry</label>
                    <input wire:model="mayor_permit_expiry" type="date" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Tax Clearance No.</label>
                    <input wire:model="tax_clearance_no" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Tax Clearance Expiry</label>
                    <input wire:model="tax_clearance_expiry" type="date" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">SEC / DTI Registration No.</label>
                    <input wire:model="sec_dti_registration_no" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">PCAB License No. <span class="text-slate-400">(if applicable)</span></label>
                    <input wire:model="pcab_license_no" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">PCAB License Expiry</label>
                    <input wire:model="pcab_license_expiry" type="date" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>

                <div class="sm:col-span-2">
                    <x-button type="submit" wire:loading.attr="disabled" wire:target="save">Save Changes</x-button>
                </div>
            </form>
        </x-card>

        <x-card title="Eligibility Documents">
            <div class="space-y-3">
                @foreach(\App\Models\Supplier\Bidder::DOCUMENT_CATEGORIES as $category => $label)
                    @php $doc = $bidder->documents->firstWhere('category', $category); @endphp
                    <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $label }}</p>
                            @if($doc)
                                <span class="text-xs text-emerald-600">v{{ $doc->version }} uploaded</span>
                            @else
                                <span class="text-xs text-slate-400">Not uploaded</span>
                            @endif
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <input type="file" wire:model="documentFile" x-ref="file{{ $loop->index }}"
                                   @if($activeUploadCategory === $category) wire:loading.class="opacity-50" @endif
                                   class="block w-full text-xs text-slate-500 file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-xs dark:file:bg-slate-800 dark:text-slate-400">
                            <x-button size="sm" variant="secondary" wire:click="uploadDocument('{{ $category }}')" wire:loading.attr="disabled" wire:target="uploadDocument('{{ $category }}')">Upload</x-button>
                        </div>
                        @error('documentFile') @if($activeUploadCategory === $category)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@endif @enderror
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</div>
