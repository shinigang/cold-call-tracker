<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallStatus extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'status',
        'status_group_id'
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(CallStatusGroup::class, 'status_group_id');
    }
}
