<?php

namespace App\Filament\App\Resources\KnowledgeDocumentResource\Pages;

use App\Filament\App\Resources\KnowledgeDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Facades\Filament;

class ManageKnowledgeDocuments extends ManageRecords
{
    protected static string $resource = KnowledgeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['client_id'] = Filament::getTenant()->id;
                    // Status default is handled by Observer (PENDING)
                    return $data;
                }),
        ];
    }
}
