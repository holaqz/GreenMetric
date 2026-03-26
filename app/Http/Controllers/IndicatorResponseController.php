<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\IndicatorResponse;
use App\Models\EvidenceFile;
use App\Models\ChangeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IndicatorResponseController extends Controller
{
    /**
     * Обновить ответ на индикатор
     */
    public function update(Request $request, IndicatorResponse $response)
    {
        $user = Auth::user();
        $indicator = $response->indicator;
        
        // Проверяем доступ к категории
        $categoryCode = $indicator->category->code;
        if (!$user || !$user->hasCategoryAccess($categoryCode)) {
            abort(403, 'Нет доступа к этой категории');
        }
        
        // Проверяем, меняется ли статус
        $newStatus = $request->input('status');
        if ($newStatus && $newStatus !== $response->status) {
            // Только админ может менять статус
            if (!$user->isAdmin()) {
                abort(403, 'Только администратор может менять статус индикатора');
            }
        }
        
        // Для вычисляемых полей можно обновить только description и status
        if ($indicator->is_computed) {
            $rules = $this->getValidationRules($indicator);
            $validated = $request->validate($rules);

            // Разрешаем обновлять только program_description и status
            $allowedFields = ['program_description', 'status'];
            $filteredData = array_filter($validated, function($key) use ($allowedFields) {
                return in_array($key, $allowedFields);
            }, ARRAY_FILTER_USE_KEY);

            if (empty($filteredData)) {
                return back()->with('error', 'Для вычисляемых полей можно обновить только описание и статус');
            }

            $this->logChanges($response, $filteredData);

            $response->update([
                ...$filteredData,
                'updated_by' => Auth::id(),
            ]);

            return back()->with('success', 'Данные сохранены');
        }

        $rules = $this->getValidationRules($indicator);

        $validated = $request->validate($rules);

        // Логирование изменений
        $this->logChanges($response, $validated);

        $response->update([
            ...$validated,
            'updated_by' => Auth::id(),
        ]);

        // После обновления проверяем, нужно ли пересчитать вычисляемые поля
        $this->recomputeDependentIndicators($response);

        return back()->with('success', 'Данные сохранены');
    }

    /**
     * Пересчитать вычисляемые индикаторы, которые зависят от текущего
     */
    private function recomputeDependentIndicators(IndicatorResponse $response)
    {
        $cycleId = $response->cycle_id;
        $category = $response->indicator->category;
        
        // Находим все вычисляемые индикаторы в этой категории
        $computedIndicators = Indicator::where('category_id', $category->id)
            ->where('input_type', 'computed')
            ->get();
        
        foreach ($computedIndicators as $indicator) {
            $dependsOn = $indicator->depends_on;
            
            // Проверяем, зависит ли этот индикатор от обновленного
            if (in_array($response->indicator->code_in_category, $dependsOn)) {
                // Вычисляем значение
                $computedValue = $indicator->computeValue($cycleId);
                
                if ($computedValue !== null) {
                    // Находим или создаем ответ для вычисляемого индикатора
                    $computedResponse = IndicatorResponse::firstOrCreate(
                        [
                            'cycle_id' => $cycleId,
                            'indicator_id' => $indicator->id,
                        ],
                        [
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'status' => 'in_progress',
                        ]
                    );
                    
                    // Определяем, какой вариант ответа выбрать на основе вычисленного значения
                    $selectedOption = $this->determineOptionForComputedValue($indicator, $computedValue);
                    
                    $computedResponse->update([
                        'value_numeric' => $computedValue,
                        'selected_option' => $selectedOption,
                        'updated_by' => Auth::id(),
                    ]);
                }
            }
        }
    }

    /**
     * Определить вариант ответа на основе вычисленного значения
     */
    private function determineOptionForComputedValue($indicator, float $value): ?int
    {
        $options = $indicator->options;

        if (empty($options)) {
            return null;
        }

        // Парсим варианты ответов и находим подходящий
        foreach ($options as $index => $option) {
            $optionNum = $index + 1;

            // Очищаем опцию от лишних символов для парсинга
            $optionClean = trim($option);

            // Формат: "≤ 1%", "≤ 10" (меньше или равно)
            if (preg_match('/^≤\s*([\d.]+)\s*%?$/', $optionClean, $matches)) {
                if ($value <= floatval($matches[1])) {
                    return $optionNum;
                }
            }
            // Формат: "< 0.5", "< 1%" (меньше)
            elseif (preg_match('/^<\s*([\d.]+)\s*%?$/', $optionClean, $matches)) {
                if ($value < floatval($matches[1])) {
                    return $optionNum;
                }
            }
            // Формат: "≥ 2400", "≥ 2.05" (больше или равно)
            elseif (preg_match('/^≥\s*([\d.]+)\s*%?$/', $optionClean, $matches)) {
                if ($value >= floatval($matches[1])) {
                    return $optionNum;
                }
            }
            // Формат: "> 75%", "> 0.02" (больше)
            elseif (preg_match('/^>\s*([\d.]+)\s*%?$/', $optionClean, $matches)) {
                if ($value > floatval($matches[1])) {
                    return $optionNum;
                }
            }
            // Формат: "1% - 25%", "10 - 20", "0.5 - 1" (диапазон: от X до Y включительно)
            elseif (preg_match('/^([\d.]+)\s*%?\s*-\s*([\d.]+)\s*%?$/', $optionClean, $matches)) {
                $min = floatval($matches[1]);
                $max = floatval($matches[2]);
                if ($value >= $min && $value <= $max) {
                    return $optionNum;
                }
            }
            // Формат: "0" (точное совпадение)
            elseif (preg_match('/^0$/', $optionClean)) {
                if (round($value) == 0) {
                    return $optionNum;
                }
            }
        }

        return null;
    }

    /**
     * Загрузить файл доказательства
     */
    public function uploadFile(Request $request, IndicatorResponse $response)
    {
        $user = Auth::user();
        
        // Проверяем доступ к категории
        $categoryCode = $response->indicator->category->code;
        if (!$user || !$user->hasCategoryAccess($categoryCode)) {
            abort(403, 'Нет доступа к этой категории');
        }
        
        // Проверяем статус (не админ не может редактировать approved/ready_for_review)
        if (!$user->isAdmin() && in_array($response->status, ['approved', 'ready_for_review'])) {
            return back()->withErrors(['file' => 'Нельзя редактировать индикатор со статусом "' . $response->status . '"']);
        }
        
        // Проверяем количество загруженных файлов (максимум 4)
        $currentFileCount = $response->files()->where('file_type', '!=', 'link')->count();

        if ($currentFileCount >= 4) {
            return back()->withErrors(['file' => 'Можно загрузить не больше 4 фотографий.']);
        }
        
        $validated = $request->validate([
            // По новым требованиям принимаем только изображения-доказательства.
            'file' => 'required|image|mimes:jpg,jpeg,png|max:10240', // 10MB
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('evidence/' . $response->cycle_id, 'private');

        EvidenceFile::create([
            'response_id' => $response->id,
            'uploaded_by' => Auth::id(),
            'file_name_original' => $file->getClientOriginalName(),
            'file_path_storage' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'description' => $validated['description'] ?? null,
            'file_size_bytes' => $file->getSize(),
        ]);

        return back()->with('success', 'Файл загружен');
    }

    /**
     * Добавить ссылку как доказательство
     */
    public function addLink(Request $request, IndicatorResponse $response)
    {
        $user = Auth::user();
        
        // Проверяем доступ к категории
        $categoryCode = $response->indicator->category->code;
        if (!$user || !$user->hasCategoryAccess($categoryCode)) {
            abort(403, 'Нет доступа к этой категории');
        }
        
        // Проверяем статус (не админ не может редактировать approved/ready_for_review)
        if (!$user->isAdmin() && in_array($response->status, ['approved', 'ready_for_review'])) {
            return back()->withErrors(['url' => 'Нельзя редактировать индикатор со статусом "' . $response->status . '"']);
        }
        
        // Проверяем количество ссылок (максимум 10)
        $currentLinkCount = $response->files()->where('file_type', 'link')->count();
        if ($currentLinkCount >= 10) {
            return back()->withErrors(['url' => 'Можно добавить не больше 10 ссылок.']);
        }
        
        $validated = $request->validate([
            'url' => 'required|url',
            'description' => 'nullable|string|max:255',
        ]);

        EvidenceFile::create([
            'response_id' => $response->id,
            'uploaded_by' => Auth::id(),
            'file_name_original' => parse_url($validated['url'], PHP_URL_HOST),
            'file_path_storage' => '',
            'file_type' => 'link',
            'external_url' => $validated['url'],
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Ссылка добавлена');
    }

    /**
     * Удалить файл доказательства
     */
    public function deleteFile(EvidenceFile $file)
    {
        $user = Auth::user();
        $response = $file->response;
        $indicator = $response->indicator;
        
        // Проверяем доступ к категории
        $categoryCode = $indicator->category->code;
        if (!$user || !$user->hasCategoryAccess($categoryCode)) {
            abort(403, 'Нет доступа к этой категории');
        }
        
        // Проверяем статус (не админ не может удалять из approved/ready_for_review)
        if (!$user->isAdmin() && in_array($response->status, ['approved', 'ready_for_review'])) {
            return back()->withErrors(['file' => 'Нельзя удалять файлы из индикатора со статусом "' . $response->status . '"']);
        }
        
        $responseId = $file->response_id;

        if ($file->file_type !== 'link') {
            Storage::disk('private')->delete($file->file_path_storage);
        }

        $file->delete();

        return back()->with('success', 'Файл удалён');
    }

    /**
     * Получить историю изменений
     */
    public function history(IndicatorResponse $response)
    {
        $logs = $response->changeLogs()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }

    /**
     * Получить правила валидации для индикатора
     */
    private function getValidationRules($indicator): array
    {
        $rules = [
            'program_description' => 'nullable|string|max:5000',
            'status' => 'nullable|in:in_progress,ready_for_review,approved',
        ];

        $validationRules = $indicator->validation_rules ?? [];

        return match ($indicator->input_type) {
            'number' => [
                ...$rules,
                'value_numeric' => 'nullable|numeric|min:' . ($validationRules['min'] ?? 0),
            ],
            'boolean' => [
                ...$rules,
                'value_boolean' => 'nullable|boolean',
            ],
            'select', 'select_with_area' => [
                ...$rules,
                'selected_option' => 'nullable|integer|min:1|max:' . count($validationRules['options'] ?? []),
                'value_numeric' => $indicator->input_type === 'select_with_area'
                    ? 'nullable|numeric|min:0'
                    : 'nullable|numeric',
            ],
            'text' => [
                ...$rules,
                'value_text' => 'nullable|string|max:255',
            ],
            'computed' => [
                ...$rules,
                // Для вычисляемых индикаторов сохраняем только description и status
            ],
            default => $rules,
        };
    }

    /**
     * Логирование изменений
     */
    private function logChanges(IndicatorResponse $response, array $newData): void
    {
        $changes = [];
        $fields = ['value_numeric', 'value_text', 'value_boolean', 'program_description', 'status'];

        foreach ($fields as $field) {
            if (array_key_exists($field, $newData)) {
                $oldValue = $response->getOriginal($field);
                $newValue = $newData[$field];

                if ($oldValue !== $newValue) {
                    ChangeLog::create([
                        'user_id' => Auth::id(),
                        'response_id' => $response->id,
                        'field_changed' => $field,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                    ]);
                }
            }
        }
    }
}
