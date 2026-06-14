<?php

namespace App\Filament\Resources\Receptionists;

use App\Filament\Resources\Receptionists\Pages\CreateReceptionist;
use App\Filament\Resources\Receptionists\Pages\EditReceptionist;
use App\Filament\Resources\Receptionists\Pages\ListReceptionists;
use App\Filament\Resources\Receptionists\Schemas\ReceptionistForm;
use App\Filament\Resources\Receptionists\Tables\ReceptionistsTable;
use UnitEnum;
use App\Models\Receptionist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReceptionistResource extends Resource
{
    protected static ?string $model = Receptionist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Main Menu';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ReceptionistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceptionistsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceptionists::route('/'),
            'create' => CreateReceptionist::route('/create'),
            'edit' => EditReceptionist::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
