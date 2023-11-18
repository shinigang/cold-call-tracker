<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactPerson extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contact_persons';

    protected $fillable = [
        'company_id',
        'prefix',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'position',
        'verified'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
