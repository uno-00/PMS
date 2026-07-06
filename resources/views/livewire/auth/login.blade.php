<div x-data="{ open: false, showPassword: false }">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Sign in to your account</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Use your agency-issued credentials to continue.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="space-y-5">
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email address</label>
            <div class="mt-1.5 flex items-stretch overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm transition focus-within:border-primary-500 focus-within:ring-2 focus-within:ring-primary-500/40 dark:border-slate-700 dark:bg-slate-900">
                <span class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 text-slate-400 dark:border-slate-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0-.414.336-.75.75-.75h18a.75.75 0 01.75.75v10.5a.75.75 0 01-.75.75H3a.75.75 0 01-.75-.75V6.75z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.4 6.32l9.6 6.4 9.6-6.4" />
                    </svg>
                </span>
                <input wire:model.live="email" id="email" type="email" autofocus autocomplete="username"
                       placeholder="you@agency.gov.ph"
                       class="min-w-0 flex-1 border-0 bg-transparent py-2.5 pl-3 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0 dark:text-white dark:placeholder:text-slate-500">
            </div>
            @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
            <div class="mt-1.5 flex items-stretch overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm transition focus-within:border-primary-500 focus-within:ring-2 focus-within:ring-primary-500/40 dark:border-slate-700 dark:bg-slate-900">
                <span class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 text-slate-400 dark:border-slate-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </span>
                <input wire:model.live="password" id="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password"
                       placeholder="Enter your password"
                       class="min-w-0 flex-1 border-0 bg-transparent py-2.5 pl-3 pr-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0 dark:text-white dark:placeholder:text-slate-500">
                <button type="button" @click="showPassword = !showPassword" tabindex="-1" aria-label="Toggle password visibility"
                        class="flex w-11 shrink-0 items-center justify-center border-l border-slate-200 text-slate-400 hover:text-slate-600 dark:border-slate-700 dark:hover:text-slate-300">
                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            @error('password') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input wire:model="remember" type="checkbox" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                Remember me
            </label>
        </div>

        <button type="submit"
                class="flex w-full items-center justify-center rounded-lg bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login">Signing in&hellip;</span>
        </button>
    </form>

    <p class="mt-8 text-center text-xs text-slate-400">
        Bidder/Supplier? <a href="{{ route('bidder.login') }}" class="font-medium text-primary-600 hover:underline">Go to the Supplier Portal</a>
    </p>

    @if ($this->demoAccountsEnabled)
        <div class="mt-8 rounded-lg border border-dashed border-slate-300 dark:border-slate-700">
            <button type="button" @click="open = !open"
                    class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-medium text-slate-600 dark:text-slate-300">
                <span>Demo / test accounts (UAT only)</span>
                <svg :class="open ? 'rotate-180' : ''" class="h-4 w-4 shrink-0 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="open" x-cloak x-transition class="border-t border-dashed border-slate-300 px-4 py-3 dark:border-slate-700">
                <p class="mb-2 text-xs text-slate-500 dark:text-slate-400">
                    Password for every account:
                    <code class="rounded bg-slate-100 px-1 py-0.5 font-mono dark:bg-slate-800">{{ \App\Support\DemoAccounts::PASSWORD }}</code>.
                    Click a role to sign in instantly.
                </p>
                <div class="grid max-h-56 grid-cols-1 gap-1 overflow-y-auto pr-1 sm:grid-cols-2">
                    @foreach ($this->demoAccounts as $account)
                        <button type="button"
                                wire:click="loginAsDemo('{{ $account['email'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="loginAsDemo"
                                class="flex flex-col rounded-md px-2 py-1.5 text-left text-xs transition hover:bg-slate-100 disabled:opacity-50 dark:hover:bg-slate-800">
                            <span class="font-medium text-slate-700 dark:text-slate-200">{{ $account['role'] }}</span>
                            <span class="text-slate-400">{{ $account['email'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
