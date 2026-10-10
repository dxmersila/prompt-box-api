<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    public const EVENT_VISIT = 'visit';

    public const EVENT_ITEM_ACCESS = 'item_access';

    public const EVENT_ITEM_COPY = 'item_copy';

    public $timestamps = false;

    protected $fillable = [
        'event',
        'visitor_id',
        'session_id',
        'user_id',
        'item_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
