<?php

namespace App\Filament\Resources\ScoringDefinitionResource\Pages;

use App\Filament\Resources\ScoringDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScoringDefinition extends EditRecord
{
    protected static string $resource = ScoringDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
