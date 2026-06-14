<?php

namespace App\Filament\Exports;

use App\Models\Visitor;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class VisitorExporter extends Exporter
{
    protected static ?string $model = Visitor::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('event.name')
                ->label('event_name'),
            ExportColumn::make('name')
                ->label('name'),
            ExportColumn::make('email')
                ->label('email'),
            ExportColumn::make('phone')
                ->label('phone_number'),
            ExportColumn::make('status')
                ->label('status'),
            ExportColumn::make('presence_timestamp')
                ->label('presence_at'),
            ExportColumn::make('receptionist_name')
                ->label('scanned_by'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your visitor export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
