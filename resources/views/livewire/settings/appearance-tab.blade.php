<div>
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <p class="text-sm text-slate-600 dark:text-slate-400">
        Customize the primary brand color and login-page gradient. Changes apply immediately across the internal portal and login screens.
    </p>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach($presets as $key => $preset)
            <button type="button" wire:click="applyPreset('{{ $key }}')"
                    class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                {{ $preset['label'] }}
            </button>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Primary brand color</label>
                <div class="mt-1.5 flex items-center gap-3">
                    <input type="color" wire:model.live="primary" class="h-10 w-14 cursor-pointer rounded border border-slate-300 dark:border-slate-700">
                    <input type="text" wire:model.live="primary" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
                @error('primary') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Gradient start</label>
                <div class="mt-1.5 flex items-center gap-3">
                    <input type="color" wire:model.live="gradient_from" class="h-10 w-14 cursor-pointer rounded border border-slate-300 dark:border-slate-700">
                    <input type="text" wire:model.live="gradient_from" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Gradient middle</label>
                <div class="mt-1.5 flex items-center gap-3">
                    <input type="color" wire:model.live="gradient_via" class="h-10 w-14 cursor-pointer rounded border border-slate-300 dark:border-slate-700">
                    <input type="text" wire:model.live="gradient_via" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Gradient end</label>
                <div class="mt-1.5 flex items-center gap-3">
                    <input type="color" wire:model.live="gradient_to" class="h-10 w-14 cursor-pointer rounded border border-slate-300 dark:border-slate-700">
                    <input type="text" wire:model.live="gradient_to" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                </div>
            </div>

            <div class="flex flex-wrap gap-2 pt-2">
                <x-button wire:click="save">Save theme</x-button>
                <x-button wire:click="resetDefaults" variant="secondary" wire:confirm="Restore default blue theme colors?">Reset defaults</x-button>
            </div>
        </div>

        <div>
            <p class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">Live preview</p>
            <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="theme-gradient-panel p-8 text-white">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10 text-lg font-bold">PMS</div>
                        <span class="text-lg font-semibold">Procurement Management System</span>
                    </div>
                    <p class="mt-6 text-2xl font-bold leading-tight">Enterprise Procurement,<br>from GAA to Payment.</p>
                    <p class="mt-3 max-w-sm text-sm text-white/80">Preview of the login-page gradient and brand panel.</p>
                </div>
                <div class="space-y-3 bg-white p-4 dark:bg-slate-900">
                    <button type="button" class="w-full rounded-lg bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white">Primary button</button>
                    <p class="text-sm text-primary-700 dark:text-primary-300">Primary link / accent text sample</p>
                    <span class="inline-flex rounded-full bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">Status badge</span>
                </div>
            </div>
        </div>
    </div>
</div>
