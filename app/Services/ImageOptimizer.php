<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Compress and store an uploaded image on the public storage disk.
     *
     * @param  int  $maxDimension  Maximum width or height in pixels
     * @param  int  $quality  JPEG compression quality (1-100)
     * @return string Stored relative path on public disk
     */
    public static function compressAndStore(
        UploadedFile $file,
        string $directory,
        int $maxDimension = 1600,
        int $quality = 80
    ): string {
        if (! extension_loaded('gd')) {
            return $file->store($directory, 'public');
        }

        try {
            $imageContent = file_get_contents($file->getRealPath());
            if ($imageContent === false) {
                return $file->store($directory, 'public');
            }

            $sourceImage = @imagecreatefromstring($imageContent);
            if (! $sourceImage) {
                return $file->store($directory, 'public');
            }

            // Orientasi EXIF otomatis untuk foto kamera ponsel
            if (function_exists('exif_read_data')) {
                $exif = @exif_read_data($file->getRealPath());
                if (! empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $sourceImage = imagerotate($sourceImage, 180, 0);
                            break;
                        case 6:
                            $sourceImage = imagerotate($sourceImage, -90, 0);
                            break;
                        case 8:
                            $sourceImage = imagerotate($sourceImage, 90, 0);
                            break;
                    }
                }
            }

            $originalWidth = imagesx($sourceImage);
            $originalHeight = imagesy($sourceImage);

            if ($originalWidth > $maxDimension || $originalHeight > $maxDimension) {
                if ($originalWidth >= $originalHeight) {
                    $newWidth = $maxDimension;
                    $newHeight = (int) round(($originalHeight / $originalWidth) * $maxDimension);
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = (int) round(($originalWidth / $originalHeight) * $maxDimension);
                }

                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);

                imagecopyresampled(
                    $resizedImage,
                    $sourceImage,
                    0, 0, 0, 0,
                    $newWidth,
                    $newHeight,
                    $originalWidth,
                    $originalHeight
                );

                imagedestroy($sourceImage);
                $finalImage = $resizedImage;
            } else {
                $finalImage = $sourceImage;
            }

            ob_start();
            imagejpeg($finalImage, null, $quality);
            $compressedData = ob_get_clean();

            imagedestroy($finalImage);

            if ($compressedData === false || strlen($compressedData) === 0) {
                return $file->store($directory, 'public');
            }

            $filename = Str::random(40).'.jpg';
            $targetPath = trim($directory, '/').'/'.$filename;

            Storage::disk('public')->put($targetPath, $compressedData);

            return $targetPath;
        } catch (\Throwable) {
            return $file->store($directory, 'public');
        }
    }

    /**
     * Simpan gambar kategori motor dalam 2 mode: Versi Besar dan Versi Kecil (Thumbnail) dengan mempertahankan transparansi PNG/WebP.
     *
     * @return array{large: string, thumbnail: string}
     */
    public static function processCategoryImages(UploadedFile $file, string $directory = 'categories'): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['png', 'webp', 'jpg', 'jpeg'])) {
            $extension = 'png';
        }

        $baseName = Str::random(30);
        $largeName = "{$baseName}_large.{$extension}";
        $thumbName = "{$baseName}_thumb.{$extension}";

        $dir = trim($directory, '/');

        if (! extension_loaded('gd')) {
            $path = $file->storeAs($dir, $largeName, 'public');

            return [
                'large' => basename($path),
                'thumbnail' => basename($path),
            ];
        }

        try {
            $imageContent = file_get_contents($file->getRealPath());
            $sourceImage = @imagecreatefromstring($imageContent);
            if (! $sourceImage) {
                $path = $file->storeAs($dir, $largeName, 'public');

                return [
                    'large' => basename($path),
                    'thumbnail' => basename($path),
                ];
            }

            // 1. Buat versi gambar besar (maks lebar 800px)
            $largeImage = self::resizeImagePreservingAlpha($sourceImage, 800);
            $largePath = "{$dir}/{$largeName}";
            $largeData = self::exportImageData($largeImage, $extension);
            Storage::disk('public')->put($largePath, $largeData);
            imagedestroy($largeImage);

            // 2. Buat versi gambar kecil / thumbnail (maks lebar 240px)
            $thumbImage = self::resizeImagePreservingAlpha($sourceImage, 240);
            $thumbPath = "{$dir}/{$thumbName}";
            $thumbData = self::exportImageData($thumbImage, $extension);
            Storage::disk('public')->put($thumbPath, $thumbData);
            imagedestroy($thumbImage);

            imagedestroy($sourceImage);

            return [
                'large' => $largeName,
                'thumbnail' => $thumbName,
            ];
        } catch (\Throwable) {
            $path = $file->storeAs($dir, $largeName, 'public');

            return [
                'large' => basename($path),
                'thumbnail' => basename($path),
            ];
        }
    }

    /**
     * Resize image dengan mempertahankan alpha channel transparansi PNG/WebP.
     */
    public static function resizeImagePreservingAlpha($sourceImage, int $maxDimension)
    {
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);

        if ($originalWidth > $maxDimension || $originalHeight > $maxDimension) {
            if ($originalWidth >= $originalHeight) {
                $newWidth = $maxDimension;
                $newHeight = (int) round(($originalHeight / $originalWidth) * $maxDimension);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int) round(($originalWidth / $originalHeight) * $maxDimension);
            }
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled(
            $canvas,
            $sourceImage,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        return $canvas;
    }

    /**
     * Export image resource ke raw binary string sesuai format ekstensi.
     */
    public static function exportImageData($image, string $extension): string
    {
        ob_start();
        if ($extension === 'png') {
            imagepng($image, null, 8);
        } elseif ($extension === 'webp' && function_exists('imagewebp')) {
            imagewebp($image, null, 85);
        } else {
            imagejpeg($image, null, 85);
        }

        return (string) ob_get_clean();
    }
}
