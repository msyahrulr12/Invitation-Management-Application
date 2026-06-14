<?php

namespace App\Filament\Resources\Receptionists\Pages;

use App\Filament\Resources\Receptionists\ReceptionistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReceptionists extends ListRecords
{
    protected static string $resource = ReceptionistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
