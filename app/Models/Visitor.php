<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visitor extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PRESENCE = 'PRESENCE';
    public const STATUS_ABSENCE = 'ABSENCE';
    public const STATUS_DATA = [
        self::STATUS_PENDING => 'PENDING',
        self::STATUS_PRESENCE => 'PRESENCE',
        self::STATUS_ABSENCE => 'ABSENCE',
    ];

    protected $fillable = [
        'event_id',
        'code_uuid',
        'name',
        'description',
        'status',
        'qr_code_path',
        'presence_image',
        'presence_image_url',
        'presence_latitude',
        'presence_longitude',
        'presence_timestamp',
        'receptionist_id',
        'receptionist_name',
        'receptionist_code_uuid',
        'email',
        'phone',
        'address',
        'invitation_email_sent',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'invitation_email_sent' => 'boolean',
            'presence_timestamp' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
