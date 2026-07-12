<?php

namespace Tests\Unit;

use App\Support\RichTextSanitizer;
use Tests\TestCase;

class RichTextSanitizerTest extends TestCase
{
    public function test_it_strips_script_tags_and_event_handlers(): void
    {
        $dirty = '<p>Hello</p><script>alert(1)</script><img src=x onerror="alert(1)">';

        $clean = RichTextSanitizer::clean($dirty);

        $this->assertStringContainsString('<p>Hello</p>', $clean);
        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
    }

    public function test_it_returns_null_for_empty_content(): void
    {
        $this->assertNull(RichTextSanitizer::clean('<p><br></p>'));
        $this->assertNull(RichTextSanitizer::clean(''));
    }

    public function test_it_preserves_allowed_formatting_tags(): void
    {
        $html = '<h3>Title</h3><p><strong>Bold</strong> and <em>italic</em></p><ul><li>One</li></ul>';

        $this->assertSame($html, RichTextSanitizer::clean($html));
    }

    public function test_it_preserves_table_markup(): void
    {
        $html = '<table><thead><tr><th>Item</th><th>Cost</th></tr></thead><tbody><tr><td>Unit A</td><td>1000</td></tr></tbody></table>';

        $clean = RichTextSanitizer::clean($html);

        $this->assertStringContainsString('<table>', $clean);
        $this->assertStringContainsString('<th>Item</th>', $clean);
        $this->assertStringContainsString('<td>Unit A</td>', $clean);
    }

    public function test_it_converts_html_to_plain_text(): void
    {
        $html = '<h3>General Objective</h3><p>Procure <strong>twenty (20) desktop units</strong>.</p><ul><li>Acquire 20 units</li></ul>';

        $plain = RichTextSanitizer::plainText($html);

        $this->assertStringNotContainsString('<', $plain);
        $this->assertStringContainsString('General Objective', $plain);
        $this->assertStringContainsString('twenty (20) desktop units', $plain);
        $this->assertStringContainsString('Acquire 20 units', $plain);
    }
}
