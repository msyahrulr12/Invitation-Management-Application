<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\UserManagement\Models\User;

class Receptionist extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_INACTIVE = 'INACTIVE';

    public const STATUS_DATA = [
        self::STATUS_ACTIVE => 'ACTIVE',
        self::STATUS_INACTIVE => 'INACTIVE',
    ];

    protected $fillable = [
        'user_id',
        'code_uuid',
        'name',
        'email',
        'phone_number',
        'status',
        'description',
        'created_by',
        'updated_by'
    ];

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_receptionist')
            ->withPivot(['code_uuid', 'pin', 'id'])
            ->withTimestamps();
    }

    public function eventReceptionists(): HasMany
    {
        return $this->hasMany(EventReceptionist::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
