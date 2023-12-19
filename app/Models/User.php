<?php

namespace App\Models;

use Carbon\Carbon;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'nickname', 'metadata', 'google_metadata', 'availability', 'current_team_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'google_metadata'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'metadata' => 'array',
        'google_metadata' => AsArrayObject::class,
        'availability' => 'array'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'timeslots',
        'upcoming_meetings'
    ];

    /**
     * Timeslots
     *
     * @return array
     */
    public function getTimeslotsAttribute()
    {
        $timeSlots = [];
        if ($this->availability['shift_start'] && $this->availability['shift_end'] && $this->availability['meeting_duration']) {
            $startTime = Carbon::now()->setTimeFrom($this->availability['shift_start']);
            $endTime = Carbon::now()->setTimeFrom($this->availability['shift_end']);
            $duration = $this->availability['meeting_duration'];

            while ($startTime->lessThan($endTime)) {
                $timeSlots[] = [
                    'start' => Carbon::parse($startTime)->format('H:i'),
                    'end' => Carbon::parse($startTime)->addMinutes($duration)->format('H:i'),
                ];
                $startTime->addMinutes($duration);
            }
        }
        return $timeSlots;
    }

    /**
     * Upcoming Meetings
     * 
     * @return array
     */
    public function getUpcomingMeetingsAttribute()
    {
        return $this->calendarEvents()->where('start', '>', now())->get();
    }

    /**
     * Get all of the calendar events for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    /**
     * Get all of the socials for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function socials(): HasMany
    {
        return $this->hasMany(Social::class);
    }

    /**
     * Set google metadata
     *
     * @param string|null $google_uid
     * @param string $token
     * @param string $refresh_token
     * @param int $expires_in
     * @return boolean
     */
    public function setGoogleMetadata($google_uid = null, $token, $refresh_token, $expires_in)
    {
        $this->google_metadata = array_merge(
            [
                'token' => $token,
                'refresh_token' => $refresh_token,
                'token_expiry' => Carbon::now()->addSeconds($expires_in),
            ],
            $google_uid ? ['google_uid' => $google_uid] : []
        );
        $this->save();
    }
}
