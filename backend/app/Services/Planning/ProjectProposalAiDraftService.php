<?php

namespace App\Services\Planning;

use App\Support\RichTextSanitizer;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Drafts NMP-PP-01 narrative sections (II–VI) from basic project metadata.
 * Uses OpenAI when configured; otherwise falls back to structured templates.
 */
class ProjectProposalAiDraftService
{
    protected ?string $lastFallbackReason = null;

    /** @var array<int, string> */
    public const SECTIONS = [
        'rationale',
        'objectives',
        'target_schedule',
        'budgetary_requirement',
        'fund_source_narrative',
    ];

    public function isAiConfigured(): bool
    {
        return filled(config('services.openai.api_key'));
    }

    public function usedTemplateFallback(): bool
    {
        return $this->lastFallbackReason !== null;
    }

    public function lastFallbackReason(): ?string
    {
        return $this->lastFallbackReason;
    }

    /** @return array<string, string> */
    public function draftAll(array $context): array
    {
        $this->lastFallbackReason = null;

        if ($this->isAiConfigured()) {
            try {
                return $this->sanitizeDrafts($this->draftWithOpenAi($context));
            } catch (Throwable $e) {
                if (! $this->shouldFallbackAfterOpenAiFailure($e)) {
                    throw $e;
                }

                $this->recordFallback($e);
            }
        }

        return $this->sanitizeDrafts($this->draftWithTemplates($context));
    }

    public function draftSection(string $section, array $context): string
    {
        abort_unless(in_array($section, self::SECTIONS, true), 422, 'Invalid narrative section.');

        $this->lastFallbackReason = null;

        if ($this->isAiConfigured()) {
            try {
                return $this->sanitizeHtml($this->draftSingleWithOpenAi($section, $context), $section);
            } catch (Throwable $e) {
                if (! $this->shouldFallbackAfterOpenAiFailure($e)) {
                    throw $e;
                }

                $this->recordFallback($e);
            }
        }

        $html = $this->draftWithTemplates($context)[$section] ?? '';

        return $this->sanitizeHtml($html, $section);
    }

    /** @param  array<string, string|null>  $drafts
     * @return array<string, string>
     */
    protected function sanitizeDrafts(array $drafts): array
    {
        $clean = [];

        foreach (self::SECTIONS as $section) {
            $clean[$section] = $this->sanitizeHtml($drafts[$section] ?? null, $section);
        }

        return $clean;
    }

    protected function sanitizeHtml(?string $html, string $section): string
    {
        $clean = RichTextSanitizer::clean($html) ?? '';

        if ($clean === '') {
            throw new RuntimeException('AI drafting returned empty content for '.$section.'.');
        }

        return $clean;
    }

