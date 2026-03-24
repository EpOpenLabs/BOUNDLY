<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Infrastructure\FrameworkCore\Attributes\Validation\SecureUpload;

class SecureFileUploader
{
    protected InputSanitizer $sanitizer;

    protected array $config;

    public function __construct(?InputSanitizer $sanitizer = null)
    {
        $this->sanitizer = $sanitizer ?? new InputSanitizer;
        $this->config = config('boundly.security', []);
    }

    public function upload(UploadedFile $file, ?SecureUpload $attribute = null, ?string $path = 'uploads'): array
    {
        $attribute = $attribute ?? new SecureUpload;
        $config = $this->getMergedConfig($attribute);

        $this->validateFile($file, $config);

        $filename = $this->generateFilename($file, $config);
        $sanitizedPath = $this->sanitizer->sanitizeFilename($path);

        $disk = $config['storage_disk'] ?? 'local';

        $uploadedPath = $file->storeAs(
            $sanitizedPath,
            $filename,
            $disk
        );

        return [
            'path' => $uploadedPath,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $disk,
        ];
    }

    public function validateFile(UploadedFile $file, array $config): void
    {
        $this->validateMimeType($file, $config);
        $this->validateExtension($file, $config);
        $this->validateSize($file, $config);
    }

    protected function validateMimeType(UploadedFile $file, array $config): void
    {
        $allowedTypes = $config['allowed_types'] ?? [];

        if (empty($allowedTypes)) {
            return;
        }

        $mimeType = $file->getMimeType();

        if (! in_array($mimeType, $allowedTypes, true)) {
            throw new \InvalidArgumentException(
                "File mime type '{$mimeType}' is not allowed. Allowed types: ".implode(', ', $allowedTypes)
            );
        }
    }

    protected function validateExtension(UploadedFile $file, array $config): void
    {
        $allowedMimes = $config['allowed_mimes'] ?? [];

        if (empty($allowedMimes)) {
            return;
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $allowedMimes, true)) {
            throw new \InvalidArgumentException(
                "File extension '{$extension}' is not allowed. Allowed extensions: ".implode(', ', $allowedMimes)
            );
        }
    }

    protected function validateSize(UploadedFile $file, array $config): void
    {
        $maxSizeKb = $config['max_size'] ?? 10240;
        $maxSizeBytes = $maxSizeKb * 1024;
        $fileSize = $file->getSize();

        if ($fileSize > $maxSizeBytes) {
            throw new \InvalidArgumentException(
                "File size ({$fileSize} bytes) exceeds maximum allowed size ({$maxSizeBytes} bytes)"
            );
        }
    }

    protected function generateFilename(UploadedFile $file, array $config): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension());

        $sanitizedName = $this->sanitizer->sanitizeFilename($originalName);

        if ($config['generate_unique_name'] ?? true) {
            $uniqueId = bin2hex(random_bytes(8));
            $timestamp = time();

            return "{$sanitizedName}_{$timestamp}_{$uniqueId}.{$extension}";
        }

        return "{$sanitizedName}.{$extension}";
    }

    protected function getMergedConfig(SecureUpload $attribute): array
    {
        return [
            'allowed_mimes' => $attribute->getAllowedMimes(),
            'allowed_types' => $attribute->getAllowedTypes(),
            'max_size' => $attribute->getMaxSizeKb(),
            'scan_for_malware' => $attribute->shouldScanForMalware(),
            'generate_unique_name' => $attribute->shouldGenerateUniqueName(),
            'storage_disk' => $attribute->getStorageDisk(),
        ];
    }

    public function delete(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?? 'local';

        if (class_exists(Storage::class)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    public function isImage(UploadedFile $file): bool
    {
        $imageMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
        ];

        return in_array($file->getMimeType(), $imageMimes, true);
    }

    public function getImageDimensions(UploadedFile $file): ?array
    {
        if (! $this->isImage($file)) {
            return null;
        }

        $path = $file->getRealPath();

        if (! file_exists($path)) {
            return null;
        }

        $dimensions = @getimagesize($path);

        if ($dimensions === false) {
            return null;
        }

        return [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
        ];
    }
}
