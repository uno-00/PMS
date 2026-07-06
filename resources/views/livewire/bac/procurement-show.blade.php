<div>
    <x-page-header :title="$procurement->title" :subtitle="'Case No. '.$procurement->case_no.' · PR '.$procurement->purchaseRequest?->pr_no">
        <x-slot:actions>
            <x-status-badge :status="$procurement->status" class="!text-sm" />

            @can('bac-calendar.manage')
                <x-button wire:click="openModal('schedule')" variant="secondary" size="sm">Schedule Activity</x-button>
            @endcan

            @if($procurement->status === \App\Enums\ProcurementCaseStatus::Planning && $procurement->requiresBidding())
                @can('philgeps.post')
                    <x-button wire:click="openModal('philgeps')" size="sm">Post to PhilGEPS</x-button>
                @endcan
            @endif

            @if($procurement->philgepsPosting?->status === \App\Enums\PhilgepsPostingStatus::Published)
                @can('philgeps.manage')
                    <x-button wire:click="closePosting" variant="secondary" size="sm">Close Posting</x-button>
                    <x-button wire:click="openModal('upload-docs')" variant="secondary" size="sm">Upload Bid Docs</x-button>
                @endcan
            @endif

            @if($procurement->status === \App\Enums\ProcurementCaseStatus::Bidding)
                @can('bid-opening.conduct')
                    <x-button wire:click="openBids" size="sm">Open Bids</x-button>
                @endcan
            @endif

            @if(in_array($procurement->status, [\App\Enums\ProcurementCaseStatus::Evaluation, \App\Enums\ProcurementCaseStatus::PostQualification]))
                @can('post-qualification.process')
                    <x-button wire:click="openModal('postqual')" variant="secondary" size="sm">Post-Qualification</x-button>
                @endcan
            @endif

            @if(in_array($procurement->status, [\App\Enums\ProcurementCaseStatus::PostQualification, \App\Enums\ProcurementCaseStatus::Planning]))
                @can('award.generate')
                    <x-button wire:click="openModal('noa')" size="sm">Issue Notice of Award</x-button>
                @endcan
            @endif

            @if($procurement->status === \App\Enums\ProcurementCaseStatus::Awarded)
                @can('ntp.generate')
                    <x-button wire:click="openModal('ntp')" size="sm">Issue Notice to Proceed</x-button>
                @endcan
            @endif

            @if(in_array($procurement->status, [\App\Enums\ProcurementCaseStatus::NtpIssued, \App\Enums\ProcurementCaseStatus::Planning]))
                @can('purchase-order.create')
                    <x-button wire:click="openModal('po')" size="sm">Create Purchase Order</x-button>
                @endcan
            @endif
        </x-slot:actions>
    </x-page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <x-stat-card label="ABC" :value="'₱'.number_format($procurement->abc, 2)" icon="banknotes" />
        <x-stat-card label="Mode" :value="$procurement->modeOfProcurement?->name ?? '—'" icon="briefcase" accent="indigo" />
        <x-stat-card label="Bids Received" :value="$procurement->bidSubmissions->count()" icon="document-text" accent="amber" />
        <x-stat-card label="Clarifications" :value="$procurement->clarifications->count()" icon="question-mark-circle" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">

            @if($procurement->philgepsPosting)
                <x-card title="PhilGEPS Posting">
                    <dl class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                        <div><dt class="text-slate-400">Reference No.</dt><dd class="font-medium">{{ $procurement->philgepsPosting->reference_no ?? '—' }}</dd></div>
                        <div><dt class="text-slate-400">Posted</dt><dd class="font-medium">{{ $procurement->philgepsPosting->posting_date->format('M d, Y') }}</dd></div>
                        <div><dt class="text-slate-400">Closes</dt><dd class="font-medium">{{ $procurement->philgepsPosting->closing_date->format('M d, Y') }}</dd></div>
                        <div><dt class="text-slate-400">Status</dt><dd><x-status-badge :status="$procurement->philgepsPosting->status" /></dd></div>
                    </dl>
                    @if($procurement->philgepsPosting->documents->isNotEmpty())
                        <div class="mt-3 space-y-1">
                            @foreach($procurement->philgepsPosting->documents as $doc)
                                <p class="text-xs text-slate-500">📄 {{ $doc->original_filename }} (v{{ $doc->version }})</p>
                            @endforeach
                        </div>
                    @endif
                </x-card>
            @endif

            <x-card title="Bid Submissions">
                @forelse($procurement->bidSubmissions as $bid)
                    <div class="flex items-center justify-between border-b border-slate-100 py-3 last:border-0 dark:border-slate-800">
                        <div>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $bid->bidder?->company_name }}</p>
                            <p class="text-xs text-slate-400">{{ $bid->bid_no }} &middot; v{{ $bid->version }} &middot; {{ $bid->submitted_at?->format('M d, Y g:ia') }} @if($bid->is_late)<span class="text-red-500">(late)</span>@endif</p>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <x-status-badge :status="new \App\Support\SimpleStatus($bid->status)" />
                            @if($bid->evaluation)
                                <span class="text-slate-400">Rank {{ $bid->evaluation->rank ?? '—' }}</span>
                            @else
                                @can('bid-evaluation.evaluate')
                                    <x-button wire:click="evaluateBid('{{ $bid->id }}')" size="sm" variant="secondary">Quick Evaluate</x-button>
                                @endcan
                            @endif
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="document-text" title="No bids submitted yet" />
                @endforelse
                @can('bid-evaluation.view')
                    <div class="mt-3 text-right">
                        <a href="{{ route('procurements.evaluation', $procurement) }}" class="text-sm font-medium text-primary-600 hover:underline">Open Evaluation Matrix &rarr;</a>
                    </div>
                @endcan
            </x-card>

            <x-card title="Clarifications">
                @forelse($procurement->clarifications as $c)
                    <div class="border-b border-slate-100 py-3 last:border-0 dark:border-slate-800">
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $c->bidder?->company_name }} asked:</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ $c->question }}</p>
                        @if($c->answer)
                            <p class="mt-1 text-sm text-emerald-600">Answer: {{ $c->answer }}</p>
                        @elseif($answeringClarificationId === $c->id)
                            <div class="mt-2">
                                <textarea wire:model="answer_text" rows="2" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"></textarea>
                                @error('answer_text') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                <div class="mt-2 flex gap-2">
                                    <x-button size="sm" wire:click="answerClarification('{{ $c->id }}')">Submit Answer</x-button>
                                    <x-button size="sm" variant="secondary" wire:click="$set('answeringClarificationId', null)">Cancel</x-button>
                                </div>
                            </div>
                        @else
                            @can('clarification.answer')
                                <x-button size="sm" variant="secondary" class="mt-2" wire:click="$set('answeringClarificationId', '{{ $c->id }}')">Answer</x-button>
                            @endcan
                        @endif
                    </div>
                @empty
                    <x-empty-state icon="question-mark-circle" title="No clarifications asked" />
                @endforelse
            </x-card>

            @if($procurement->purchaseOrders->isNotEmpty())
                <x-card title="Purchase Orders">
                    @foreach($procurement->purchaseOrders as $po)
                        <div class="flex items-center justify-between border-b border-slate-100 py-3 last:border-0 dark:border-slate-800">
                            <div>
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $po->po_no }}</p>
                                <p class="text-xs text-slate-400">₱{{ number_format($po->total_amount, 2) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-status-badge :status="$po->status" />
                                <x-button href="{{ route('purchase-orders.show', $po) }}" size="sm" variant="secondary">View</x-button>
                            </div>
                        </div>
                    @endforeach
                </x-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-card title="Upcoming / Past Activities">
                @forelse($procurement->calendarEvents->sortByDesc('scheduled_at') as $event)
                    <div class="border-b border-slate-100 py-2 last:border-0 dark:border-slate-800">
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $event->typeLabel() }}</p>
                        <p class="text-xs text-slate-400">{{ $event->scheduled_at->format('M d, Y g:ia') }} @if($event->venue) &middot; {{ $event->venue }} @endif</p>
                    </div>
                @empty
                    <x-empty-state icon="calendar" title="No activities scheduled" />
                @endforelse
            </x-card>

            @if($procurement->noticeOfAward)
                <x-card title="Notice of Award">
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-slate-400">NOA No.</dt><dd class="font-medium">{{ $procurement->noticeOfAward->noa_no }}</dd></div>
                        <div><dt class="text-slate-400">Awarded To</dt><dd class="font-medium">{{ $procurement->noticeOfAward->bidder?->company_name }}</dd></div>
                        <div><dt class="text-slate-400">Amount</dt><dd class="font-medium">₱{{ number_format($procurement->noticeOfAward->amount, 2) }}</dd></div>
                        <div><dt class="text-slate-400">Status</dt><dd><x-status-badge :status="$procurement->noticeOfAward->status" /></dd></div>
                    </dl>
                    <x-button href="{{ route('procurements.noa.print', $procurement) }}" variant="secondary" size="sm" class="mt-3"><x-icon name="printer" class="h-4 w-4" /> Print NOA</x-button>
                </x-card>
            @endif

            @if($procurement->noticeToProceed)
                <x-card title="Notice to Proceed">
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-slate-400">NTP No.</dt><dd class="font-medium">{{ $procurement->noticeToProceed->ntp_no }}</dd></div>
                        <div><dt class="text-slate-400">Effectivity</dt><dd class="font-medium">{{ $procurement->noticeToProceed->effectivity_date->format('M d, Y') }}</dd></div>
                        <div><dt class="text-slate-400">Completion</dt><dd class="font-medium">{{ $procurement->noticeToProceed->completion_date?->format('M d, Y') ?? '—' }}</dd></div>
                    </dl>
                    <x-button href="{{ route('procurements.ntp.print', $procurement) }}" variant="secondary" size="sm" class="mt-3"><x-icon name="printer" class="h-4 w-4" /> Print NTP</x-button>
                </x-card>
            @endif

            <x-card title="Workflow History">
                <x-workflow-timeline :history="$history" />
            </x-card>
        </div>
    </div>

    {{-- Modals --}}
    @if($activeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:click.self="closeModal">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">

                @if($activeModal === 'schedule')
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Schedule BAC Activity</h3>
                    <div class="mt-4 space-y-3">
                        <select wire:model="activity_type" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @foreach(\App\Models\Bac\BacCalendarEvent::TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input wire:model="scheduled_at" type="datetime-local" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('scheduled_at') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <input wire:model="venue" type="text" placeholder="Venue" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button variant="secondary" wire:click="closeModal">Cancel</x-button>
                        <x-button wire:click="scheduleActivity">Schedule</x-button>
                    </div>
                @elseif($activeModal === 'philgeps')
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Post to PhilGEPS</h3>
                    <div class="mt-4 space-y-3">
                        <input wire:model="reference_no" type="text" placeholder="Reference No. (leave blank if posting manually later)" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <input wire:model="closing_date" type="date" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('closing_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <textarea wire:model="remarks" rows="2" placeholder="Remarks" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button variant="secondary" wire:click="closeModal">Cancel</x-button>
                        <x-button wire:click="postToPhilgeps">Post</x-button>
                    </div>
                @elseif($activeModal === 'upload-docs')
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Upload Bidding Documents</h3>
                    <div class="mt-4 space-y-3">
                        <input type="file" wire:model="biddingDocumentFile" class="block w-full text-sm">
                        @error('biddingDocumentFile') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button variant="secondary" wire:click="closeModal">Cancel</x-button>
                        <x-button wire:click="uploadBiddingDocument">Upload</x-button>
                    </div>
                @elseif($activeModal === 'postqual')
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Post-Qualification</h3>
                    <div class="mt-4 space-y-3">
                        <select wire:model="bidder_id" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">Select bidder</option>
                            @foreach($bidders as $b)
                                <option value="{{ $b->id }}">{{ $b->company_name }}</option>
                            @endforeach
                        </select>
                        @error('bidder_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="site_visit_conducted"> Site visit conducted</label>
                        <textarea wire:model="document_validation_notes" rows="2" placeholder="Document validation notes" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                        <select wire:model="result" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="passed">Passed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button variant="secondary" wire:click="closeModal">Cancel</x-button>
                        <x-button wire:click="processPostQualification">Save</x-button>
                    </div>
                @elseif($activeModal === 'noa')
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Issue Notice of Award</h3>
                    <div class="mt-4 space-y-3">
                        <select wire:model="bidder_id" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">Select awarded bidder</option>
                            @foreach($bidders as $b)
                                <option value="{{ $b->id }}">{{ $b->company_name }}</option>
                            @endforeach
                        </select>
                        @error('bidder_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <input wire:model="amount" type="number" step="0.01" placeholder="Awarded Amount" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('amount') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <textarea wire:model="remarks" rows="2" placeholder="Remarks" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button variant="secondary" wire:click="closeModal">Cancel</x-button>
                        <x-button wire:click="issueNoa">Issue NOA</x-button>
                    </div>
                @elseif($activeModal === 'ntp')
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Issue Notice to Proceed</h3>
                    <div class="mt-4 space-y-3">
                        <input wire:model="effectivity_date" type="date" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('effectivity_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <input wire:model="contract_duration_days" type="number" placeholder="Contract Duration (days)" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button variant="secondary" wire:click="closeModal">Cancel</x-button>
                        <x-button wire:click="issueNtp">Issue NTP</x-button>
                    </div>
                @elseif($activeModal === 'po')
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Create Purchase Order</h3>
                    <div class="mt-4 space-y-3">
                        <input wire:model="delivery_date" type="date" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('delivery_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <input wire:model="delivery_place" type="text" placeholder="Delivery Place" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('delivery_place') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <div class="max-h-48 space-y-1 overflow-y-auto text-xs text-slate-500">
                            @foreach($po_items as $item)
                                <div class="flex justify-between rounded bg-slate-50 px-2 py-1 dark:bg-slate-800">
                                    <span>{{ $item['item_name'] }}</span>
                                    <span>{{ $item['quantity'] }} {{ $item['unit'] }} &times; ₱{{ number_format((float) $item['unit_cost'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button variant="secondary" wire:click="closeModal">Cancel</x-button>
                        <x-button wire:click="createPurchaseOrder">Create as Draft</x-button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
