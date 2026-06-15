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
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
                // TextColumn::make('scanner_qr')
                //     ->label('Scanner QR')
                //     ->getStateUsing(fn(EventReceptionist $record) => $record->code_uuid ? url("/scan-presence/{$record->code_uuid}") : null)
                //     ->html()
                //     ->formatStateUsing(fn(?string $state) => $state ? '<div style="background: white; padding: 2px; border-radius: 4px; border: 1px solid #e5e7eb; display: inline-block; cursor: pointer;">' . QrCode::size(40)->margin(0)->generate($state) . '</div>' : '—')
                //     ->action(fn(EventReceptionist $record) => $record->code_uuid ? $this->mountTableAction('viewQr', $record->getKey()) : null),
                TextColumn::make('scanner_link')
                    ->label('Scanner Link')
                    ->getStateUsing(fn(EventReceptionist $record) => $record->code_uuid ? url("/scan-presence/{$record->code_uuid}") : null)
                    ->copyable()
                    ->tooltip(fn(EventReceptionist $record) => $record->code_uuid ? url("/scan-presence/{$record->code_uuid}") : null)
                    ->placeholder('—'),
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
                    // View QR Code modal
                    Action::make('viewQr')
                        ->label('View QR')
                        ->icon('heroicon-o-qr-code')
                        ->color('success')
                        ->visible(fn(EventReceptionist $record) => !empty($record->code_uuid))
                        ->modalHeading('Scanner QR Code')
                        ->modalWidth('md')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalContent(fn(EventReceptionist $record) => view(
                            'filament.resources.events.widgets.receptionist-qr-modal',
                            ['url' => url("/scan-presence/{$record->code_uuid}")]
                        )),

                    // Copy scanner link action
                    Action::make('openScanner')
                        ->label('Open Scanner')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->color('info')
                        ->visible(fn(EventReceptionist $record) => !empty($record->code_uuid))
                        ->url(fn(EventReceptionist $record) => url("/scan-presence/{$record->code_uuid}"))
                        ->openUrlInNewTab(),

                    // Generate link if it doesn't exist
                    Action::make('generateScannerLink')
                        ->label('Generate Link')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn(EventReceptionist $record) => empty($record->code_uuid))
                        ->action(function (EventReceptionist $record) {
                            $record->update([
                                'code_uuid' => (string) \Illuminate\Support\Str::uuid(),
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Scanner Link Generated')
                                ->success()
                                ->send();
                        }),

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
