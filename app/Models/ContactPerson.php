<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPerson extends Model
{
    use HasFactory;

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
}
