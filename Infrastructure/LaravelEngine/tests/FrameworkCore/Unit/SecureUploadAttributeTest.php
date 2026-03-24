<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Attributes\Validation\SecureUpload;
use PHPUnit\Framework\TestCase;

class SecureUploadAttributeTest extends TestCase
{
    public function test_default_values(): void
    {
        $attr = new SecureUpload();

        $this->assertEquals(['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'], $attr->getAllowedMimes());
        $this->assertEquals(10240, $attr->getMaxSizeKb());
        $this->assertTrue($attr->shouldGenerateUniqueName());
        $this->assertFalse($attr->shouldScanForMalware());
        $this->assertNull($attr->getStorageDisk());
    }

    public function test_custom_allowed_mimes(): void
    {
        $attr = new SecureUpload(allowedMimes: ['pdf', 'doc']);

        $this->assertEquals(['pdf', 'doc'], $attr->getAllowedMimes());
    }

    public function test_custom_max_size(): void
    {
        $attr = new SecureUpload(maxSize: 5120);

        $this->assertEquals(5120, $attr->getMaxSizeKb());
    }

    public function test_custom_allowed_types(): void
    {
        $attr = new SecureUpload(allowedTypes: ['application/pdf', 'application/msword']);

        $this->assertEquals(['application/pdf', 'application/msword'], $attr->getAllowedTypes());
    }

    public function test_scan_for_malware_enabled(): void
    {
        $attr = new SecureUpload(scanForMalware: true);

        $this->assertTrue($attr->shouldScanForMalware());
    }

    public function test_generate_unique_name_disabled(): void
    {
        $attr = new SecureUpload(generateUniqueName: false);

        $this->assertFalse($attr->shouldGenerateUniqueName());
    }

    public function test_custom_storage_disk(): void
    {
        $attr = new SecureUpload(storageDisk: 's3');

        $this->assertEquals('s3', $attr->getStorageDisk());
    }

    public function test_all_custom_values(): void
    {
        $attr = new SecureUpload(
            allowedMimes: ['png', 'webp'],
            maxSize: 2048,
            allowedTypes: ['image/png', 'image/webp'],
            scanForMalware: true,
            generateUniqueName: false,
            storageDisk: 'cloud'
        );

        $this->assertEquals(['png', 'webp'], $attr->getAllowedMimes());
        $this->assertEquals(2048, $attr->getMaxSizeKb());
        $this->assertEquals(['image/png', 'image/webp'], $attr->getAllowedTypes());
        $this->assertTrue($attr->shouldScanForMalware());
        $this->assertFalse($attr->shouldGenerateUniqueName());
        $this->assertEquals('cloud', $attr->getStorageDisk());
    }
}
