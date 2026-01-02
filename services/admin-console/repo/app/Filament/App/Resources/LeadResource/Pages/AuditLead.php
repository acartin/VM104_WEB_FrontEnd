<?php
 
 namespace App\Filament\App\Resources\LeadResource\Pages;
 
 use App\Filament\App\Resources\LeadResource;
 use App\Models\Lead;
 use Filament\Resources\Pages\Page;
 
 class AuditLead extends Page
 {
     protected static string $resource = LeadResource::class;
 
     protected static string $view = 'filament.app.resources.lead-resource.pages.audit-lead';
 
     public Lead $record;
 
     public function mount($record): void
     {
         if ($record instanceof Lead) {
             $this->record = $record;
         } else {
             $this->record = Lead::findOrFail($record);
         }
     }
 
     public function getTitle(): string
     {
         return "Chat Audit: " . $this->record->full_name;
     }
 }