    protected function openAiHttp(): PendingRequest
    {
        $retries = max(0, (int) config('services.openai.retries', 2));
        $retrySleepMs = max(0, (int) config('services.openai.retry_sleep_ms', 750));

        return Http::timeout((int) config('services.openai.timeout', 90))
            ->retry($retries, $retrySleepMs, function (Throwable $exception): bool {
                if ($exception instanceof ConnectionException) {
                    return true;
                }

                $message = strtolower($exception->getMessage());

                return str_contains($message, 'could not resolve host')
                    || str_contains($message, 'connection timed out')
                    || str_contains($message, 'failed to connect');
            }, throw: false)
            ->withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])
            ->withToken((string) config('services.openai.api_key'));
    }

    protected function shouldFallbackAfterOpenAiFailure(Throwable $exception): bool
    {
        if (! config('services.openai.fallback_to_templates', true)) {
            return false;
        }

        if ($exception instanceof ConnectionException) {
            return true;
        }

        if ($exception instanceof RuntimeException) {
            return true;
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'could not resolve host')
            || str_contains($message, 'connection timed out')
            || str_contains($message, 'failed to connect')
            || str_contains($message, 'curl error');
    }

    protected function recordFallback(Throwable $exception): void
    {
        $this->lastFallbackReason = Str::limit($exception->getMessage(), 180);

        Log::warning('OpenAI drafting unavailable; using smart templates.', [
            'error' => $exception->getMessage(),
        ]);
    }

    /** @return array<string, string> */
    protected function draftWithOpenAi(array $context): array
    {
        $response = $this->openAiHttp()->post(config('services.openai.url'), [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'temperature' => 0.35,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $this->userPrompt($context)],
                ],
            ]);

        if ($response === null) {
            throw new ConnectionException('Unable to reach OpenAI after retries.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('AI drafting failed: '.$this->extractApiError($response->json(), $response->body()));
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('AI drafting returned an empty response.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('AI drafting returned invalid JSON.');
        }

        return [
            'rationale' => (string) ($decoded['rationale'] ?? ''),
            'objectives' => (string) ($decoded['objectives'] ?? ''),
            'target_schedule' => (string) ($decoded['target_schedule'] ?? ''),
            'budgetary_requirement' => (string) ($decoded['budgetary_requirement'] ?? ''),
            'fund_source_narrative' => (string) ($decoded['fund_source_narrative'] ?? ''),
        ];
    }

    protected function draftSingleWithOpenAi(string $section, array $context): string
    {
        $label = $this->sectionLabel($section);

        $response = $this->openAiHttp()->post(config('services.openai.url'), [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'temperature' => 0.35,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $this->userPrompt($context)."\n\nDraft only Section {$label}. Return HTML only, no markdown fences."],
                ],
            ]);

        if ($response === null) {
            throw new ConnectionException('Unable to reach OpenAI after retries.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('AI drafting failed: '.$this->extractApiError($response->json(), $response->body()));
        }

        $content = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

        return preg_replace('/^```(?:html)?\s*|\s*```$/i', '', $content) ?? $content;
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
You are a procurement planning assistant for Philippine government agencies drafting NMP-PP-01 Project Proposal narrative sections aligned with RA 12009 and GPPB best practices.

Output valid HTML fragments only (no <html> or <body>). Allowed tags: p, strong, em, ul, ol, li, h3, blockquote, table, thead, tbody, tr, th, td, br.

Write formal, concise government procurement language. Use Philippine Peso (₱) formatting for amounts. Do not invent legal citations beyond RA 12009 / GPPB references when relevant.
PROMPT;
    }

    protected function userPrompt(array $context): string
    {
        return implode("\n", [
            'Draft the following NMP-PP-01 narrative sections as JSON with keys: rationale, objectives, target_schedule, budgetary_requirement, fund_source_narrative.',
            'Each value must be an HTML string.',
            '',
            'Project context:',
            '- Agency: '.($context['agency_name'] ?? 'Government Agency'),
            '- Division: '.($context['division_name'] ?? '—'),
            '- Fiscal year: '.($context['fiscal_year'] ?? '—'),
            '- Title: '.($context['title'] ?? '—'),
            '- Project type: '.($context['project_type'] ?? '—'),
            '- Schedule: '.($context['schedule'] ?? 'Not specified'),
            '- Venue / area: '.($context['venue_area'] ?? 'Not specified'),
            '- Total cost (ABC): ₱'.number_format((float) ($context['total_cost'] ?? 0), 2),
            '- Fund source (brief): '.($context['fund_source_text'] ?? 'MOOE'),
            '- Proponent: '.($context['proponent'] ?? '—'),
            '',
            'Section guidance:',
            '- rationale: need, urgency, alignment with agency mandate',
            '- objectives: general + specific measurable objectives',
            '- target_schedule: milestone timeline tied to the schedule field',
            '- budgetary_requirement: cost breakdown; use an HTML table when itemizing',
            '- fund_source_narrative: charge against GAA / fund source narrative',
        ]);
    }

    protected function sectionLabel(string $section): string
    {
        return match ($section) {
            'rationale' => 'II. Rationale',
            'objectives' => 'III. Objectives',
            'target_schedule' => 'IV. Target Schedule',
            'budgetary_requirement' => 'V. Budgetary Requirement',
            'fund_source_narrative' => 'VI. Fund Source',
            default => $section,
        };
    }

    /** @return array<string, string> */
    protected function draftWithTemplates(array $context): array
    {
        $title = e($context['title'] ?? 'the proposed project');
        $division = e($context['division_name'] ?? 'the requesting division');
        $projectType = e($context['project_type'] ?? 'procurement project');
        $schedule = e($context['schedule'] ?? 'the planned implementation period');
        $venue = e($context['venue_area'] ?? 'the implementing unit');
        $proponent = e($context['proponent'] ?? 'the end-user unit');
        $fundSource = e($context['fund_source_text'] ?? 'MOOE');
        $fiscalYear = e($context['fiscal_year'] ?? date('Y'));
        $agency = e($context['agency_name'] ?? 'the agency');
        $amount = number_format((float) ($context['total_cost'] ?? 0), 2);
        $amountFormatted = '₱'.$amount;

        return [
            'rationale' => <<<HTML
<p>The {$division} proposes <strong>{$title}</strong> to address an operational requirement under the {$projectType} category. The project supports {$agency}'s service delivery goals and aligns with approved annual procurement planning for FY {$fiscalYear}.</p>
<ul><li>Addresses a documented need within {$venue}</li><li>Supports continuity of division operations and public service</li><li>Complies with RA 12009 planning and market scoping requirements</li></ul>
HTML,
            'objectives' => <<<HTML
<h3>General Objective</h3>
<p>To procure and implement {$title} in accordance with approved specifications and within the projected schedule.</p>
<h3>Specific Objectives</h3>
<ol><li>Complete procurement activities within {$schedule}</li><li>Deliver goods/services to {$venue} with documented acceptance</li><li>Utilize the approved ABC of {$amountFormatted} efficiently and transparently</li></ol>
HTML,
            'target_schedule' => <<<HTML
<p>Implementation timeline for <strong>{$title}</strong>:</p>
<ol><li><strong>Planning & PPMP</strong> — Q1 FY {$fiscalYear}</li><li><strong>Procurement activity</strong> — {$schedule}</li><li><strong>Delivery / implementation</strong> — per contract terms at {$venue}</li><li><strong>Post-implementation review</strong> — within 30 days of acceptance</li></ol>
HTML,
            'budgetary_requirement' => <<<HTML
<p>Estimated budgetary requirement for the project:</p>
<table><thead><tr><th>Description</th><th>ABC (PhP)</th></tr></thead><tbody><tr><td>{$title}</td><td>{$amountFormatted}</td></tr></tbody></table>
<p>Total approved budget ceiling: <strong>{$amountFormatted}</strong>.</p>
HTML,
            'fund_source_narrative' => <<<HTML
<p>Funding shall be charged against the <strong>{$fundSource}</strong> allocation under the General Appropriations Act FY {$fiscalYear}, subject to existing budget rules and availability of allotment.</p>
<p>Prepared by {$proponent} on behalf of {$division}.</p>
HTML,
        ];
    }

    protected function extractApiError(mixed $json, string $body): string
    {
        $message = data_get($json, 'error.message');

        if (is_string($message) && $message !== '') {
            return $message;
        }

        return Str::limit($body, 200);
    }
}
