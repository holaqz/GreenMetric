<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndicatorResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'cycle_id',
        'indicator_id',
        'created_by',
        'updated_by',
        'selected_option',
        'value_numeric',
        'value_text',
        'value_boolean',
        'program_description',
        'status',
    ];

    protected $casts = [
        'selected_option' => 'integer',
        'value_numeric' => 'decimal:2',
        'value_boolean' => 'boolean',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function files(): HasMany
    {
        return $this->hasMany(EvidenceFile::class, 'response_id');
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(ChangeLog::class);
    }

    /**
     * Получить текст выбранного варианта ответа
     */
    public function getSelectedOptionTextAttribute(): ?string
    {
        if (!$this->selected_option || !$this->indicator->validation_rules) {
            return null;
        }

        $options = $this->indicator->validation_rules['options'] ?? [];
        $index = $this->selected_option - 1;

        return $options[$index] ?? null;
    }

    /**
     * Получить отформатированное значение в зависимости от типа индикатора
     */
    public function getFormattedValueAttribute(): string|int|float|null
    {
        return match ($this->indicator->input_type) {
            'number' => $this->value_numeric,
            'boolean' => $this->value_boolean ? 'Да' : 'Нет',
            'select', 'select_with_area' => $this->selected_option_text ?? "Вариант {$this->selected_option}",
            'text' => $this->value_text,
            default => $this->value_numeric ?? $this->value_text,
        };
    }
}
