<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOrphanedPhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-orphaned-photos {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned photos that are not associated with any submitted form';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $this->info($dryRun ? '🔍 DRY RUN MODE - No files will be deleted' : '🗑️  CLEANUP MODE - Files will be deleted');
        $this->info('================================================');

        // Find photos older than 30 minutes that are not referenced in any submitted form
        $cutoffTime = Carbon::now('Asia/Manila')->subMinutes(30);
        
        $this->info("Looking for orphaned photos older than: {$cutoffTime->format('Y-m-d H:i:s')} (Asia/Manila)");

        // Get all photos older than 30 minutes
        $oldPhotos = DB::table('photos')
            ->where('created_at', '<', $cutoffTime)
            ->select('id', 'public_path', 'created_at')
            ->get();

        $orphanedPhotos = [];
        $totalSize = 0;
        $deletedCount = 0;

        foreach ($oldPhotos as $photo) {
            $isOrphaned = false;
            
            // Check 1: Photo has FORMID in filename (indicates unfinished form)
            if (strpos($photo->public_path, 'FORMID') !== false) {
                $isOrphaned = true;
                $this->line("  - 🎯 Found FORMID pattern in: {$photo->public_path}");
            }
            
            // Check 2: Photo is not referenced in any submitted form
            if (!$isOrphaned) {
                $isReferenced = DB::table('forms')
                    ->where('form_inputs', 'LIKE', '%' . $photo->public_path . '%')
                    ->exists();
                
                if (!$isReferenced) {
                    $isOrphaned = true;
                }
            }
            
            if ($isOrphaned) {
                $orphanedPhotos[] = $photo;
            }
        }

        $totalOrphaned = count($orphanedPhotos);

        if ($totalOrphaned === 0) {
            $this->info('✅ No orphaned photos found.');
            
            // Let's also check for very recent photos with FORMID pattern
            $recentPhotos = DB::table('photos')
                ->where('created_at', '>=', $cutoffTime)
                ->where('public_path', 'LIKE', '%FORMID%')
                ->select('id', 'public_path', 'created_at')
                ->get();
                
            if ($recentPhotos->count() > 0) {
                $this->info("ℹ️  Found {$recentPhotos->count()} recent photos with FORMID (too recent to cleanup):");
                foreach ($recentPhotos as $photo) {
                    $this->line("  - ID: {$photo->id} | Path: {$photo->public_path} | Created: {$photo->created_at}");
                }
                $this->info("💡 These will be eligible for cleanup after 30 minutes");
            }
            
            return 0;
        }

        $this->info("Found {$totalOrphaned} orphaned photos:");

        foreach ($orphanedPhotos as $photo) {
            $filePath = public_path($photo->public_path);
            $fileExists = File::exists($filePath);
            $fileSize = $fileExists ? File::size($filePath) : 0;
            $totalSize += $fileSize;

            $this->line("  - ID: {$photo->id} | Path: {$photo->public_path} | Created: {$photo->created_at} | Size: " . $this->formatBytes($fileSize) . " | Exists: " . ($fileExists ? 'Yes' : 'No'));

            if (!$dryRun && $fileExists) {
                try {
                    // Delete the file
                    File::delete($filePath);
                    
                    // Delete the database record
                    DB::table('photos')->where('id', $photo->id)->delete();
                    
                    $deletedCount++;
                    $this->line("    ✅ Deleted");
                } catch (\Exception $e) {
                    $this->error("    ❌ Failed to delete: " . $e->getMessage());
                    Log::error('Failed to delete orphaned photo', [
                        'photo_id' => $photo->id,
                        'path' => $photo->public_path,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        $this->info('================================================');
        $this->info("Summary:");
        $this->line("  Total orphaned photos: {$totalOrphaned}");
        $this->line("  Total disk space: " . $this->formatBytes($totalSize));
        
        if (!$dryRun) {
            $this->line("  Files deleted: {$deletedCount}");
        } else {
            $this->line("  Files that would be deleted: {$totalOrphaned}");
            $this->info("💡 Run without --dry-run to actually delete these files");
        }

        return 0;
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
