<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'response_id',
        'field_changed',
        'old_value',
        'new_value',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(IndicatorResponse::class);
    }
}
