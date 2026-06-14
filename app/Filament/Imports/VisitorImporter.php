<?php

namespace App\Filament\Imports;

use App\Jobs\GenerateVisitorQrCode;
use App\Models\Visitor;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class VisitorImporter extends Importer
{
    protected static ?string $model = Visitor::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->rules(['nullable', 'email', 'max:255']),
            ImportColumn::make('phone')
                ->rules(['nullable', 'max:50']),
            ImportColumn::make('address')
                ->rules(['nullable']),
            ImportColumn::make('description')
                ->rules(['nullable']),
        ];
    }

    public function resolveRecord(): Visitor
    {
        $visitor = new Visitor();
        $visitor->event_id = $this->options['event_id'] ?? null;
        $visitor->code_uuid = (string) Str::uuid();
        $visitor->status = Visitor::STATUS_PENDING;
        $visitor->created_by = auth()->id();
        $visitor->updated_by = auth()->id();

        return $visitor;
    }

    public function afterSave(): void
    {
        // Dispatch QR code generation in background
        GenerateVisitorQrCode::dispatch($this->record);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your visitor import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        $body .= ' QR codes are being generated in the background.';

        return $body;
    }
}
