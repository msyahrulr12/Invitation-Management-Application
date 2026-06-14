<?php

namespace App\Filament\Imports;

use App\Models\Event;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class EventImporter extends Importer
{
    protected static ?string $model = Event::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('description'),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('started_at')
                ->requiredMapping()
                ->rules(['required', 'datetime']),
            ImportColumn::make('finished_at')
                ->requiredMapping()
                ->rules(['required', 'datetime']),
            ImportColumn::make('google_maps_location_url')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('google_maps_location_address')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('google_maps_location_lat')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('google_maps_location_lng')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('created_by')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('updated_by')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
        ];
    }

    public function resolveRecord(): Event
    {
        return Event::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your event import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
