<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Store;
use App\Services\Store\StoreBrochureService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Process Brochure Upload Job
 *
 * Handles asynchronous processing of brochure PDF uploads including
 * validation, optimization, and storage.
 */
class ProcessBrochureUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $storeId,
        public readonly string $tempPath,
        public readonly string $originalName
    ) {}

    /**
     * Execute the job.
     */
    public function handle(StoreBrochureService $brochureService): void
    {
        $store = Store::findOrFail($this->storeId);

        // Validate file exists
        if (! Storage::disk('local')->exists($this->tempPath)) {
            $this->fail(new \RuntimeException('Temporary file not found'));
            return;
        }

        // Generate unique filename
        $filename = $this->generateFilename($store, $this->originalName);
        $destinationPath = 'brochures/' . $filename;

        // Move file from temp to public storage
        Storage::disk('public')->put(
            $destinationPath,
            Storage::disk('local')->get($this->tempPath)
        );

        // Delete temp file
        Storage::disk('local')->delete($this->tempPath);

        // Update store with new brochure path
        $store->update(['brochure_path' => $destinationPath]);

        // Clear any cached brochures
        $brochureService->forgetGeneratedCacheByBranchCode($store->branch_code);
    }

    /**
     * Generate a unique filename for the brochure.
     */
    protected function generateFilename(Store $store, string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = Str::slug($store->name);
        $timestamp = now()->format('YmdHis');

        return "{$baseName}_{$timestamp}.{$extension}";
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Clean up temp file on failure
        Storage::disk('local')->delete($this->tempPath);

        // Log the failure
        \Log::error('Brochure upload failed', [
            'store_id' => $this->storeId,
            'temp_path' => $this->tempPath,
            'error' => $exception->getMessage(),
        ]);
    }
}
