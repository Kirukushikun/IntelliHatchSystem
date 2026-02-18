<?php

namespace App\Livewire\Shared\Forms\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait TempPhotoManager
{
    /** @var array<string, int[]> Track uploaded photo IDs per field */
    public array $uploadedPhotoIds = [];

    /** @var array<string, string[]> Track uploaded photo URLs per field */
    public array $uploadedPhotoUrls = [];

    /**
     * Handle temporary photo upload
     */
    protected function handleTempPhotoUpload($photoKey, $files, $formType): void
    {
        if (!is_array($files) || empty($files)) {
            return;
        }

        $this->validateOnly("photoUploads.{$photoKey}.*", [
            "photoUploads.{$photoKey}.*" => ['image', 'max:15360'],
        ]);

        $timestamp = now()->format('Ymd_His');

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            try {
                $uuid = Str::uuid()->getHex();
                $ext = method_exists($file, 'getClientOriginalExtension') ? $file->getClientOriginalExtension() : 'jpg';
                
                $originalName = method_exists($file, 'getClientOriginalName') ? (string) $file->getClientOriginalName() : 'photo';
                $baseName = pathinfo($originalName, PATHINFO_FILENAME);
                $safePhotoName = Str::slug($baseName);
                if ($safePhotoName === '') {
                    $safePhotoName = 'photo';
                }

                // Use pending prefix instead of FORMID placeholder
                $filename = "{$formType}_pending_{$timestamp}_{$photoKey}_{$safePhotoName}-{$uuid}.{$ext}";
                $path = $file->storeAs('forms', $filename, 'public');

                if (!$path) {
                    continue;
                }

                $url = $this->absolutePublicUrlFromDiskPath($path);

                // Store relative path in database (like digital-disinfection-slip-system)
                $photoId = (int) DB::table('photos')->insertGetId([
                    'public_path' => $url,
                    'disk' => 'public',
                    'uploaded_by' => Auth::id() ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->uploadedPhotoUrls[$photoKey][] = $url;
                $this->uploadedPhotoIds[$photoKey][] = $photoId;

                $this->dispatch('photoStored', photoKey: $photoKey, photoId: $photoId, url: $url);

                // Clean up the Livewire temporary file
                $this->cleanupLivewireTempFile($file);

            } catch (\Exception $e) {
                Log::error("Photo upload failed for {$photoKey}: " . $e->getMessage());
                continue;
            }
        }

        $this->photoUploads[$photoKey] = [];
    }

    /**
     * Clean up Livewire temporary file after successful processing
     */
    protected function cleanupLivewireTempFile($file): void
    {
        try {
            if (method_exists($file, 'getRealPath')) {
                $tempPath = $file->getRealPath();
                if ($tempPath && file_exists($tempPath)) {
                    // Delete the actual temporary file
                    unlink($tempPath);
                    
                    // Delete the corresponding .json metadata file
                    $jsonPath = $tempPath . '.json';
                    if (file_exists($jsonPath)) {
                        unlink($jsonPath);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to cleanup Livewire temp file: " . $e->getMessage());
        }
    }

    /**
     * Clean up all remaining Livewire temporary files
     */
    protected function cleanupAllLivewireTempFiles(): void
    {
        try {
            // Clean up any remaining photo uploads
            foreach ($this->photoUploads as $photoKey => $files) {
                if (is_array($files)) {
                    foreach ($files as $file) {
                        if ($file) {
                            $this->cleanupLivewireTempFile($file);
                        }
                    }
                }
            }
            
            // Clear the photo uploads array
            $this->photoUploads = [];
            
        } catch (\Exception $e) {
            Log::error("Failed to cleanup all Livewire temp files: " . $e->getMessage());
        }
    }

    /**
     * Finalize photos for form - rename from pending to final
     */
    protected function finalizePhotosForForm(int $formId): void
    {
        DB::beginTransaction();
        
        try {
            foreach ($this->uploadedPhotoIds as $photoKey => $ids) {
                foreach ($ids as $i => $photoId) {
                    $photo = DB::table('photos')->where('id', (int) $photoId)->first();
                    if (!$photo || !isset($photo->public_path)) {
                        continue;
                    }

                    $currentUrl = (string) $photo->public_path;
                    $relativePath = $this->diskPathFromPublicUrl($currentUrl);
                    
                    // Only process pending photos
                    if ($relativePath === '' || !str_contains($relativePath, '_pending_')) {
                        continue;
                    }

                    // Replace pending with actual form ID
                    $newRelativePath = str_replace('_pending_', "_form_{$formId}_", $relativePath);

                    // Move file to final location
                    $moved = Storage::disk('public')->move($relativePath, $newRelativePath);
                    if (!$moved) {
                        Log::error("Failed to move photo file: {$relativePath} -> {$newRelativePath}");
                        continue;
                    }

                    $newUrl = $this->absolutePublicUrlFromDiskPath($newRelativePath);

                    // Update database record
                    DB::table('photos')->where('id', (int) $photoId)->update([
                        'public_path' => $newUrl,
                        'updated_at' => now(),
                    ]);

                    // Update in-memory URLs
                    if (isset($this->uploadedPhotoUrls[$photoKey][$i])) {
                        $this->uploadedPhotoUrls[$photoKey][$i] = $newUrl;
                    }
                }
            }
            
            DB::commit();
            
            // Clean up any remaining Livewire temporary files
            $this->cleanupAllLivewireTempFiles();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to finalize photos for form {$formId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete uploaded photo
     */
    public function deleteUploadedPhoto(string $photoKey, int $photoId): void
    {
        $ids = $this->uploadedPhotoIds[$photoKey] ?? [];
        if (!in_array($photoId, $ids, true)) {
            return;
        }

        try {
            $photo = DB::table('photos')->where('id', $photoId)->first();
            if ($photo && isset($photo->public_path)) {
                $relativePath = $this->diskPathFromPublicUrl((string) $photo->public_path);
                if ($relativePath !== '') {
                    Storage::disk('public')->delete($relativePath);
                }
            }

            DB::table('photos')->where('id', $photoId)->delete();

            // Remove from arrays
            $this->uploadedPhotoIds[$photoKey] = array_values(array_filter(
                $this->uploadedPhotoIds[$photoKey] ?? [],
                fn ($id) => (int) $id !== $photoId
            ));

            $this->uploadedPhotoUrls[$photoKey] = array_values(array_filter(
                $this->uploadedPhotoUrls[$photoKey] ?? [],
                function ($url) use ($photo, $photoId) {
                    if ($photo && isset($photo->public_path)) {
                        return (string) $url !== (string) $photo->public_path;
                    }
                    return true;
                }
            ));

        } catch (\Exception $e) {
            Log::error("Failed to delete photo {$photoId}: " . $e->getMessage());
        }
    }

    /**
     * Cleanup all uploaded photos (for form reset/cancel)
     */
    protected function cleanupAllUploadedPhotos(): void
    {
        try {
            // Delete physical files
            foreach ($this->uploadedPhotoUrls as $field => $urls) {
                foreach ($urls as $url) {
                    $relativePath = $this->diskPathFromPublicUrl($url);
                    Storage::disk('public')->delete($relativePath);
                }
            }

            // Delete database records
            $allIds = [];
            foreach ($this->uploadedPhotoIds as $field => $ids) {
                foreach ($ids as $id) {
                    $allIds[] = $id;
                }
            }

            if (!empty($allIds)) {
                DB::table('photos')->whereIn('id', $allIds)->delete();
            }

            // Clear arrays
            $this->uploadedPhotoUrls = [];
            $this->uploadedPhotoIds = [];
            $this->photoUploads = [];

        } catch (\Exception $e) {
            Log::error("Failed to cleanup photos: " . $e->getMessage());
        }
    }

    /**
     * Cleanup orphaned pending photos (should be called periodically)
     */
    protected function cleanupOrphanedPendingPhotos(): void
    {
        try {
            // Find all pending photos older than 24 hours that aren't associated with any form
            $cutoffTime = now()->subHours(24);
            
            $orphanedPhotos = DB::table('photos')
                ->where('created_at', '<', $cutoffTime)
                ->where('public_path', 'like', '%_pending_%')
                ->whereNotIn('id', function ($query) {
                    $query->select('id')
                        ->from('forms')
                        ->whereJsonContains('form_inputs->photos', function ($photo) {
                            // This is a simplified check - you may need to adjust based on your JSON structure
                            return true;
                        });
                })
                ->get();

            foreach ($orphanedPhotos as $photo) {
                $relativePath = $this->diskPathFromPublicUrl($photo->public_path);
                Storage::disk('public')->delete($relativePath);
                DB::table('photos')->where('id', $photo->id)->delete();
            }

            if ($orphanedPhotos->count() > 0) {
                Log::info("Cleaned up {$orphanedPhotos->count()} orphaned pending photos");
            }

        } catch (\Exception $e) {
            Log::error("Failed to cleanup orphaned photos: " . $e->getMessage());
        }
    }

    /**
     * Get absolute URL from disk path
     */
    protected function absolutePublicUrlFromDiskPath(string $diskPath): string
    {
        return url(Storage::url($diskPath));
    }

    /**
     * Get disk path from public URL
     */
    protected function diskPathFromPublicUrl(string $publicUrl): string
    {
        $path = parse_url($publicUrl, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return ltrim(str_replace('/storage/', '', $publicUrl), '/');
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            return substr($path, strlen('storage/'));
        }

        return $path;
    }

    /**
     * Get form inputs with photo URLs included
     */
    protected function formInputsWithPhotos(array $baseInputs): array
    {
        foreach ($this->uploadedPhotoUrls as $photoKey => $urls) {
            if (!empty($urls)) {
                $baseInputs[$photoKey] = $urls;
            }
        }

        return $baseInputs;
    }
}
