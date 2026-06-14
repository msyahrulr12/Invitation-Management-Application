<?php

namespace App\Filament\Resources\Events\Widgets;

use App\Filament\Exports\VisitorExporter;
use App\Filament\Imports\VisitorImporter;
use App\Jobs\GenerateVisitorQrCode;
use App\Jobs\SendVisitorInvitationEmail;
use App\Models\Event;
use App\Models\Visitor;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VisitorTable extends TableWidget
{
    public ?Model $record = null;

    protected function isReceptionist(): bool
    {
        return auth()->user()?->hasRole('Receptionist') ?? false;
    }

    protected function isEventFinished(): bool
    {
        if (!$this->record) {
            return false;
        }

        return $this->record->status === Event::STATUS_COMPLETED
            || ($this->record->finished_at && $this->record->finished_at->isPast());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => Visitor::query()->where('event_id', $this->record?->id))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PRESENCE' => 'success',
                        'ABSENCE' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('presence_timestamp')
                    ->label('Presence At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('receptionist_name')
                    ->label('Scanned By')
                    ->searchable()
                    ->placeholder('—'),
                IconColumn::make('invitation_email_sent')
                    ->label('Email Sent')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('code_uuid')
                    ->label('UUID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    // Create visitor (admin only)
                    !$this->isReceptionist() ? CreateAction::make('Create')
                        ->form([
                            Hidden::make('event_id')
                                ->default(fn() => $this->record?->id),
                            Hidden::make('code_uuid')
                                ->default(fn() => (string) Str::uuid()),
                            Hidden::make('status')
                                ->default(Visitor::STATUS_ABSENCE),
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->email()
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->tel()
                                ->maxLength(50),
                            Textarea::make('address'),
                            Textarea::make('description'),
                        ])
                        ->after(function (Model $record) {
                            // Dispatch QR code generation in background
                            GenerateVisitorQrCode::dispatch($record);

                            // Also dispatch invitation email
                            if ($record->email) {
                                SendVisitorInvitationEmail::dispatch($record);
                            }
                        }) : null,

                    // Import visitors (admin only)
                    !$this->isReceptionist() ? ImportAction::make()
                        ->importer(VisitorImporter::class)
                        ->options([
                            'event_id' => $this->record?->id,
                        ]) : null,

                    // Export visitors
                    ExportAction::make()
                        ->exporter(VisitorExporter::class)
                        ->modifyQueryUsing(fn(Builder $query) => $query->where('event_id', $this->record?->id))
                        ->label('Export Visitors')
                        ->icon('heroicon-o-arrow-down-tray'),

                    // Send bulk invitation emails (admin only)
                    !$this->isReceptionist() ? Action::make('sendAllInvitations')
                        ->label('Send All Invitations')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Send Invitation Emails')
                        ->modalDescription('This will send invitation emails to all visitors who have an email address and have not yet received an invitation.')
                        ->action(function () {
                            $visitors = Visitor::where('event_id', $this->record?->id)
                                ->whereNotNull('email')
                                ->where('email', '!=', '')
                                ->where('invitation_email_sent', false)
                                ->get();

                            if ($visitors->isEmpty()) {
                                Notification::make()
                                    ->title('No Pending Invitations')
                                    ->body('All visitors with email addresses have already been sent invitations.')
                                    ->info()
                                    ->send();
                                return;
                            }

                            foreach ($visitors as $visitor) {
                                SendVisitorInvitationEmail::dispatch($visitor);
                            }

                            Notification::make()
                                ->title('Invitations Queued')
                                ->body("Sending invitations to {$visitors->count()} visitors...")
                                ->success()
                                ->send();
                        }) : null,
                ])
            )
            ->recordActions(
                array_filter([
                    // Show QR in modal (available for receptionist)
                    Action::make('showQr')
                        ->label('Show QR')
                        ->icon('heroicon-o-qr-code')
                        ->color('info')
                        ->modalHeading(fn(Visitor $record) => "QR Code — {$record->name}")
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalContent(fn(Visitor $record) => view('components.visitor-qr-modal', ['visitor' => $record])),

                    // Send individual invitation email (admin only)
                    !$this->isReceptionist() ? Action::make('sendInvitation')
                        ->label('Send Email')
                        ->icon('heroicon-o-envelope')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(Visitor $record) => $record->email && !$record->invitation_email_sent)
                        ->action(function (Visitor $record) {
                            SendVisitorInvitationEmail::dispatch($record);

                            Notification::make()
                                ->title('Invitation Queued')
                                ->body("Invitation email will be sent to {$record->email}")
                                ->success()
                                ->send();
                        }) : null,
                ])
            )
            ->toolbarActions([
                BulkActionGroup::make(
                    array_filter([
                        !$this->isReceptionist() ? BulkAction::make('sendBulkInvitations')
                            ->label('Send Invitations')
                            ->icon('heroicon-o-envelope')
                            ->requiresConfirmation()
                            ->action(function (Collection $records) {
                                $sent = 0;
                                foreach ($records as $visitor) {
                                    if ($visitor->email && !$visitor->invitation_email_sent) {
                                        SendVisitorInvitationEmail::dispatch($visitor);
                                        $sent++;
                                    }
                                }

                                Notification::make()
                                    ->title('Invitations Queued')
                                    ->body("Sending invitations to {$sent} visitors...")
                                    ->success()
                                    ->send();
                            }) : null,
                    ])
                ),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
