<?php

namespace App\Filament\App\Resources\LeadResource\Pages;

use App\Filament\App\Resources\LeadResource;
use App\Models\Lead;
use App\Models\LeadStatus;
use Filament\Notifications\Notification;
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
        return $this->record->full_name;
    }

    public function getSubheading(): ?string
    {
        return $this->record->conversations()->latest('last_message_at')->first()?->summary;
    }

    public function updateStatus(string $statusId): void
    {
        $this->record->update([
            'status_id' => $statusId,
            'updated_at' => now(), // Forcing update for idle tracking
        ]);

        Notification::make()
            ->title('Status updated successfully')
            ->success()
            ->send();

        $this->record->refresh();
    }
}
