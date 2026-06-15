<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\Widgets\ReceptionistTable;
use App\Filament\Resources\Events\Widgets\VisitorTable;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Widgets\VisitorQrListWidget;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Only show Edit button if user is NOT a receptionist
        if (!auth()->user()?->hasRole('Receptionist')) {
            $actions[] = EditAction::make();
        }

        return $actions;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Detail')
                            ->schema([
                                $this->hasInfolist() // This method returns `true` if the page has an infolist defined
                                    ? $this->getInfolistContentComponent() // This method returns a component to display the infolist that is defined in this resource
                                    : $this->getFormContentComponent(), // This method returns a component to display the form that is defined in this resource
                                $this->getRelationManagersContentComponent()
                            ]),
                        Tab::make('Receptionist')
                            ->schema([
                                Livewire::make(ReceptionistTable::class, [
                                    'record' => $this->getRecord(),
                                ]),
                            ]),
                        Tab::make('Visitor')
                            ->schema([
                                Livewire::make(VisitorTable::class, [
                                    'record' => $this->getRecord(),
                                ]),
                            ]),
                        Tab::make('Visitor QR List')
                            ->schema([
                                Livewire::make(VisitorQrListWidget::class, [
                                    'record' => $this->getRecord(),
                                ]),
                            ]),
                    ]),
            ]);
    }
}
