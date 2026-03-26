<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'data_period_start',
        'data_period_end',
        'submission_start',
        'submission_end',
        'status',
    ];

    protected $casts = [
        'data_period_start' => 'date',
        'data_period_end' => 'date',
        'submission_start' => 'date',
        'submission_end' => 'date',
        'year' => 'integer',
    ];

    public function profile(): HasMany
    {
        return $this->hasMany(UniversityProfile::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(IndicatorResponse::class);
    }

    public function latestProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UniversityProfile::class)->latestOfMany();
    }
}
