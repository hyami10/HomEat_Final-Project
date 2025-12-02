<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FileUploadService
{
    public const SIZE_PROFILE_PHOTO = 2048;      
    public const SIZE_FOOD_IMAGE = 2048;         
    public const SIZE_PAYMENT_PROOF = 10240;    
    
    private static array $allowedTypes = [
        'image/jpeg' => [
            'extensions' => ['jpg', 'jpeg'],
            'magic' => ["\xFF\xD8\xFF"],
        ],
        'image/png' => [
            'extensions' => ['png'],
            'magic' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
        ],
        'image/webp' => [
            'extensions' => ['webp'],
            'magic' => ["RIFF"],
        ],
    ];

    /**
     * @param UploadedFile $file
     * @param array $allowedMimes Optional override for allowed mime types
     * @param int $maxSizeKB Maximum file size in kilobytes (default: 2048 = 2MB)
     * @return array ['valid' => bool, 'error' => string|null, 'safe_extension' => string|null]
     */
    public static function validateFile(UploadedFile $file, array $allowedMimes = null, int $maxSizeKB = 2048): array
    {
        $allowedMimes = $allowedMimes ?? ['image/jpeg', 'image/png', 'image/jpg'];
        
        if ($error = self::checkBasicAttributes($file, $allowedMimes, $maxSizeKB)) {
            return $error;
        }

        $magicCheck = self::checkMagicBytes($file, $allowedMimes);
        if (!$magicCheck['valid']) {
            return $magicCheck;
        }
        $safeExtension = $magicCheck['safe_extension'];
        
        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            Log::warning('File upload rejected: getimagesize validation failed', [
                'filename' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
            ]);
            return ['valid' => false, 'error' => 'File bukan gambar yang valid.', 'safe_extension' => null];
        }
        
        $allowedImageTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
        if (!in_array($imageInfo[2], $allowedImageTypes)) {
            Log::warning('File upload rejected: unexpected image type', [
                'detected_type' => $imageInfo[2],
                'filename' => $file->getClientOriginalName(),
            ]);
            return ['valid' => false, 'error' => 'Tipe gambar tidak didukung.', 'safe_extension' => null];
        }

        if ($error = self::sanitizeImage($file)) {
            return $error;
        }

        return [
            'valid' => true,
            'error' => null,
            'safe_extension' => $safeExtension,
        ];
    }

    private static function checkBasicAttributes(UploadedFile $file, array $allowedMimes, int $maxSizeKB = 2048): ?array
    {
        $detectedMime = $file->getMimeType();
        if (!in_array($detectedMime, $allowedMimes)) {
            Log::warning('File upload rejected: Invalid MIME type', ['detected' => $detectedMime]);
            return ['valid' => false, 'error' => 'Tipe file tidak diizinkan.', 'safe_extension' => null];
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = self::getAllowedExtensions($allowedMimes);
        
        if (!in_array($extension, $allowedExtensions)) {
            Log::warning('File upload rejected: Invalid extension', ['ext' => $extension]);
            return ['valid' => false, 'error' => 'Ekstensi file tidak diizinkan.', 'safe_extension' => null];
        }

        $maxBytes = $maxSizeKB * 1024;
        if ($file->getSize() > $maxBytes) {
            $maxMB = round($maxSizeKB / 1024, 1);
            return ['valid' => false, 'error' => "Ukuran file terlalu besar (maksimal {$maxMB}MB).", 'safe_extension' => null];
        }

        $originalName = $file->getClientOriginalName();
        if (preg_match('/[\/\\\:\*\?\"\<\>\|]/', $originalName) || str_contains($originalName, '..') || str_contains($originalName, "\0")) {
            Log::warning('File upload rejected: Suspicious filename', ['filename' => $originalName]);
            return ['valid' => false, 'error' => 'Nama file tidak valid.', 'safe_extension' => null];
        }

        return null;
    }

    private static function checkMagicBytes(UploadedFile $file, array $allowedMimes): array
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if (!$handle) {
            return ['valid' => false, 'error' => 'Tidak dapat membaca file.', 'safe_extension' => null];
        }
        
        $header = fread($handle, 16);
        fclose($handle);

        foreach (self::$allowedTypes as $mime => $config) {
            if (!in_array($mime, $allowedMimes)) continue;
            
            foreach ($config['magic'] as $magic) {
                if (str_starts_with($header, $magic)) {
                    return ['valid' => true, 'safe_extension' => $config['extensions'][0]];
                }
            }
        }

        if (in_array('image/webp', $allowedMimes) && str_starts_with($header, 'RIFF') && strpos($header, 'WEBP') !== false) {
            return ['valid' => true, 'safe_extension' => 'webp'];
        }

        Log::warning('File upload rejected: Invalid magic bytes', ['header_hex' => bin2hex(substr($header, 0, 8))]);
        return ['valid' => false, 'error' => 'Konten file tidak valid.', 'safe_extension' => null];
    }

    private static function sanitizeImage(UploadedFile $file): ?array
    {
        try {
            $imageSize = getimagesize($file->getRealPath());
            if ($imageSize === false) {
                Log::warning('Image sanitization skipped: getimagesize failed');
                return null; // Skip sanitization but allow upload
            }

            $content = file_get_contents($file->getRealPath());
            if ($content === false) {
                Log::warning('Image sanitization skipped: cannot read file');
                return null;
            }
            
            $image = @imagecreatefromstring($content);
            if (!$image) {
                Log::warning('Image sanitization skipped: imagecreatefromstring failed');
                return null;
            }

            $tempPath = tempnam(sys_get_temp_dir(), 'upload_');
            $success = false;

            switch ($imageSize[2]) {
                case IMAGETYPE_JPEG:
                    $success = imagejpeg($image, $tempPath, 90);
                    break;
                case IMAGETYPE_PNG:
                    $success = imagepng($image, $tempPath, 9);
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagewebp')) {
                        $success = imagewebp($image, $tempPath, 90);
                    } else {
                        Log::warning('WebP support not available, skipping sanitization');
                        imagedestroy($image);
                        return null;
                    }
                    break;
                default:
                    imagedestroy($image);
                    Log::warning('Image sanitization skipped: unsupported image type');
                    return null;
            }
            
            imagedestroy($image);

            if ($success && file_exists($tempPath)) {
                if (@copy($tempPath, $file->getRealPath())) {
                    @unlink($tempPath);
                    Log::info('Image sanitization successful');
                } else {
                    @unlink($tempPath);
                    Log::warning('Image sanitization: copy failed, using original');
                }
            } else {
                @unlink($tempPath);
                Log::warning('Image sanitization: save failed, using original');
            }

        } catch (\Exception $e) {
            Log::warning('Image sanitization error (non-blocking): ' . $e->getMessage());
        }

        return null; 
    }

    private static function getAllowedExtensions(array $allowedMimes): array
    {
        $extensions = [];
        foreach ($allowedMimes as $mime) {
            if (isset(self::$allowedTypes[$mime])) {
                $extensions = array_merge($extensions, self::$allowedTypes[$mime]['extensions']);
            }
        }
        return array_unique(array_merge($extensions, ['jpg', 'jpeg', 'png']));
    }

    public static function generateSafeFilename(string $prefix, string $extension): string
    {
        $extension = preg_replace('/[^a-z0-9]/', '', strtolower($extension));
        return sprintf('%s_%s_%s.%s', $prefix, time(), bin2hex(random_bytes(8)), $extension);
    }
}
