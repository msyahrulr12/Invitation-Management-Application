<?php

namespace App\Filament\Resources\Visitors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class VisitorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('code_uuid')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                ImageColumn::make('presence_image'),
                ImageColumn::make('presence_image_url'),
                TextColumn::make('presence_latitude')
                    ->searchable(),
                TextColumn::make('presence_longitude')
                    ->searchable(),
                TextColumn::make('presence_timestamp')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('receptionist_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('receptionist_name')
                    ->searchable(),
                TextColumn::make('receptionist_code_uuid')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_by')
                    ->numeric()
                    ->sortable(),
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
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
