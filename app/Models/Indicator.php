<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'code_in_category',
        'question_text',
        'unit',
        'input_type',
        'filename_slug',
        'description_help',
        'validation_rules',
        'order',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'order' => 'integer',
        'code_in_category' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(IndicatorResponse::class);
    }

    /**
     * Получить полное имя файла для экспорта
     * Пример: University_Country_4_1_Water_Conservation_Program_Implementation
     */
    public function getFullFilenameSlugAttribute(): string
    {
        $categoryCode = $this->category->code ?? 'CAT';
        return "{$categoryCode}_{$this->code_in_category}_{$this->filename_slug}";
    }

    /**
     * Получить варианты ответов для select-индикаторов
     */
    public function getOptionsAttribute(): array
    {
        return $this->validation_rules['options'] ?? [];
    }

    /**
     * Проверить, требует ли индикатор ввода площади
     */
    public function getHasAreaInputAttribute(): bool
    {
        return $this->validation_rules['has_area_input'] ?? false;
    }

    /**
     * Получить единицу измерения площади
     */
    public function getAreaUnitAttribute(): string
    {
        return $this->validation_rules['area_unit'] ?? 'м²';
    }

    /**
     * Получить ответ для конкретного цикла
     */
    public function getResponseForCycle(int $cycleId): ?IndicatorResponse
    {
        return $this->responses()->where('cycle_id', $cycleId)->first();
    }

    /**
     * Проверить, является ли индикатор вычисляемым (автоматический расчет)
     */
    public function getIsComputedAttribute(): bool
    {
        return $this->input_type === 'computed';
    }

    /**
     * Получить формулу для вычисления
     */
    public function getFormulaAttribute(): ?string
    {
        return $this->validation_rules['formula'] ?? null;
    }

    /**
     * Получить коды индикаторов, от которых зависит вычисление
     */
    public function getDependsOnAttribute(): array
    {
        return $this->validation_rules['depends_on'] ?? [];
    }

    /**
     * Проверить, является ли индикатор заполненным
     */
    public function isCompleted(int $cycleId): bool
    {
        $response = $this->getResponseForCycle($cycleId);
        
        if (!$response) {
            return false;
        }

        // Вычисляемые индикаторы считаются заполненными, если все зависимые индикаторы заполнены
        if ($this->is_computed) {
            $dependsOn = $this->depends_on;
            foreach ($dependsOn as $depCode) {
                $depIndicator = Indicator::where('category_id', $this->category_id)
                    ->where('code_in_category', $depCode)
                    ->first();
                if ($depIndicator && !$depIndicator->isCompleted($cycleId)) {
                    return false;
                }
            }
            return true;
        }

        return match ($this->input_type) {
            'select', 'select_with_area' => $response->selected_option !== null,
            'number' => $response->value_numeric !== null,
            'boolean' => $response->value_boolean !== null,
            'text' => $response->value_text !== null,
            default => false,
        };
    }

    /**
     * Вычислить значение для вычисляемого индикатора
     */
    public function computeValue(int $cycleId): ?float
    {
        if (!$this->is_computed || !$this->formula) {
            return null;
        }

        $dependsOn = $this->depends_on;
        $values = [];

        foreach ($dependsOn as $depCode) {
            // depCode может быть строкой "SI.5" или числом 5
            $depCodeStr = (string) $depCode;
            
            // Если формат "SI.5", извлекаем число после точки
            if (strpos($depCodeStr, '.') !== false) {
                $parts = explode('.', $depCodeStr);
                $depCodeNum = (int) end($parts);
            } else {
                $depCodeNum = (int) $depCodeStr;
            }
            
            // Ищем индикатор по code_in_category
            $depIndicator = Indicator::where('category_id', $this->category_id)
                ->where('code_in_category', $depCodeNum)
                ->first();

            if ($depIndicator) {
                $response = $depIndicator->getResponseForCycle($cycleId);
                if ($response && $response->value_numeric !== null) {
                    $values[$depCodeNum] = (float) $response->value_numeric;
                }
            }
        }

        // Проверяем, все ли значения получены
        if (count($values) !== count($dependsOn)) {
            return null;
        }

        // Заменяем коды индикаторов в формуле на значения
        $formula = $this->formula;

        // Сортируем по убыванию длины, чтобы сначала заменять более длинные ключи
        krsort($values);

        foreach ($values as $code => $value) {
            // Заменяем "X.Y" где Y — code_in_category
            $formula = preg_replace('/\b' . $this->category_id . '\.' . $code . '\b/', (string) $value, $formula);
            // Также заменяем просто code_in_category
            $formula = str_replace((string) $code, (string) $value, $formula);
        }

        // Вычисляем формулу безопасно
        try {
            $result = eval('return ' . $formula . ';');
            return is_numeric($result) ? (float) $result : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
