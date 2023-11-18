<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Laravel\Scout\Searchable;

class Company extends Model
{
    use HasFactory;
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'industry',
        'total_employees',
        'status',
        'call_status',
        'follow_up_date',
        'appointment_date',
        'email',
        'website',
        'linkedin',
        'referral_source',
        'address_street',
        'address_city',
        'address_state',
        'address_country',
        'address_zipcode',
        'created_by',
        'modified_by',
        'assigned_caller',
        'assigned_consultant',
    ];

    public function actionLogs(): HasMany
    {
        return $this->hasMany(ActionLog::class);
    }

    public function assignedCaller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_caller');
    }

    public function assignedConsultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_consultant');
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function contactPersons(): HasMany
    {
        return $this->hasMany(ContactPerson::class);
    }

    public function contactNumbers(): HasMany
    {
        return $this->hasMany(ContactNumber::class);
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        return [
            'id' => $array['id'],
            'name' => $array['name'],
            'industry' => $array['industry'],
            'call_status' => $array['call_status'],
            'website' => $array['website'],
            'email' => $array['email'],
            'linkedin' => $array['linkedin'],
            'address_city' => $array['address_city'],
            'address_state' => $array['address_state'],
            'address_country' => $array['address_country']
        ];
    }
}
