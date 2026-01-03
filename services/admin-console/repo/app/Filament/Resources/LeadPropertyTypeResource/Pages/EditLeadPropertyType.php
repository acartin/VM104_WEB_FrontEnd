<?php

namespace App\Filament\Resources\LeadPropertyTypeResource\Pages;

use App\Filament\Resources\LeadPropertyTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeadPropertyType extends EditRecord
{
    protected static string $resource = LeadPropertyTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
