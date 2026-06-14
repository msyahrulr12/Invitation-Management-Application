<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\Event;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                FileUpload::make('image')
                    ->label('Image')
                    ->columnSpanFull()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory('uploads'),
                Select::make('status')
                    ->required()
                    ->options(Event::STATUS_DATA)
                    ->columnSpanFull()
                    ->required(),
                DateTimePicker::make('started_at')
                    ->required(),
                DateTimePicker::make('finished_at')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
