<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Indicator;
use App\Models\IndicatorResponse;
use App\Models\Category;
use App\Services\GreenMetricTemplateExporter;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class IndicatorExportController extends Controller
{
    /**
     * Экспорт одного индикатора в формате Word
     * Генерирует отдельный файл для каждого индикатора
     */
    public function exportIndicator(Cycle $cycle, int $indicatorId)
    {
        try {
            $indicator = Indicator::with('category')->findOrFail($indicatorId);
            $response = IndicatorResponse::where('cycle_id', $cycle->id)
                ->where('indicator_id', $indicator->id)
                ->with(['indicator.category', 'files'])
                ->first();

            if (!$response) {
                abort(404, 'Данные для этого индикатора ещё не заполнены');
            }

            $profile = $cycle->latestProfile;
            $exporter = app(GreenMetricTemplateExporter::class);
            $templatePath = $exporter->resolveTemplatePath();
            
            \Log::info('Export indicator', [
                'indicator_id' => $indicatorId,
                'cycle_id' => $cycle->id,
                'template_path' => $templatePath,
                'response_id' => $response->id,
            ]);
            
            if (!$templatePath) {
                \Log::error('Template not found');
                abort(500, 'Не найден master_template.docx');
            }

            $filename = $exporter->buildIndicatorFileName($cycle, $indicator);
            $content = $exporter->buildIndicatorDocxBytes($templatePath, $cycle, $response, $profile);

            return Response::make($content, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => "attachment; filename=\"{$filename}.docx\"",
            ]);
        } catch (\Exception $e) {
            \Log::error('Export error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    /**
     * Получить данные для вычисляемых индикаторов, от которых зависит этот
     */
    private function getComputedDependencies(Cycle $cycle, Indicator $indicator): array
    {
        $dependencies = [];

        // Находим все вычисляемые индикаторы в этой категории
        $computedIndicators = Indicator::with('category')
            ->where('category_id', $indicator->category_id)
            ->where('input_type', 'computed')
            ->get();

        foreach ($computedIndicators as $computed) {
            $dependsOn = $computed->depends_on ?? ($computed->validation_rules['depends_on'] ?? []);
            
            // Проверяем, зависит ли вычисляемый индикатор от текущего
            if (in_array($indicator->code_in_category, $dependsOn)) {
                $computedResponse = IndicatorResponse::where('cycle_id', $cycle->id)
                    ->where('indicator_id', $computed->id)
                    ->first();

                if ($computedResponse) {
                    $dependencies[] = [
                        'code' => $computed->category->code . '.' . $computed->code_in_category,
                        'question' => $computed->question_text,
                        'value' => $computedResponse->value_numeric,
                        'formula' => $computed->formula ?? ($computed->validation_rules['formula'] ?? null),
                    ];
                }
            }
        }

        return $dependencies;
    }

    /**
     * Сгенерировать имя файла
     * Пример: GreenMetric_2026_TR_16_Pedestrian_Pathways
     */
    private function generateFilename(Cycle $cycle, Indicator $indicator): string
    {
        return "GreenMetric_{$cycle->year}_{$indicator->category->code}_{$indicator->code_in_category}_{$indicator->filename_slug}";
    }

    /**
     * Построение документа Word
     */
    private function buildDocument(PhpWord $phpWord, Cycle $cycle, Indicator $indicator, IndicatorResponse $response, array $computedDependencies): void
    {
        $section = $phpWord->addSection([
            'marginLeft' => 1440,
            'marginRight' => 1440,
            'marginTop' => 1440,
            'marginBottom' => 1440,
        ]);

        // === Шапка ===
        $this->addHeader($section, $cycle);

        // === Заголовок индикатора ===
        $section->addTextBreak(2);
        $section->addText(
            "Индикатор {$indicator->category->code}.{$indicator->code_in_category}",
            ['bold' => true, 'size' => 20, 'color' => '2E7D32', 'spaceAfter' => 100]
        );
        $section->addText(
            "Категория: {$indicator->category->name}",
            ['size' => 14, 'color' => '1565C0', 'spaceAfter' => 100]
        );
        $section->addText($indicator->question_text, ['size' => 12, 'spaceAfter' => 200]);

        // === Числовые данные ===
        $section->addText('ЧИСЛОВЫЕ ДАННЫЕ', ['bold' => true, 'size' => 16, 'spaceAfter' => 100]);

        $table = $section->addTable(['borderSize' => 6, 'cellMargin' => 80, 'width' => 10000]);
        $table->addRow();
        $table->addCell(4000)->addText('Параметр', ['bold' => true]);
        $table->addCell(6000)->addText('Значение', ['bold' => true]);

        $table->addRow();
        $table->addCell(4000)->addText('Единица измерения');
        $table->addCell(6000)->addText($indicator->unit ?? '—');

        $table->addRow();
        $table->addCell(4000)->addText('Тип ввода');
        $table->addCell(6000)->addText($this->getInputTypeText($indicator->input_type));

        $table->addRow();
        $table->addCell(4000)->addText('Значение');
        $valueCell = $table->addCell(6000);
        
        if ($indicator->input_type === 'computed') {
            $valueCell->addText($response->value_numeric ?? 'Вычисляется', ['color' => '0066CC', 'bold' => true]);
        } elseif ($indicator->input_type === 'select') {
            $valueCell->addText($response->selected_option_text ?? 'Не выбрано');
        } else {
            $valueCell->addText($response->value_numeric ?? $response->value_text ?? 'Не заполнено');
        }

        // === Вычисляемые зависимости ===
        if (count($computedDependencies) > 0) {
            $section->addTextBreak(2);
            $section->addText('ВЫЧИСЛЯЕМЫЕ ЗАВИСИМОСТИ', ['bold' => true, 'size' => 16, 'spaceAfter' => 100]);
            $section->addText('Этот индикатор используется для расчёта следующих показателей:', ['size' => 11, 'italic' => true, 'spaceAfter' => 100]);

            foreach ($computedDependencies as $dep) {
                $section->addText("{$dep['code']}: {$dep['question']}", ['bold' => true, 'size' => 12]);
                $section->addText("Значение: {$dep['value']}", ['size' => 11]);
                if ($dep['formula']) {
                    $section->addText("Формула: {$dep['formula']}", ['size' => 10, 'italic' => true]);
                }
                $section->addTextBreak(1);
            }
        }

        // === Описание программы ===
        $section->addTextBreak(2);
        $section->addText('ОПИСАНИЕ ПРОГРАММЫ', ['bold' => true, 'size' => 16, 'spaceAfter' => 100]);

        if ($response->program_description) {
            $section->addText($response->program_description, ['size' => 11, 'lineHeight' => 1.5]);
        } else {
            $section->addText('[Описание программы не заполнено]', ['size' => 11, 'italic' => true, 'color' => '999999']);
        }

        // === Прикреплённые файлы ===
        $section->addTextBreak(2);
        $section->addText('ПРИКРЕПЛЁННЫЕ ФАЙЛЫ', ['bold' => true, 'size' => 16, 'spaceAfter' => 100]);

        if ($response->files->count() > 0) {
            foreach ($response->files as $file) {
                $icon = $file->file_type === 'link' ? '[URL] ' : '[FILE] ';
                $section->addText("{$icon}{$file->file_name_original}", ['size' => 11]);
                if ($file->description) {
                    $section->addText("  → {$file->description}", ['size' => 10, 'italic' => true, 'color' => '666666']);
                }
            }
        } else {
            $section->addText('Файлы не прикреплены', ['size' => 11, 'italic' => true, 'color' => '999999']);
        }

        // === Пояснение ===
        if ($indicator->description_help) {
            $section->addTextBreak(2);
            $section->addText('ПОЯСНЕНИЕ', ['bold' => true, 'size' => 14, 'spaceAfter' => 50]);
            $section->addText($indicator->description_help, ['size' => 10, 'italic' => true, 'color' => '666666']);
        }
    }

    /**
     * Добавить шапку с логотипами
     */
    private function addHeader($section, Cycle $cycle)
    {
        $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 0, 'width' => 10000]);
        $table->addRow();
        
        // Логотип UI GreenMetric (слева)
        $logoPath = public_path('images/greenmetric-logo.png');
        if (file_exists($logoPath)) {
            $table->addCell(3000)->addImage($logoPath, [
                'width' => 120,
                'height' => 60,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START,
            ]);
        } else {
            $table->addCell(3000)->addText('UI GreenMetric', ['bold' => true, 'size' => 12, 'color' => '2E7D32']);
        }

        // Название университета (центр)
        $table->addCell(4000)->addText("Название Университета", ['bold' => true, 'size' => 18, 'align' => 'center', 'color' => '1565C0']);

        // Логотип университета (справа)
        $uniLogoPath = public_path('images/university-logo.png');
        if (file_exists($uniLogoPath)) {
            $table->addCell(3000)->addImage($uniLogoPath, [
                'width' => 120,
                'height' => 60,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END,
            ]);
        } else {
            $table->addCell(3000)->addText('Университет', ['bold' => true, 'size' => 12, 'color' => '1565C0', 'align' => 'right']);
        }

        // Информация под логотипами
        $section->addTextBreak();
        $infoTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 50, 'width' => 10000]);
        $infoTable->addRow();
        $infoTable->addCell(3333)->addText("Сайт: www.university.edu", ['size' => 10, 'color' => '666666']);
        $infoTable->addCell(3333)->addText("Страна: Страна", ['size' => 10, 'color' => '666666', 'align' => 'center']);
        $infoTable->addCell(3333)->addText("Цикл: {$cycle->year}", ['size' => 10, 'color' => '666666', 'align' => 'right']);
    }

    /**
     * Получить текст типа ввода
     */
    private function getInputTypeText(string $inputType): string
    {
        $types = [
            'number' => 'Числовое поле',
            'text' => 'Текстовое поле',
            'select' => 'Выпадающий список',
            'computed' => 'Вычисляемое автоматически',
            'boolean' => 'Да/Нет',
        ];
        
        return $types[$inputType] ?? $inputType;
    }
}
