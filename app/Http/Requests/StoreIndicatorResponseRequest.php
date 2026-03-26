<?php

namespace App\Http\Requests;

use App\Models\Indicator;
use Illuminate\Foundation\Http\FormRequest;

class StoreIndicatorResponseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $indicator = $this->route('indicator');
        
        if (!$indicator) {
            return [];
        }

        return $this->getIndicatorRules($indicator);
    }

    /**
     * Получить правила валидации в зависимости от типа индикатора
     */
    private function getIndicatorRules(Indicator $indicator): array
    {
        $rules = [
            'program_description' => 'nullable|string|max:5000',
            'status' => 'nullable|in:in_progress,ready_for_review,approved',
        ];

        $validationRules = $indicator->validation_rules ?? [];

        return match ($indicator->input_type) {
            'number' => [
                ...$rules,
                'value_numeric' => 'required|numeric|min:' . ($validationRules['min'] ?? 0),
            ],
            'boolean' => [
                ...$rules,
                'value_boolean' => 'required|boolean',
            ],
            'select', 'select_with_area' => [
                ...$rules,
                'selected_option' => 'required|integer|min:1|max:' . count($validationRules['options'] ?? []),
                'value_numeric' => $indicator->input_type === 'select_with_area' 
                    ? 'required|numeric|min:0' 
                    : 'nullable|numeric',
            ],
            'text' => [
                ...$rules,
                'value_text' => 'required|string|max:255',
            ],
            'file_only' => [
                ...$rules,
                // Файлы загружаются отдельно
            ],
            default => $rules,
        };
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $indicator = $this->route('indicator');
        
        if (!$indicator) {
            return [];
        }

        return [
            'selected_option.required' => 'Выберите вариант ответа',
            'selected_option.max' => 'Неверный вариант ответа',
            'value_numeric.required' => 'Укажите числовое значение',
            'value_numeric.min' => 'Значение не может быть отрицательным',
        ];
    }
}
