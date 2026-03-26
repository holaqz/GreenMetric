<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniversityProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'cycle_id',
        'created_by',
        'total_area_hectares',
        'green_area_percent',
        'total_buildings',
        'total_students',
        'total_staff',
        'total_researchers',
    ];

    protected $casts = [
        'total_area_hectares' => 'decimal:2',
        'green_area_percent' => 'decimal:2',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
