<?php

namespace App\Jobs;

use App\Models\LeadKnowledgeDocument;
use App\Services\KnowledgeBaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class IngestDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes for PDF processing

    public $tries = 3;
    public $backoff = [10, 60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public LeadKnowledgeDocument $document
    ) {}

    /**
     * Execute the job.
     */
    public function handle(KnowledgeBaseService $service): void
    {
        Log::info("Starting ingestion for document {$this->document->id}");

        try {
            $path = Storage::disk('local')->path($this->document->storage_path);
            
            if (!file_exists($path)) {
                $this->failJob("File not found at path: {$path}", false);
                return;
            }

            // 1. Extract Text
            try {
                $text = $service->extractText($path);
            } catch (\Exception $e) {
                // If extraction fails (e.g. binary missing), it might be permanent config issue.
                // But we can throw to retry in case it's a temporary lock? 
                // Let's fail hard for now if binary is missing.
                $this->failJob("Extraction failed: " . $e->getMessage(), false);
                return;
            }

            // 2. Validate
            if (strlen($text) < 50) {
                $this->failJob("Text too short (< 50 chars). Possible scanned image.", false);
                return;
            }

            // 3. Calculate Hash
            $hash = hash('sha256', $text);

            // 4. Update Model with Hash (Optimistic)
            $this->document->content_hash = $hash;
            $this->document->saveQuietly(); 

            // 5. Sync to AI
            $success = $service->ingest($this->document, $text);

            if ($success) {
                $this->document->sync_status = 'SYNCED';
                $this->document->last_synced_at = now();
                $this->document->error_message = null; // Clear error
                $this->document->saveQuietly();
                Log::info("Document {$this->document->id} synced successfully.");
            } else {
                // If API returns false, we throw exception to trigger Retry
                throw new \Exception("API Ingest returned failure.");
            }

        } catch (\Exception $e) {
            // Rethrow exception to let Laravel handle Retries
             $this->failJob("Exception: " . $e->getMessage(), true);
             throw $e;
        }
    }

    protected function failJob(string $reason, bool $isRetryable): void
    {
        Log::error("Ingestion failed for doc {$this->document->id}: {$reason}");
        
        // Update DB with error
        $this->document->sync_status = 'FAILED';
        $this->document->error_message = $reason;
        $this->document->saveQuietly();
    }
}
