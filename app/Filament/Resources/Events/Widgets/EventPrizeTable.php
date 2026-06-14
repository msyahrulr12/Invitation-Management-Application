<?php

namespace App\Filament\Resources\Events\Widgets;

use App\Models\EventPrize;
use App\Models\Prize;
use App\Models\TemporaryWinner;
use App\Models\DrawSession;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;


class EventPrizeTable extends TableWidget
{
    public ?Model $record = null;
    public ?int $event_id = null;
    public ?string $activeTab = null;
    public ?int $selectedDrawSession = null;

    public function setActiveTab(?int $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function getBaseQuery(): Builder
    {
        $query = EventPrize::query();

        if ($this->record) {
            $query->where('event_id', $this->record->id);
        } elseif ($this->event_id) {
            $query->where('event_id', $this->event_id);
        }

        return $query;
    }

    public function getTabs()
    {
        $drawSessions = $this->getDrawSessions();

        return $drawSessions;
    }

    public function getDrawSessions()
    {
        return DrawSession::where('event_id', $this->record->id)->orderBy('started_at', 'asc')->get();
    }

    public function parseTab(?int $drawSessionId): string
    {
        return $drawSessionId ? $this->getTabs()->filter(fn($drawSession) => (int) $drawSession->id === $drawSessionId)->first()->name : 'All';
    }

    public function table(Table $table): Table
    {
        $tabs = $this->getTabs();

        return $table->query(function (): Builder {
            $query = $this->getBaseQuery();
            if ($this->activeTab) {
                $query->where('draw_session_id', $this->activeTab);
            }
            return $query;
        })
            ->columns([
                TextColumn::make('drawSession.name')
                    ->label('Draw Session')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('prize.prize_code')
                    ->label('Prize Code')
                    ->searchable(),
                ImageColumn::make('prize.prize_image')
                    ->label('Image')
                    ->circular(),
                TextColumn::make('prize.prize_name')
                    ->label('Prize Name')
                    ->searchable(),
                TextColumn::make('prize.tier')
                    ->label('Prize Tier')
                    ->state(function ($record): string {
                        return Prize::PRIZE_TIER[$record->prize->tier ?? count(Prize::PRIZE_TIER) - 1];
                    })
                    ->searchable(),
                TextColumn::make('prize.value')
                    ->label('Prize Value')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('drawSession.name')
                    ->label('Draw Session')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('total_quantity')
                    ->label('Total Qty')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('remaining_quantity')
                    ->label('Remaining Qty')
                    ->state(function (EventPrize $record): int {
                        $activeSessionId = DrawSession::where('event_id', $record->event_id)
                            ->where('status', DrawSession::STATUS_ACTIVE)
                            ->where('started_at', '<=', now())
                            ->where('ended_at', '>=', now())
                            ->value('id');

                        $staged = $activeSessionId
                            ? TemporaryWinner::where('event_prize_id', $record->id)
                            ->where('draw_session_id', $activeSessionId)
                            ->count()
                            : 0;

                        return max(0, $record->remaining_quantity - $staged);
                    })
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_points_required')
                    ->label('Min Points Req.')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('total_value')
                    ->label('Total Value')
                    ->state(fn($record) => $record->prize->value * $record->total_quantity)
                    ->numeric(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('prize.value', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                ActionGroup::make([
                    Action::make('All Tabs')
                        ->label('All')
                        ->icon('heroicon-o-presentation-chart-line')
                        ->color(fn() => $this->activeTab === null ? 'primary' : 'gray')
                        ->action(fn() => $this->setActiveTab(null)),

                    ...$tabs->map(
                        fn($drawSession) => Action::make('tab_' . $drawSession->id)
                            ->label($drawSession->name)
                            ->icon("heroicon-o-presentation-chart-line")
                            ->color(fn() => (int) $this->activeTab === (int) $drawSession->id ? ($drawSession->status == DrawSession::STATUS_INACTIVE ? 'danger' : 'success') : 'gray')
                            ->action(fn() => $this->setActiveTab($drawSession->id))
                    ),
                ])
                    ->label("Choose Draw Session: " . $this->parseTab($this->activeTab))
                    ->icon("heroicon-o-calendar-days")
                    ->color('primary')
                    ->button(),
                ExportAction::make()
                    ->exporter(\App\Filament\Exports\EventPrizeExporter::class)
                    ->label('Export CSV/Excel'),
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->color('danger')
                    ->icon('heroicon-o-document-text')
                    ->action(function () {
                        $query = EventPrize::query()->with('prize');
                        if ($this->record) {
                            $query->where('event_id', $this->record->id);
                        } elseif ($this->event_id) {
                            $query->where('event_id', $this->event_id);
                        }
                        $records = $query->get();
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.event-prizes', ['records' => $records]);
                        return response()->streamDownload(fn() => print($pdf->output()), 'event-prizes.pdf');
                    }),
                CreateAction::make()
                    ->form([
                        Hidden::make('event_id')
                            ->default(fn() => $this->record?->id),
                        Select::make('draw_session_id')
                            ->relationship(
                                'drawSession',
                                'name',
                                fn(Builder $query) => $query
                                    ->where('event_id', $this->record?->id ?? $this->event_id)
                                    ->orderBy('started_at', 'asc')
                            )
                            ->afterStateUpdated(fn(Set $set) => $set('prize_id', null))
                            ->required()
                            ->searchable()
                            ->live()
                            ->preload(),
                        Select::make('prize_id')
                            ->relationship(
                                'prize',
                                'prize_name',
                                modifyQueryUsing: fn(Builder $query, Get $get) => $query->whereNotIn('id', EventPrize::where('draw_session_id', $get('draw_session_id'))->pluck('prize_id')->toArray())
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn(Get $get) => !$get('draw_session_id'))
                            ->placeholder(fn(Get $get) => $get('draw_session_id') ? 'Select Prize' : 'Select an draw session first'),
                        TextInput::make('total_quantity')
                            ->numeric()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn($state, $set) => [
                                $set('remaining_quantity', $state),
                                $set('split_draw', $state)
                            ]),
                        TextInput::make('remaining_quantity')
                            ->numeric()
                            ->required(),
                        TextInput::make('min_points_required')
                            ->numeric()
                            ->required(),
                        TextInput::make('split_draw')
                            ->numeric()
                            ->required(),
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Admin Draw')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->url(fn(EventPrize $record): string => in_array($record->prize->tier, [Prize::TIER_GRAND_PRIZE]) ? route('filament.admin.pages.draw-winner.{eventPrize}', ['eventPrize' => $record->id]) : route('filament.admin.pages.draw-winner-bulk.{eventPrize}', ['eventPrize' => $record->id]))
                    ->visible(false),
                ViewAction::make('public_draw')
                    ->label('Public Draw')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    // ->url(fn(EventPrize $record): string => in_array($record->prize->tier, [Prize::TIER_GRAND_PRIZE]) ? route('public.draw', ['uuid' => $record->uuid]) : route('public.draw-bulk', ['uuid' => $record->uuid]))
                    ->url(fn(EventPrize $record): string => route('public.draw-bulk', ['uuid' => $record->uuid]))
                    ->openUrlInNewTab(),
                EditAction::make()
                    ->form([
                        Hidden::make('event_id')
                            ->default(fn() => $this->record?->id),
                        TextInput::make('total_quantity')
                            ->numeric()
                            ->required(),
                        TextInput::make('remaining_quantity')
                            ->numeric()
                            ->required(),
                        TextInput::make('min_points_required')
                            ->numeric()
                            ->required(),
                        TextInput::make('split_draw')
                            ->numeric()
                            ->required(),
                        Select::make('draw_session_id')
                            ->options(function () {
                                return DrawSession::where('event_id', $this->record->id)
                                    ->orderBy('started_at', 'asc')
                                    ->pluck('name', 'id');
                            })
                            ->required(),
                    ]),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
