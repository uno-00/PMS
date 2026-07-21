<?php

namespace App\Livewire\Settings;

use App\Models\Settings\SystemSetting;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Settings > Integrations: SMTP/Email, SMS gateway, PhilGEPS API/manual
 * mode, AWS S3, and database backup schedule. Persisted through the
 * generic SystemSetting key/value store; sensitive fields (SMTP/SMS
 * passwords, AWS secret key) are stored encrypted.
 */
class IntegrationsTab extends Component
{
    public string $section = 'mail';

    // Mail
    public string $mail_host = '';

    public string $mail_port = '587';

    public string $mail_username = '';

    public string $mail_password = '';

    public string $mail_encryption = 'tls';

    public string $mail_from_address = '';

    public string $mail_from_name = '';

    // SMS
    public string $sms_provider = 'none';

    public string $sms_api_key = '';

    public string $sms_sender_id = '';

    // PhilGEPS
    public string $philgeps_mode = 'manual';

    public string $philgeps_api_url = '';

    public string $philgeps_api_key = '';

    public string $philgeps_organization_id = '';

    // AWS S3
    public string $aws_access_key_id = '';

    public string $aws_secret_access_key = '';

    public string $aws_region = 'ap-southeast-1';

    public string $aws_bucket = '';

    public string $aws_url = '';

    // Backup
    public string $backup_enabled = '1';

    public string $backup_time = '01:00';

    public string $backup_retention_days = '30';

    public string $backup_disk = 's3';

    public function mount(): void
    {
        Gate::authorize('settings.manage');
        $this->loadAll();
    }

    protected function loadAll(): void
    {
        $mail = SystemSetting::group('mail');
        $sms = SystemSetting::group('sms');
        $philgeps = SystemSetting::group('philgeps');
        $aws = SystemSetting::group('aws');
        $backup = SystemSetting::group('backup');

        $this->mail_host = $mail['host'] ?? '';
        $this->mail_port = (string) ($mail['port'] ?? '587');
        $this->mail_username = $mail['username'] ?? '';
        $this->mail_password = $mail['password'] ?? '';
        $this->mail_encryption = $mail['encryption'] ?? 'tls';
        $this->mail_from_address = $mail['from_address'] ?? '';
        $this->mail_from_name = $mail['from_name'] ?? '';

        $this->sms_provider = $sms['provider'] ?? 'none';
        $this->sms_api_key = $sms['api_key'] ?? '';
        $this->sms_sender_id = $sms['sender_id'] ?? '';

        $this->philgeps_mode = $philgeps['mode'] ?? 'manual';
        $this->philgeps_api_url = $philgeps['api_url'] ?? '';
        $this->philgeps_api_key = $philgeps['api_key'] ?? '';
        $this->philgeps_organization_id = $philgeps['organization_id'] ?? '';

        $this->aws_access_key_id = $aws['access_key_id'] ?? '';
        $this->aws_secret_access_key = $aws['secret_access_key'] ?? '';
        $this->aws_region = $aws['region'] ?? 'ap-southeast-1';
        $this->aws_bucket = $aws['bucket'] ?? '';
        $this->aws_url = $aws['url'] ?? '';

        $this->backup_enabled = (string) ($backup['enabled'] ?? '1');
        $this->backup_time = $backup['time'] ?? '01:00';
        $this->backup_retention_days = (string) ($backup['retention_days'] ?? '30');
        $this->backup_disk = $backup['disk'] ?? 's3';
    }

    public function switchSection(string $section): void
    {
        $this->section = $section;
    }

    public function saveMail(): void
    {
        Gate::authorize('settings.manage');
        $this->validate([
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|numeric',
            'mail_from_address' => 'nullable|email',
        ]);

        SystemSetting::set('mail', 'host', $this->mail_host);
        SystemSetting::set('mail', 'port', $this->mail_port);
        SystemSetting::set('mail', 'username', $this->mail_username);
        SystemSetting::set('mail', 'password', $this->mail_password, encrypted: true);
        SystemSetting::set('mail', 'encryption', $this->mail_encryption);
        SystemSetting::set('mail', 'from_address', $this->mail_from_address);
        SystemSetting::set('mail', 'from_name', $this->mail_from_name);

        session()->flash('status', 'SMTP/Email settings updated.');
    }

    public function saveSms(): void
    {
        Gate::authorize('settings.manage');

        SystemSetting::set('sms', 'provider', $this->sms_provider);
        SystemSetting::set('sms', 'api_key', $this->sms_api_key, encrypted: true);
        SystemSetting::set('sms', 'sender_id', $this->sms_sender_id);

        session()->flash('status', 'SMS gateway settings updated.');
    }

    public function savePhilgeps(): void
    {
        Gate::authorize('settings.manage');

        SystemSetting::set('philgeps', 'mode', $this->philgeps_mode);
        SystemSetting::set('philgeps', 'api_url', $this->philgeps_api_url);
        SystemSetting::set('philgeps', 'api_key', $this->philgeps_api_key, encrypted: true);
        SystemSetting::set('philgeps', 'organization_id', $this->philgeps_organization_id);

        session()->flash('status', 'PhilGEPS configuration updated.');
    }

    public function saveAws(): void
    {
        Gate::authorize('settings.manage');
        $this->validate(['aws_region' => 'required|string', 'aws_bucket' => 'nullable|string']);

        SystemSetting::set('aws', 'access_key_id', $this->aws_access_key_id, encrypted: true);
        SystemSetting::set('aws', 'secret_access_key', $this->aws_secret_access_key, encrypted: true);
        SystemSetting::set('aws', 'region', $this->aws_region);
        SystemSetting::set('aws', 'bucket', $this->aws_bucket);
        SystemSetting::set('aws', 'url', $this->aws_url);

        session()->flash('status', 'AWS S3 configuration updated.');
    }

    public function saveBackup(): void
    {
        Gate::authorize('settings.manage');
        $this->validate(['backup_retention_days' => 'required|integer|min:1|max:3650']);

        SystemSetting::set('backup', 'enabled', $this->backup_enabled);
        SystemSetting::set('backup', 'time', $this->backup_time);
        SystemSetting::set('backup', 'retention_days', $this->backup_retention_days);
        SystemSetting::set('backup', 'disk', $this->backup_disk);

        session()->flash('status', 'Database backup schedule updated.');
    }

    public function render()
    {
        return view('livewire.settings.integrations-tab');
    }
}
