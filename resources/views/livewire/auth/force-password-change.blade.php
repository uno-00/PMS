<div>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Set a new password</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            For security, you must set a new password before continuing.
        </p>
    </div>

    <form wire:submit="update" class="space-y-5">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Current password</label>
            <input wire:model="current_password" type="password"
                   class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
            @error('current_password') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">New password</label>
            <input wire:model="password" type="password"
                   class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
            @error('password') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-slate-400">Minimum 12 characters, with upper/lowercase letters, numbers, and symbols.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Confirm new password</label>
            <input wire:model="password_confirmation" type="password"
                   class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm">
        </div>

        <button type="submit" class="w-full rounded-lg bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-800">
            Update password
        </button>
    </form>
</div>
