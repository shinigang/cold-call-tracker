<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallStatusGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'group',
        'color'
    ];

    public function callStatuses(): HasMany
    {
        return $this->hasMany(CallStatus::class);
    }
}
