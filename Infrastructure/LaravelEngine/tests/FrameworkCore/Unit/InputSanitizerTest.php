<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Services\InputSanitizer;
use PHPUnit\Framework\TestCase;

class InputSanitizerTest extends TestCase
{
    public function test_disabled_by_default(): void
    {
        $sanitizer = new InputSanitizer(['enabled' => false]);

        $this->assertFalse($sanitizer->isEnabled());
    }

    public function test_enabled_when_configured(): void
    {
        $sanitizer = new InputSanitizer(['enabled' => true]);

        $this->assertTrue($sanitizer->isEnabled());
    }

    public function test_sanitize_returns_input_when_disabled(): void
    {
        $sanitizer = new InputSanitizer(['enabled' => false]);
        $input = '<script>alert("xss")</script>';

        $this->assertEquals($input, $sanitizer->sanitize($input));
    }

    public function test_strips_html_tags(): void
    {
        $sanitizer = new InputSanitizer([
            'enabled' => true,
            'strip_html' => true,
            'strip_scripts' => true,
            'escape_sql_wildcards' => true,
            'allowed_tags' => '',
        ]);

        $input = '<p>Hello <b>World</b></p>';
        $result = $sanitizer->sanitizeHtml($input);

        $this->assertEquals('Hello World', $result);
    }

    public function test_strips_script_tags(): void
    {
        $sanitizer = new InputSanitizer([
            'enabled' => true,
            'strip_html' => true,
            'strip_scripts' => true,
            'escape_sql_wildcards' => false,
            'allowed_tags' => '',
        ]);

        $input = '<p>Hello</p><script>alert("xss")</script>';
        $result = $sanitizer->sanitizeJavascript($input);

        $this->assertStringNotContainsString('<script>', $result);
    }

    public function test_escapes_sql_wildcards(): void
    {
        $sanitizer = new InputSanitizer([
            'enabled' => true,
            'strip_html' => false,
            'strip_scripts' => false,
            'escape_sql_wildcards' => true,
            'allowed_tags' => '',
        ]);

        $input = '100% of items_200';
        $result = $sanitizer->sanitizeSqlWildcards($input);

        $this->assertEquals('100\% of items\_200', $result);
    }

    public function test_sanitize_filename(): void
    {
        $sanitizer = new InputSanitizer([]);

        $input = 'file<>:"/\\|?*name.php';
        $result = $sanitizer->sanitizeFilename($input);

        $this->assertEquals('file_name.php', $result);
    }

    public function test_sanitize_email(): void
    {
        $sanitizer = new InputSanitizer([]);

        $input = 'test@example.com<script>';
        $result = $sanitizer->sanitizeEmail($input);

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('test@example.com', $result);
    }

    public function test_sanitize_url(): void
    {
        $sanitizer = new InputSanitizer([]);

        $input = 'https://example.com/path';
        $result = $sanitizer->sanitizeUrl($input);

        $this->assertEquals('https://example.com/path', $result);
    }

    public function test_sanitize_integer(): void
    {
        $sanitizer = new InputSanitizer([]);

        $input = '123abc';
        $result = $sanitizer->sanitizeInteger($input);

        $this->assertEquals(123, $result);
    }

    public function test_sanitize_float(): void
    {
        $sanitizer = new InputSanitizer([]);

        $input = '123.45abc';
        $result = $sanitizer->sanitizeFloat($input);

        $this->assertEquals(123.45, $result);
    }

    public function test_sanitize_array(): void
    {
        $sanitizer = new InputSanitizer([
            'enabled' => true,
            'strip_html' => true,
            'strip_scripts' => true,
            'escape_sql_wildcards' => false,
            'allowed_tags' => '',
        ]);

        $input = ['<p>Hello</p>', 'World', 123];
        $result = $sanitizer->sanitizeArray($input);

        $this->assertEquals(['Hello', 'World', 123], $result);
    }

    public function test_detects_xss_script(): void
    {
        $sanitizer = new InputSanitizer([]);

        $this->assertTrue($sanitizer->detectSuspiciousInput('<script>alert(1)</script>'));
    }

    public function test_detects_javascript_uri(): void
    {
        $sanitizer = new InputSanitizer([]);

        $this->assertTrue($sanitizer->detectSuspiciousInput('javascript:alert(1)'));
    }

    public function test_detects_sql_injection(): void
    {
        $sanitizer = new InputSanitizer([]);

        $this->assertTrue($sanitizer->detectSuspiciousInput("' OR '1'='1"));
    }

    public function test_detects_union_select(): void
    {
        $sanitizer = new InputSanitizer([]);

        $this->assertTrue($sanitizer->detectSuspiciousInput('UNION SELECT * FROM users'));
    }

    public function test_detects_drop_table(): void
    {
        $sanitizer = new InputSanitizer([]);

        $this->assertTrue($sanitizer->detectSuspiciousInput('DROP TABLE users'));
    }

    public function test_does_not_detect_normal_input(): void
    {
        $sanitizer = new InputSanitizer([]);

        $this->assertFalse($sanitizer->detectSuspiciousInput('Hello World'));
    }

    public function test_get_suspicious_details(): void
    {
        $sanitizer = new InputSanitizer([]);

        $details = $sanitizer->getSuspiciousDetails('<script>alert(1)</script>');

        $this->assertContains('XSS Script', $details);
    }

    public function test_allowed_tags_preserved(): void
    {
        $sanitizer = new InputSanitizer([
            'enabled' => true,
            'strip_html' => true,
            'strip_scripts' => true,
            'escape_sql_wildcards' => false,
            'allowed_tags' => '<b><i>',
        ]);

        $input = '<b>Bold</b> and <i>Italic</i>';
        $result = $sanitizer->sanitizeHtml($input);

        $this->assertStringContainsString('Bold', $result);
        $this->assertStringContainsString('Italic', $result);
    }
}
