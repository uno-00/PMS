<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document Verification &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 p-6 font-sans">
    <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <div class="mb-6 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-700 text-sm font-bold text-white">PMS</div>
            <div>
                <p class="text-sm font-semibold text-slate-900">{{ config('app.name') }}</p>
                <p class="text-xs text-slate-400">Public Document Verification</p>
            </div>
        </div>

        @if($record)
            <div class="mb-4 flex items-center gap-2 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l1.5 1.5 3.75-3.75M12 3l7.5 4.5v6c0 4.14-3.14 7.85-7.5 9-4.36-1.15-7.5-4.86-7.5-9v-6L12 3z" /></svg>
                This is a genuine, system-issued document.
            </div>

            <dl class="divide-y divide-slate-100 text-sm">
                <div class="flex justify-between py-2">
                    <dt class="text-slate-500">Document Type</dt>
                    <dd class="font-medium text-slate-800">{{ $label }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-500">Reference / Control No.</dt>
                    <dd class="font-mono font-medium text-slate-800">{{ $code }}</dd>
                </div>
                @if(method_exists($record, 'getAttribute') && $record->status ?? null)
                    <div class="flex items-center justify-between py-2">
                        <dt class="text-slate-500">Current Status</dt>
                        <dd><x-status-badge :status="$record->status" /></dd>
                    </div>
                @endif
                @if(isset($record->title))
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-500">Title</dt>
                        <dd class="text-right font-medium text-slate-800">{{ $record->title }}</dd>
                    </div>
                @endif
                @if(isset($record->amount))
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-500">Amount</dt>
                        <dd class="font-medium text-slate-800">₱{{ number_format($record->amount, 2) }}</dd>
                    </div>
                @endif
                @if(isset($record->total_amount))
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-500">Total Amount</dt>
                        <dd class="font-medium text-slate-800">₱{{ number_format($record->total_amount, 2) }}</dd>
                    </div>
                @endif
                <div class="flex justify-between py-2">
                    <dt class="text-slate-500">Issued / Created</dt>
                    <dd class="text-slate-800">{{ $record->created_at?->format('F d, Y g:ia') }}</dd>
                </div>
            </dl>
        @else
            <div class="rounded-lg bg-red-50 px-4 py-4 text-sm font-medium text-red-700">
                No matching document was found for this reference code. This document may not be genuine, or the code was entered incorrectly.
            </div>
        @endif

        <p class="mt-6 text-center text-xs text-slate-400">&copy; {{ date('Y') }} {{ config('app.name') }}. Generated for public verification purposes only.</p>
    </div>
</body>
</html>
