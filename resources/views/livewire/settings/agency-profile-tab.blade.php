<div>
    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Agency Profile</h3>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <form wire:submit="save" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2 flex items-center gap-4">
            @if($logoUrl)
                <img wire:key="agency-logo-{{ $profile->updated_at?->timestamp ?? 'new' }}" src="{{ $logoUrl }}" alt="Agency Logo" class="h-16 w-16 rounded-lg border border-slate-200 object-contain bg-white p-1 dark:border-slate-700 dark:bg-slate-800">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 text-xs text-slate-400 dark:border-slate-700 dark:bg-slate-800">No logo</div>
            @endif
            <div class="flex-1">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Agency Logo</label>
                <input wire:model="logo" type="file" accept="image/png,image/jpeg,image/jpg,image/gif,image/webp,image/svg+xml" class="mt-1 block w-full text-xs text-slate-500 file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-xs dark:file:bg-slate-800 dark:text-slate-400">
                <p class="mt-1 text-xs text-slate-400">PNG, JPG, GIF, WebP, or SVG. Max 2 MB.</p>
                @error('logo') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="sm:col-span-2 border-t border-slate-200 pt-4 dark:border-slate-700">
            <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">General Settings — Login Page Display</h4>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Background image shown on the left panel of the internal and bidder login pages.
            </p>

            @if ($loginBackgroundUrl)
                <div
                    wire:key="login-background-{{ $profile->updated_at?->timestamp ?? 'new' }}"
                    class="mt-3 h-36 w-full max-w-md overflow-hidden rounded-lg border border-slate-200 bg-cover bg-center dark:border-slate-700"
                    style="background-image: url('{{ $loginBackgroundUrl }}')"
                    role="img"
                    aria-label="Login page background preview"
                ></div>
            @endif

            @if ($canManageLoginBackground)
                <div class="mt-3 max-w-md">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Login Background Image</label>
                    <input wire:model="loginBackground" type="file" accept="image/png,image/jpeg,image/jpg,image/webp" class="mt-1 block w-full text-xs text-slate-500 file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-xs dark:file:bg-slate-800 dark:text-slate-400">
                    <p class="mt-1 text-xs text-slate-400">PNG, JPG, or WebP. Max 5 MB. Super Admin only.</p>
                    @error('loginBackground') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                    @if ($profile->hasCustomLoginBackground())
                        <label class="mt-2 flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                            <input wire:model="removeLoginBackground" type="checkbox" class="rounded border-slate-300 text-primary-600 dark:border-slate-600">
                            Remove custom image and restore the default BRHMC background
                        </label>
                    @endif
                </div>
            @else
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Only the Super Admin can change the login background image.</p>
            @endif
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Agency Name</label>
            <input wire:model="name" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            @error('name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Acronym</label>
            <input wire:model="acronym" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Agency Code</label>
            <input wire:model="agency_code" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Address</label>
            <textarea wire:model="address" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Region</label>
            <input wire:model="region" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">TIN</label>
            <input wire:model="tin" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Head of Procuring Entity (HOPE)</label>
            <input wire:model="head_of_agency" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">HOPE Position/Title</label>
            <input wire:model="hope_position" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">BAC Chairperson</label>
            <input wire:model="bac_chairperson" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Website</label>
            <input wire:model="website" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Contact Email</label>
            <input wire:model="contact_email" type="email" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            @error('contact_email') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Contact Phone</label>
            <input wire:model="contact_phone" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">PhilGEPS Organization ID</label>
            <input wire:model="philgeps_organization_id" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
        </div>

        <div class="sm:col-span-2">
            <x-button type="submit" wire:loading.attr="disabled" wire:target="save,logo,loginBackground">Save Agency Profile</x-button>
        </div>
    </form>
</div>
