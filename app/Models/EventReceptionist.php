<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventReceptionist extends Model
{
    protected $table = 'event_receptionist';

    protected $fillable = [
        'event_id',
        'receptionist_id',
        'code_uuid',
        'pin',
    ];

    protected $hidden = [
        'pin',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function receptionist(): BelongsTo
    {
        return $this->belongsTo(Receptionist::class);
    }
}
