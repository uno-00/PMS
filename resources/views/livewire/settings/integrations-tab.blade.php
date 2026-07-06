<div>
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3 dark:border-slate-800">
        @foreach(['mail' => 'SMTP / Email', 'sms' => 'SMS Gateway', 'philgeps' => 'PhilGEPS', 'aws' => 'AWS S3', 'backup' => 'Database Backup'] as $key => $label)
            <button type="button" wire:click="switchSection('{{ $key }}')"
                    class="rounded-full px-3 py-1.5 text-xs font-medium transition
                        {{ $section === $key ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    @if($section === 'mail')
        <form wire:submit="saveMail" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">SMTP Host</label>
                <input wire:model="mail_host" type="text" placeholder="smtp.mailgun.org" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Port</label>
                <input wire:model="mail_port" type="number" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Username</label>
                <input wire:model="mail_username" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                <input wire:model="mail_password" type="password" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Encryption</label>
                <select wire:model="mail_encryption" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="tls">TLS</option><option value="ssl">SSL</option><option value="">None</option>
                </select></div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">From Address</label>
                <input wire:model="mail_from_address" type="email" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                @error('mail_from_address') <p class="text-xs text-red-600">{{ $message }}</p> @enderror</div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">From Name</label>
                <input wire:model="mail_from_name" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div class="sm:col-span-2"><x-button type="submit">Save SMTP Settings</x-button></div>
        </form>
    @elseif($section === 'sms')
        <form wire:submit="saveSms" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Provider</label>
                <select wire:model="sms_provider" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="none">Disabled</option><option value="semaphore">Semaphore</option><option value="twilio">Twilio</option>
                </select></div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Sender ID</label>
                <input wire:model="sms_sender_id" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">API Key</label>
                <input wire:model="sms_api_key" type="password" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div class="sm:col-span-2"><x-button type="submit">Save SMS Settings</x-button></div>
        </form>
    @elseif($section === 'philgeps')
        <form wire:submit="savePhilgeps" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Posting Mode</label>
                <select wire:model="philgeps_mode" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="manual">Manual Upload</option><option value="api">API Integration</option>
                </select></div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Organization ID</label>
                <input wire:model="philgeps_organization_id" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            @if($philgeps_mode === 'api')
                <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">API URL</label>
                    <input wire:model="philgeps_api_url" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
                <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">API Key</label>
                    <input wire:model="philgeps_api_key" type="password" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            @endif
            <div class="sm:col-span-2"><x-button type="submit">Save PhilGEPS Settings</x-button></div>
        </form>
    @elseif($section === 'aws')
        <form wire:submit="saveAws" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Access Key ID</label>
                <input wire:model="aws_access_key_id" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Secret Access Key</label>
                <input wire:model="aws_secret_access_key" type="password" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Region</label>
                <input wire:model="aws_region" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Bucket</label>
                <input wire:model="aws_bucket" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Custom URL / CDN (optional)</label>
                <input wire:model="aws_url" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <p class="sm:col-span-2 text-xs text-slate-400">Bucket layout: /fiscal-year/ /ppmp/ /app/ /pr/ /caf/ /bac/ /philgeps/ /bids/ /award/ /ntp/ /purchase-order/ /reports/ — see docs/AWS_S3_SETUP.md.</p>
            <div class="sm:col-span-2"><x-button type="submit">Save AWS Settings</x-button></div>
        </form>
    @elseif($section === 'backup')
        <form wire:submit="saveBackup" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 sm:col-span-2">
                <input wire:model="backup_enabled" type="checkbox" value="1" class="rounded border-slate-300 text-primary-600"> Enable scheduled database backups
            </label>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Daily Run Time</label>
                <input wire:model="backup_time" type="time" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Retention (days)</label>
                <input wire:model="backup_retention_days" type="number" min="1" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                @error('backup_retention_days') <p class="text-xs text-red-600">{{ $message }}</p> @enderror</div>
            <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Storage Disk</label>
                <select wire:model="backup_disk" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="s3">AWS S3</option><option value="local">Local</option>
                </select></div>
            <div class="sm:col-span-2"><x-button type="submit">Save Backup Settings</x-button></div>
        </form>
    @endif
</div>
