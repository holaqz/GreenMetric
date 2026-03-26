<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Indicator;
use App\Models\IndicatorResponse;
use App\Models\UniversityProfile;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CycleController extends Controller
{
    /**
     * Список всех циклов
     */
    public function index()
    {
        $cycles = Cycle::with(['latestProfile'])
            ->orderBy('year', 'desc')
            ->get()
            ->map(fn($cycle) => [
                'id' => $cycle->id,
                'year' => $cycle->year,
                'status' => $cycle->status,
                'data_period_start' => $cycle->data_period_start,
                'data_period_end' => $cycle->data_period_end,
                'submission_start' => $cycle->submission_start,
                'submission_end' => $cycle->submission_end,
                'completion_percentage' => $this->getCycleCompletionPercentage($cycle),
            ]);

        return Inertia::render('cycles/Index', [
            'cycles' => $cycles,
            'categories' => Category::orderBy('order')->get(),
        ]);
    }

    /**
     * Получить процент заполнения цикла
     */
    private function getCycleCompletionPercentage(Cycle $cycle): float
    {
        $total = Indicator::count();
        if ($total === 0) {
            return 0;
        }

        $completed = IndicatorResponse::where('cycle_id', $cycle->id)
            ->where('status', 'approved')
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Показать цикл с данными по категории
     */
    public function show(Cycle $cycle, Request $request)
    {
        $user = Auth::user();
        $categoryCode = $request->query('category', 'WR');
        
        // Получаем список доступных пользователю категорий
        $accessibleCategories = $user ? $user->getAccessibleCategoryCodes() : [];

        $indicators = Indicator::whereHas('category', function ($query) use ($categoryCode) {
            $query->where('code', $categoryCode);
        })
        ->with(['category', 'responses' => function ($query) use ($cycle) {
            $query->where('cycle_id', $cycle->id)
                ->with('files');
        }])
        ->orderBy('order')
        ->get()
        ->map(function ($indicator) use ($cycle, $accessibleCategories, $categoryCode) {
            $response = $indicator->responses->first();
            
            // Может ли пользователь редактировать эту категорию
            $canEditCategory = in_array($categoryCode, $accessibleCategories);

            return [
                'id' => $indicator->id,
                'category_id' => $indicator->category_id,
                'category_code' => $indicator->category->code,
                'category_name' => $indicator->category->name,
                'code_in_category' => $indicator->code_in_category,
                'code_full' => $indicator->category->code . '.' . $indicator->code_in_category,
                'question_text' => $indicator->question_text,
                'unit' => $indicator->unit,
                'input_type' => $indicator->input_type,
                'filename_slug' => $indicator->filename_slug,
                'description_help' => $indicator->description_help,
                'options' => $indicator->options,
                'has_area_input' => $indicator->has_area_input,
                'area_unit' => $indicator->area_unit,
                'is_computed' => $indicator->is_computed,
                'formula' => $indicator->formula ?? ($indicator->validation_rules['formula'] ?? null),
                'depends_on' => $indicator->depends_on ?? ($indicator->validation_rules['depends_on'] ?? []),
                'computed_value' => $indicator->is_computed ? $indicator->computeValue($cycle->id) : null,
                'can_edit_category' => $canEditCategory,
                'response' => $response ? [
                    'id' => $response->id,
                    'selected_option' => $response->selected_option,
                    'selected_option_text' => $response->selected_option_text,
                    'value_numeric' => $response->value_numeric,
                    'value_text' => $response->value_text,
                    'value_boolean' => $response->value_boolean,
                    'formatted_value' => $response->formatted_value,
                    'program_description' => $response->program_description,
                    'status' => $response->status,
                    'files' => $response->files->map(fn($f) => [
                        'id' => $f->id,
                        'file_name_original' => $f->file_name_original,
                        'file_type' => $f->file_type,
                        'file_size_bytes' => $f->file_size_bytes,
                        'description' => $f->description,
                        'is_link' => $f->is_link,
                        'external_url' => $f->external_url,
                        'download_url' => route('files.download', $f->id),
                    ]),
                ] : null,
            ];
        });

        $profile = $cycle->latestProfile;
        $categories = Category::orderBy('order')->get();
        
        // Процент заполнения по каждой категории
        $categoryCompletion = [];
        foreach ($categories as $category) {
            $categoryCompletion[$category->code] = $this->getCategoryCompletionPercentage($cycle, $category);
        }

        return Inertia::render('cycles/Show', [
            'cycle' => $cycle,
            'profile' => $profile,
            'category_code' => $categoryCode,
            'indicators' => $indicators,
            'categories' => $categories,
            'category_completion' => $categoryCompletion,
        ]);
    }

    /**
     * Получить процент заполнения категории
     */
    private function getCategoryCompletionPercentage(Cycle $cycle, Category $category): float
    {
        $total = $category->indicators()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $category->indicators()
            ->whereHas('responses', function ($query) use ($cycle) {
                $query->where('cycle_id', $cycle->id)
                    ->where('status', 'approved');
            })
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Создать новый цикл
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|unique:cycles,year',
            'data_period_start' => 'required|date',
            'data_period_end' => 'required|date|after:data_period_start',
            'submission_start' => 'nullable|date',
            'submission_end' => 'nullable|date|after:submission_start',
        ]);

        $cycle = Cycle::create([
            ...$validated,
            'status' => 'draft',
        ]);

        // Создаём пустой профиль для цикла
        UniversityProfile::create([
            'cycle_id' => $cycle->id,
            'created_by' => Auth::id(),
        ]);

        // Создаём пустые ответы для всех индикаторов
        $indicators = Indicator::all();
        foreach ($indicators as $indicator) {
            IndicatorResponse::create([
                'cycle_id' => $cycle->id,
                'indicator_id' => $indicator->id,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'status' => 'in_progress',
            ]);
        }

        // Редирект на страницу редактирования цикла (категория WR по умолчанию)
        return redirect()->route('cycles.show', ['cycle' => $cycle, 'category' => 'WR'])
            ->with('success', "Цикл {$cycle->year} создан");
    }

    /**
     * Обновить статус цикла
     */
    public function updateStatus(Request $request, Cycle $cycle)
    {
        $user = Auth::user();
        
        // Только админ может менять статус цикла
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Только администратор может менять статус цикла');
        }
        
        $validated = $request->validate([
            'status' => 'required|in:draft,open,closed,submitted',
        ]);

        $cycle->update($validated);

        return redirect()->back()->with('success', "Статус цикла обновлён на '{$validated['status']}'");
    }
}
