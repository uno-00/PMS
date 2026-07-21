<div>
    <x-page-header title="Help & User Manuals" subtitle="Step-by-step guides with real system screenshots for every module. Pick a module or role manual below." />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <nav class="space-y-4 lg:col-span-1">
            <div>
                <p class="px-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Module Guides</p>
                <div class="mt-1 space-y-1">
                    @foreach($modules as $key => $def)
                        <button type="button" wire:click="selectModule('{{ $key }}')"
                                class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium transition
                                    {{ $module === $key ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                            {{ $def['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="px-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Role Manuals</p>
                <div class="mt-1 space-y-2 px-1">
                    @foreach($roleManuals as $role)
                        <div class="rounded-lg border border-slate-200 p-2 dark:border-slate-800">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $role['label'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $role['summary'] }}</p>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach($role['modules'] as $modKey)
                                    <button type="button" wire:click="selectModule('{{ $modKey }}')" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700">{{ $modules[$modKey]['label'] ?? $modKey }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </nav>

        <div class="lg:col-span-3">
            <x-card :title="$active['label']">
                <div class="space-y-8">
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Purpose</h4>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $active['purpose'] }}</p>
                    </div>

                    <div>
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Step-by-Step Guide</h4>
                            <span class="text-xs text-slate-400">{{ count($active['steps']) }} steps · click screenshots to enlarge</span>
                        </div>
                        <div class="space-y-4">
                            @foreach($active['steps'] as $i => $step)
                                <x-help.step
                                    :number="$i + 1"
                                    :text="$step['text']"
                                    :screenshot="$step['screenshot'] ?? null"
                                    :caption="$step['caption'] ?? null"
                                />
                            @endforeach
                        </div>
                    </div>

                    @if($active['workflow'])
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Workflow Diagram</h4>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                @foreach($active['workflow'] as $step)
                                    <span class="rounded-full bg-primary-50 px-3 py-1 text-xs font-medium text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">{{ $step }}</span>
                                    @if(!$loop->last)
                                        <x-icon name="arrow-left" class="h-3 w-3 rotate-180 text-slate-300" />
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Approval Process</h4>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $active['approval'] }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Common Errors</h4>
                            <ul class="mt-2 list-disc space-y-1.5 pl-5 text-sm text-slate-600 dark:text-slate-300">
                                @foreach($active['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-400">FAQs</h4>
                            <ul class="mt-2 list-disc space-y-1.5 pl-5 text-sm text-slate-600 dark:text-slate-300">
                                @foreach($active['faqs'] as $faq)
                                    <li>{{ $faq }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="rounded-lg bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                        <span class="font-semibold">Tip:</span> {{ $active['tips'] }}
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>
