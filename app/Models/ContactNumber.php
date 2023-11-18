<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactNumber extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contact_numbers';

    protected $fillable = [
        'company_id',
        'label',
        'number',
        'verified'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
