<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Register Your Company</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create your Supplier/Bidder account to participate in government procurement opportunities.</p>
    </div>

    <form wire:submit="register" class="space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Company Name</label>
                <input wire:model="company_name" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
                @error('company_name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Business Type</label>
                <select wire:model="business_type" class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
                    <option value="sole_proprietorship">Sole Proprietorship</option>
                    <option value="partnership">Partnership</option>
                    <option value="corporation">Corporation</option>
                    <option value="cooperative">Cooperative</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">TIN</label>
                <input wire:model="tin" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Contact Person</label>
                <input wire:model="contact_person" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
                @error('contact_person') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Phone</label>
                <input wire:model="phone" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Address</label>
                <textarea wire:model="address" rows="2" class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm"></textarea>
            </div>
        </div>

        <hr class="border-slate-200 dark:border-slate-800">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Your Name</label>
                <input wire:model="name" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
                @error('name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email address</label>
                <input wire:model="email" type="email" autocomplete="username" class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
                @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                <input wire:model="password" type="password" autocomplete="new-password" class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
                @error('password') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Confirm Password</label>
                <input wire:model="password_confirmation" type="password" autocomplete="new-password" class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
            </div>
        </div>

        <button type="submit" class="flex w-full items-center justify-center rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="register">Create Account</span>
            <span wire:loading wire:target="register">Creating&hellip;</span>
        </button>
    </form>

    <p class="mt-6 text-center text-xs text-slate-400">
        Already registered? <a href="{{ route('bidder.login') }}" class="font-medium text-indigo-600 hover:underline">Sign in</a>
    </p>
</div>
