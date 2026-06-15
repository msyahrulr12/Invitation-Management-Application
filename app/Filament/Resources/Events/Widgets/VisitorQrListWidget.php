<?php

namespace App\Filament\Resources\Events\Widgets;

use App\Models\Visitor;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class VisitorQrListWidget extends Widget
{
    public ?Model $record = null;
    protected string $view = 'filament.resources.events.widgets.visitor-qr-list-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public string $search = '';

    public string $statusFilter = '';


    public function getVisitors(): array
    {
        $visitors = Visitor::query()
            ->when(
                filled($this->search),
                fn($q) =>
                $q->where('name', 'ILIKE', "%{$this->search}%")
                    ->orWhere('description', 'ILIKE', "%{$this->search}%")
            )
            ->where('event_id', $this->record->id)
            ->get()
            ->toArray();

        if (filled($this->statusFilter)) {
            $visitors = array_filter(
                $visitors,
                fn($v) =>
                strtolower($v['status'] ?? '') === $this->statusFilter
            );
        }

        return array_values($visitors);
    }
}
