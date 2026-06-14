<?php

namespace App\Filament\Resources\Events\Widgets;

use App\Models\EventReceptionist;
use App\Models\Receptionist;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\UserManagement\Models\User;

class ReceptionistTable extends TableWidget
{
    public ?Model $record = null;

    protected function isReceptionist(): bool
    {
        return auth()->user()?->hasRole('Receptionist') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => EventReceptionist::query()
                ->with(['receptionist', 'event'])
                ->where('event_id', $this->record?->id))
            ->columns([
                TextColumn::make('receptionist.name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('receptionist.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('receptionist.phone_number')
                    ->label('Phone')
                    ->searchable(),
                TextColumn::make('receptionist.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'INACTIVE' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('code_uuid')
                    ->label('Scanner Code')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('scanner_link')
                    ->label('Scanner Link')
                    ->getStateUsing(fn(EventReceptionist $record) => url("/scan-presence/{$record->code_uuid}"))
                    ->copyable()
                    ->tooltip(fn(EventReceptionist $record) => url("/scan-presence/{$record->code_uuid}")),
                TextColumn::make('pin')
                    ->label('PIN')
                    // ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions(
                array_filter([
                    !$this->isReceptionist() ? CreateAction::make('Assign Receptionist')
                        ->label('Assign Receptionist')
                        ->form([
                            Hidden::make('event_id')
                                ->default(fn() => $this->record?->id),
                            Hidden::make('code_uuid')
                                ->default(fn() => (string) Str::uuid()),
                            Select::make('receptionist_id')
                                ->label('Receptionist')
                                ->options(function () {
                                    // Exclude receptionists already assigned to this event
                                    $assignedIds = EventReceptionist::where('event_id', $this->record?->id)
                                        ->pluck('receptionist_id')
                                        ->toArray();

                                    return Receptionist::where('status', Receptionist::STATUS_ACTIVE)
                                        ->whereNotIn('id', $assignedIds)
                                        ->get()
                                        ->mapWithKeys(fn($r) => [$r->id => "{$r->name} ({$r->email})"]);
                                })
                                ->required()
                                ->preload()
                                ->live()
                                ->searchable(),
                            TextInput::make('pin')
                                ->label('Scanner PIN')
                                ->required()
                                ->minLength(4)
                                ->maxLength(6)
                                ->default(fn() => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT))
                                ->helperText('4-6 digit PIN for scanner page authentication'),
                        ]) : null,
                ])
            )
            ->recordActions(
                array_filter([
                    // Copy scanner link action
                    Action::make('openScanner')
                        ->label('Open Scanner')
                        ->icon('heroicon-o-qr-code')
                        ->color('info')
                        ->url(fn(EventReceptionist $record) => url("/scan-presence/{$record->code_uuid}"))
                        ->openUrlInNewTab(),

                    !$this->isReceptionist() ? DeleteAction::make() : null,
                ])
            )
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
