<?php

namespace App\Models;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Call extends Model
{
    use BroadcastsEvents, HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'company_id', 'contact_number', 'called_at', 'status', 'follow_up_at', 'appointment_at', 'consultant_id', 'meeting_email'];

    protected $casts = [
        'called_at' => 'datetime',
        'follow_up_at' => 'datetime',
        'appointment_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function calendarEvent(): HasOne
    {
        return $this->hasOne(CalendarEvent::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('call-updates')
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function () {
            Cache::forget('calls_list');
        });

        static::updated(function () {
            Cache::forget('calls_list');
        });
    }
}
