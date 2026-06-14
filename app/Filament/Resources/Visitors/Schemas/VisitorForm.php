<?php

namespace App\Filament\Resources\Visitors\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VisitorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('event_id')
                    ->required()
                    ->numeric(),
                TextInput::make('code_uuid')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required(),
                FileUpload::make('presence_image')
                    ->image(),
                FileUpload::make('presence_image_url')
                    ->image(),
                TextInput::make('presence_latitude'),
                TextInput::make('presence_longitude'),
                DateTimePicker::make('presence_timestamp'),
                TextInput::make('receptionist_id')
                    ->numeric(),
                TextInput::make('receptionist_name'),
                TextInput::make('receptionist_code_uuid'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->required()
                    ->numeric(),
                TextInput::make('updated_by')
                    ->required()
                    ->numeric(),
            ]);
    }
}
