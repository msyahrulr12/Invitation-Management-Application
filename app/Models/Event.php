<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_INACTIVE = 'INACTIVE';
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_DATA = [
        self::STATUS_ACTIVE => 'ACTIVE',
        self::STATUS_INACTIVE => 'INACTIVE',
        self::STATUS_DRAFT => 'DRAFT',
        self::STATUS_COMPLETED => 'COMPLETED',
    ];

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'image',
        'started_at',
        'finished_at',
        'google_maps_location_url',
        'google_maps_location_address',
        'google_maps_location_lat',
        'google_maps_location_lng',
        'created_by',
        'updated_by'
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function receptionists(): BelongsToMany
    {
        return $this->belongsToMany(Receptionist::class, 'event_receptionist')
            ->withPivot(['code_uuid', 'pin', 'id'])
            ->withTimestamps();
    }

    public function eventReceptionists(): HasMany
    {
        return $this->hasMany(EventReceptionist::class);
    }

    public function visitors(): HasMany
    {
        return $this->hasMany(Visitor::class);
    }
}
