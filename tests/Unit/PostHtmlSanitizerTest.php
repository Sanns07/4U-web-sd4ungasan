<?php

namespace Tests\Unit;

use App\Services\PostHtmlSanitizer;
use PHPUnit\Framework\TestCase;

class PostHtmlSanitizerTest extends TestCase
{
    public function test_it_removes_active_content_attributes_and_unsafe_links(): void
    {
        $sanitizer = new PostHtmlSanitizer;
        $html = <<<'HTML'
        <h2 style="color:red" onclick="alert(1)">Judul</h2>
        <script>alert('xss')</script><style>body{display:none}</style>
        <p><img src=x onerror=alert(1)>Isi <strong data-x="1">aman</strong></p>
        <iframe src="https://example.test"></iframe><svg onload="alert(1)"><text>jahat</text></svg>
        <a href="java&#x0A;script:alert(1)" target="_blank">Tautan jahat</a>
        <a href="https://example.test/info" onclick="alert(1)">Tautan aman</a>
        HTML;

        $safe = $sanitizer->sanitize($html);

        $this->assertStringContainsString('<h2>Judul</h2>', $safe);
        $this->assertStringContainsString('<p>Isi <strong>aman</strong></p>', $safe);
        $this->assertStringContainsString('<a>Tautan jahat</a>', $safe);
        $this->assertStringContainsString('<a href="https://example.test/info" rel="noopener noreferrer">Tautan aman</a>', $safe);
        $this->assertStringNotContainsString('script', strtolower($safe));
        $this->assertStringNotContainsString('iframe', strtolower($safe));
        $this->assertStringNotContainsString('svg', strtolower($safe));
        $this->assertStringNotContainsString('onerror', strtolower($safe));
        $this->assertStringNotContainsString('onclick', strtolower($safe));
        $this->assertStringNotContainsString('style=', strtolower($safe));
        $this->assertStringNotContainsString('<img', strtolower($safe));
    }

    public function test_it_keeps_only_explicitly_owned_image_markers_and_safe_editor_tokens(): void
    {
        $sanitizer = new PostHtmlSanitizer;
        $html = '<p>Awal</p>'
            .'<figure data-image-id="12"><img src="/foreign.jpg"></figure>'
            .'<figure data-image-id="13" class="x"><script>jahat</script></figure>'
            .'<figure data-image-token="slot_valid"><img src="data:x"></figure>'
            .'<figure data-image-token="slot-asing"></figure>'
            .'<p>Akhir</p>';

        $safe = $sanitizer->sanitize($html, [13], ['slot_valid']);

        $this->assertSame(
            '<p>Awal</p><figure data-image-id="13"></figure><figure data-image-token="slot_valid"></figure><p>Akhir</p>',
            $safe,
        );
        $this->assertSame([13], $sanitizer->imageIds($safe));
        $this->assertSame(['slot_valid'], $sanitizer->imageTokens($safe));
    }

    public function test_it_allows_only_application_relative_fragment_mail_and_http_links(): void
    {
        $sanitizer = new PostHtmlSanitizer;
        $safe = $sanitizer->sanitize(
            '<a href="/profil">Profil</a>'
            .'<a href="#bagian">Bagian</a>'
            .'<a href="mailto:admin@example.test">Email</a>'
            .'<a href="http://example.test">HTTP</a>'
            .'<a href="//evil.test">Protocol relative</a>'
            .'<a href="data:text/html,x">Data</a>',
        );

        $this->assertStringContainsString('href="/profil"', $safe);
        $this->assertStringContainsString('href="#bagian"', $safe);
        $this->assertStringContainsString('href="mailto:admin@example.test"', $safe);
        $this->assertStringContainsString('href="http://example.test" rel="noopener noreferrer"', $safe);
        $this->assertStringNotContainsString('href="//evil.test"', $safe);
        $this->assertStringNotContainsString('href="data:', $safe);
    }
}
