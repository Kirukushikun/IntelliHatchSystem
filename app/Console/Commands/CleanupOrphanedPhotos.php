<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOrphanedPhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photos:clean {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned photos and temporary photos older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $this->info($dryRun ? '🔍 DRY RUN MODE - No files will be deleted' : '🗑️  CLEANUP MODE - Files will be deleted');
        $this->info('================================================');

        // Find photos older than 24 hours
        $cutoffTime = Carbon::now('Asia/Manila')->subHours(24);
        
        $this->info("Looking for orphaned/temporary photos older than: {$cutoffTime->format('Y-m-d H:i:s')} (Asia/Manila)");

        // Get all photos older than 24 hours
        $oldPhotos = DB::table('photos')
            ->where('created_at', '<', $cutoffTime)
            ->select('id', 'public_path', 'created_at')
            ->get();

        $cleanupPhotos = [];
        $totalSize = 0;
        $deletedCount = 0;

        foreach ($oldPhotos as $photo) {
            $isCleanupCandidate = false;
            
            // Check 1: Photo has FORMID in filename (indicates unfinished form)
            if (strpos($photo->public_path, 'FORMID') !== false) {
                $isCleanupCandidate = true;
                $this->line("  - 🎯 Found FORMID pattern in: {$photo->public_path}");
            }
            
            // Check 2: Photo has _pending_ pattern (temporary photos)
            if (strpos($photo->public_path, '_pending_') !== false) {
                $isCleanupCandidate = true;
                $this->line("  - ⏳ Found _pending_ pattern in: {$photo->public_path}");
            }
            
            // Check 3: Photo is not referenced in any submitted form
            if (!$isCleanupCandidate) {
                $isReferenced = DB::table('forms')
                    ->where('form_inputs', 'LIKE', '%' . $photo->public_path . '%')
                    ->exists();
                
                if (!$isReferenced) {
                    $isCleanupCandidate = true;
                }
            }
            
            if ($isCleanupCandidate) {
                $cleanupPhotos[] = $photo;
            }
        }

        $totalCleanup = count($cleanupPhotos);

        if ($totalCleanup === 0) {
            $this->info('✅ No orphaned or temporary photos found.');
            
            // Let's also check for very recent photos with FORMID or _pending_ pattern
            $recentPhotos = DB::table('photos')
                ->where('created_at', '>=', $cutoffTime)
                ->where(function ($query) {
                    $query->where('public_path', 'LIKE', '%FORMID%')
                          ->orWhere('public_path', 'LIKE', '%_pending_%');
                })
                ->select('id', 'public_path', 'created_at')
                ->get();
                
            if ($recentPhotos->count() > 0) {
                $this->info("ℹ️  Found {$recentPhotos->count()} recent photos with temporary patterns (too recent to cleanup):");
                foreach ($recentPhotos as $photo) {
                    $this->line("  - ID: {$photo->id} | Path: {$photo->public_path} | Created: {$photo->created_at}");
                }
                $this->info("💡 These will be eligible for cleanup after 24 hours");
            }
            
            return 0;
        }

        $this->info("Found {$totalCleanup} orphaned/temporary photos:");

        foreach ($cleanupPhotos as $photo) {
            // Strip asset() prefix and domain to get relative storage path
            $relativePath = $this->stripAssetPrefix($photo->public_path);
            $fileExists = Storage::disk('public')->exists($relativePath);
            $fileSize = $fileExists ? Storage::disk('public')->size($relativePath) : 0;
            $totalSize += $fileSize;

            $this->line("  - ID: {$photo->id} | Path: {$photo->public_path} | Created: {$photo->created_at} | Size: " . $this->formatBytes($fileSize) . " | Exists: " . ($fileExists ? 'Yes' : 'No'));

            if (!$dryRun) {
                try {
                    // Delete the file if it exists
                    if ($fileExists) {
                        Storage::disk('public')->delete($relativePath);
                    }
                    
                    // Always delete the database record to keep DB in sync
                    DB::table('photos')->where('id', $photo->id)->delete();
                    $deletedCount++;
                    $this->line("    ✅ Deleted");
                } catch (\Exception $e) {
                    $this->error("    ❌ Failed to delete: " . $e->getMessage());
                    Log::error('Failed to delete orphaned/temporary photo', [
                        'photo_id' => $photo->id,
                        'path' => $photo->public_path,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        // Also clean up any orphaned files in storage that aren't in database
        $this->cleanupOrphanedStorageFiles($cutoffTime, $dryRun);

        $this->info('================================================');
        $this->info("Summary:");
        $this->line("  Total orphaned/temporary photos: {$totalCleanup}");
        $this->line("  Total disk space: " . $this->formatBytes($totalSize));
        
        if (!$dryRun) {
            $this->line("  Files deleted: {$deletedCount}");
        } else {
            $this->line("  Files that would be deleted: {$totalCleanup}");
            $this->info("💡 Run without --dry-run to actually delete these files");
        }

        Log::info('Orphaned/temporary photo cleanup completed', [
            'deleted_count' => $deletedCount,
            'total_size_bytes' => $totalSize,
            'dry_run' => $dryRun
        ]);

        return 0;
    }

    /**
     * Strip asset() prefix or domain to get relative storage path
     */
    private function stripAssetPrefix(string $publicPath): string
    {
        // Remove asset('storage/') prefix if present
        if (str_starts_with($publicPath, asset('storage/'))) {
            return substr($publicPath, strlen(asset('storage/')));
        }

        // Remove common domain patterns
        $patterns = [
            'https://intellihatch.bfcgroup.ph/storage/',
            'http://intellihatch.bfcgroup.ph/storage/',
            'storage/',
        ];

        foreach ($patterns as $pattern) {
            if (str_starts_with($publicPath, $pattern)) {
                return substr($publicPath, strlen($pattern));
            }
        }

        // If no pattern matches, return as-is
        return $publicPath;
    }

    /**
     * Clean up orphaned files in storage that aren't in database
     */
    private function cleanupOrphanedStorageFiles($cutoffTime, $dryRun): void
    {
        $orphanedCount = 0;
        
        try {
            // Get all files in storage/forms directory
            $storageFiles = Storage::disk('public')->allFiles('forms');
            
            foreach ($storageFiles as $file) {
                // Skip if not a temporary file
                if (!str_contains($file, '_pending_') && !str_contains($file, 'FORMID')) {
                    continue;
                }

                // Check if file exists in database
                $publicUrl = asset('storage/' . $file);
                $existsInDb = DB::table('photos')
                    ->where('public_path', $publicUrl)
                    ->exists();

                // Delete if not in database and older than 24 hours
                if (!$existsInDb) {
                    $filePath = 'forms/' . $file;
                    $lastModified = Storage::disk('public')->lastModified($filePath);
                    
                    if ($lastModified && $lastModified < $cutoffTime->timestamp) {
                        if (!$dryRun) {
                            Storage::disk('public')->delete($filePath);
                        }
                        $orphanedCount++;
                    }
                }
            }

            if ($orphanedCount > 0) {
                $this->info("- Cleaned up {$orphanedCount} orphaned storage files");
            }

        } catch (\Exception $e) {
            Log::error('Failed to cleanup orphaned storage files', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
