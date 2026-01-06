<?php

namespace App\Services;

use App\Models\LeadKnowledgeDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\PdfToText\Pdf;
use Exception;

class KnowledgeBaseService
{
    protected string $baseUrl = 'http://192.168.0.32:8002/api/v1';

    /**
     * Extract text from PDF using pdftotext binary.
     */
    public function extractText(string $path): string
    {
        // Spatie PdfToText wrapper
        // Assumes pdftotext is available in PATH or specify path if needed.
        // On standard linux: /usr/bin/pdftotext
        
        try {
            $text = Pdf::getText($path);
            return trim($text);
        } catch (Exception $e) {
            Log::error("Failed to extract text from {$path}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ingest document to AI.
     */
    public function ingest(LeadKnowledgeDocument $document, string $text): bool
    {
        $payload = [
            'content_id' => "knowledge_documents_{$document->id}",
            'source' => 'knowledge_base',
            'title' => $document->filename,
            'body_content' => $text,
            'hash' => $document->content_hash,
            'metadata' => [
                'client_id' => $document->client->id, // UUID
                'category' => 'knowledge_base',
                'original_filename' => $document->filename,
            ]
        ];

        try {
            $response = Http::post("{$this->baseUrl}/ingest", $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error("Ingest failed for doc {$document->id}: " . $response->body());
            return false;

        } catch (Exception $e) {
            Log::error("Ingest connection error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete document vectors.
     */
    public function deleteDocument(string $clientId, string $contentId): bool
    {
        try {
            $response = Http::delete("{$this->baseUrl}/client/{$clientId}/document/{$contentId}");
            return $response->successful();
        } catch (Exception $e) {
            Log::error("Delete document error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete client vectors.
     */
    public function deleteClient(string $clientId): bool
    {
         try {
            $response = Http::delete("{$this->baseUrl}/client/{$clientId}");
            return $response->successful();
        } catch (Exception $e) {
            Log::error("Delete client error: " . $e->getMessage());
            return false;
        }
    }
}
