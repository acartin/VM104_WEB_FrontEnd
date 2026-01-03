<?php

namespace App\Filament\Resources\ScoringDefinitionResource\Pages;

use App\Filament\Resources\ScoringDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScoringDefinitions extends ListRecords
{
    protected static string $resource = ScoringDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
