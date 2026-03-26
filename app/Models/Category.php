<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'weight_percent',
        'order',
    ];

    protected $casts = [
        'weight_percent' => 'decimal:2',
        'order' => 'integer',
    ];

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class);
    }

    /**
     * Получить индикаторы с ответами для конкретного цикла
     */
    public function getIndicatorsWithResponses(int $cycleId)
    {
        return $this->indicators()
            ->with(['responses' => function ($query) use ($cycleId) {
                $query->where('cycle_id', $cycleId)
                    ->with('files');
            }])
            ->orderBy('order')
            ->get();
    }

    /**
     * Получить процент заполнения категории
     */
    public function getCompletionPercentage(int $cycleId): float
    {
        $total = $this->indicators()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->indicators()
            ->whereHas('responses', function ($query) use ($cycleId) {
                $query->where('cycle_id', $cycleId)
                    ->where('status', 'approved');
            })
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Проверить, является ли категория Water (WR)
     */
    public function getIsWaterAttribute(): bool
    {
        return $this->code === 'WR';
    }

    /**
     * Проверить, является ли категория Energy (EC)
     */
    public function getIsEnergyAttribute(): bool
    {
        return $this->code === 'EC';
    }

    /**
     * Проверить, является ли категория Waste (WS)
     */
    public function getIsWasteAttribute(): bool
    {
        return $this->code === 'WS';
    }
}
