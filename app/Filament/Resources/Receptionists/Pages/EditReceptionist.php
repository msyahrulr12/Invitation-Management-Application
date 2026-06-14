<?php

namespace App\Filament\Resources\Receptionists\Pages;

use App\Filament\Resources\Receptionists\ReceptionistResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditReceptionist extends EditRecord
{
    protected static string $resource = ReceptionistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
