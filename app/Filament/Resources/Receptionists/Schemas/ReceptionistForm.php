<?php

namespace App\Filament\Resources\Receptionists\Schemas;

use App\Models\Receptionist;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Modules\UserManagement\Models\User;
use Illuminate\Support\Str;

class ReceptionistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->options(function () {
                        return User::query()
                            ->whereHas('roles', function ($query) {
                                $query->where('name', 'Receptionist');
                            })
                            ->get()
                            ->mapWithKeys(fn($r) => [$r->id => "{$r->name} ({$r->email})"]);
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $user = User::find($state);
                        $set('name', $user->name);
                        $set('email', $user->email);
                        $set('phone_number', $user->phone);
                    })
                    ->dehydrated()
                    ->preload()
                    ->searchable(),
                Hidden::make('code_uuid')
                    ->default(fn() => (string) Str::uuid()),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone_number')
                    ->tel(),
                Select::make('status')
                    ->options(Receptionist::STATUS_DATA)
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
