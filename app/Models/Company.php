<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'industry',
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

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
