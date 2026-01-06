<?php

namespace App\Observers;

use App\Models\LeadKnowledgeDocument;
use App\Jobs\IngestDocumentJob;
use App\Services\KnowledgeBaseService;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Storage;

class LeadKnowledgeDocumentObserver
{
    /**
     * Handle the LeadKnowledgeDocument "created" event.
     */
    public function created(LeadKnowledgeDocument $leadKnowledgeDocument): void
    {
        // Initial ingest
        $leadKnowledgeDocument->sync_status = 'PENDING';
        $leadKnowledgeDocument->saveQuietly();
        
        IngestDocumentJob::dispatch($leadKnowledgeDocument);
    }

    /**
     * Handle the LeadKnowledgeDocument "updated" event.
     */
    public function updated(LeadKnowledgeDocument $leadKnowledgeDocument): void
    {
        // If file reference changed, re-ingest
        if ($leadKnowledgeDocument->wasChanged('storage_path')) {
             $leadKnowledgeDocument->sync_status = 'PENDING';
             $leadKnowledgeDocument->saveQuietly();
             
             IngestDocumentJob::dispatch($leadKnowledgeDocument);
        }
    }

    /**
     * Handle the LeadKnowledgeDocument "deleting" event.
     */
    public function deleting(LeadKnowledgeDocument $leadKnowledgeDocument): void
    {
        // Clean up Vectors
        // We use the Service directly here to ensure it happens before DB delete completes
        try {
            $service = app(KnowledgeBaseService::class);
            // content_id is constructed as knowledge_documents_{id}
            $contentId = "knowledge_documents_{$leadKnowledgeDocument->id}";
            $clientId = $leadKnowledgeDocument->client_id; // UUID
            
            // Note: If client was already deleted, this might fail or be irrelevant? 
            // Case A implies deleting document while client exists.
            
            $service->deleteDocument($clientId, $contentId);
            
        } catch (\Exception $e) {
            Log::error("Failed to delete document vectors for {$leadKnowledgeDocument->id}: " . $e->getMessage());
        }
    }

    /**
     * Handle the LeadKnowledgeDocument "deleted" event.
     */
    public function deleted(LeadKnowledgeDocument $leadKnowledgeDocument): void
    {
        if ($leadKnowledgeDocument->storage_path) {
            Storage::disk('local')->delete($leadKnowledgeDocument->storage_path);
        }
    }
}
