<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Category;
use App\Models\IndicatorResponse;
use App\Services\GreenMetricTemplateExporter;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Экспорт отчёта по категории в формате Word (DOCX)
     * Генерирует HTML файл, который Word открывает как документ
     */
    public function exportWord(Cycle $cycle, string $categoryCode)
    {
        try {
            $category = Category::where('code', $categoryCode)->firstOrFail();

            $data = $cycle->responses()
                ->with(['indicator.category', 'files'])
                ->whereHas('indicator', function ($query) use ($categoryCode) {
                    $query->whereHas('category', function ($q) use ($categoryCode) {
                        $q->where('code', $categoryCode);
                    });
                })
                ->orderByRaw(
                    '(SELECT "order" FROM indicators WHERE indicators.id = indicator_responses.indicator_id)'
                )
                ->get();

            $zipFilename = $this->generateWordFilename($cycle, $category) . '_reports.zip';

            $exporter = app(GreenMetricTemplateExporter::class);
            $templatePath = $exporter->resolveTemplatePath();
            if (!$templatePath) {
                Log::error('Не найден master_template.docx');
                abort(500, 'Не найден шаблон документа (master_template.docx)');
            }

            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $tempZip = $tempDir . DIRECTORY_SEPARATOR . 'category_export_' . $cycle->id . '_' . $category->code . '_' . time() . '.zip';
            $zip = new \ZipArchive();
            if ($zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                Log::error('Не удалось создать ZIP архив: ' . $tempZip);
                abort(500, 'Не удалось создать архив отчётов');
            }

            if ($data->isEmpty()) {
                $zip->addFromString('README.txt', 'Для выбранной категории нет данных для экспорта.');
            } else {
                foreach ($data as $response) {
                    $indicator = $response->indicator;
                    if (!$indicator) {
                        continue;
                    }

                    try {
                        $docBytes = $exporter->buildIndicatorDocxBytes($templatePath, $cycle, $response);
                        $docName = $exporter->buildIndicatorFileName($cycle, $indicator) . '.docx';
                        $zip->addFromString($docName, $docBytes);
                    } catch (\Exception $e) {
                        Log::error('Ошибка генерации документа для индикатора ' . $indicator->code_in_category . ': ' . $e->getMessage());
                        // Добавляем файл с ошибкой вместо документа
                        $zip->addFromString(
                            "ERROR_{$indicator->code_in_category}.txt",
                            "Ошибка генерации документа: " . $e->getMessage()
                        );
                    }
                }
            }

            $zip->close();
            
            if (!file_exists($tempZip)) {
                abort(500, 'Не удалось создать файл отчёта');
            }

            $binary = file_get_contents($tempZip);
            @unlink($tempZip);

            return Response::make($binary, 200, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => "attachment; filename=\"{$zipFilename}\"",
                'Content-Length' => strlen($binary),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Ошибка экспорта отчёта: ' . $e->getMessage(), [
                'cycle' => $cycle->id,
                'category' => $categoryCode,
                'exception' => get_class($e),
            ]);
            abort(500, 'Произошла ошибка при генерации отчёта: ' . $e->getMessage());
        }
    }

    /**
     * Экспорт всех доказательств категории в ZIP
     */
    public function exportEvidenceZip(Cycle $cycle, string $categoryCode)
    {
        $category = Category::where('code', $categoryCode)->firstOrFail();

        $data = $cycle->responses()
            ->with(['indicator.category', 'files'])
            ->whereHas('indicator', function ($query) use ($categoryCode) {
                $query->whereHas('category', function ($q) use ($categoryCode) {
                    $q->where('code', $categoryCode);
                });
            })
            ->orderByRaw('(SELECT "order" FROM indicators WHERE indicators.id = indicator_responses.indicator_id)')
            ->get();

        // Собираем все файлы
        $files = [];
        foreach ($data as $response) {
            foreach ($response->files as $file) {
                if ($file->file_type !== 'link') {
                    $files[] = [
                        'file' => $file,
                        'indicator_code' => $response->indicator->category->code . $response->indicator->code_in_category,
                    ];
                }
            }
        }

        // Генерируем ZIP
        $zip = new \ZipArchive();
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $tempFile = $tempDir . DIRECTORY_SEPARATOR . 'evidence_' . $categoryCode . '_' . $cycle->year . '_' . time() . '.zip';

        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Не удалось создать ZIP архив');
        }

        if (empty($files)) {
            $zip->addFromString(
                'README.txt',
                "Для категории {$category->code} ({$category->name}) в цикле {$cycle->year} нет загруженных файлов-доказательств."
            );
        }

        foreach ($files as $entry) {
            $file = $entry['file'];
            $filePath = storage_path('app/private/' . $file->file_path_storage);
            if (file_exists($filePath)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                    continue;
                }
                $safeName = preg_replace('/[^\pL\pN\.\-_ ]/u', '_', $file->file_name_original);
                $zipName = "{$entry['indicator_code']}_{$safeName}";
                $zip->addFile($filePath, $zipName);
            }
        }

        $zip->close();

        // Проверяем, что файл существует
        if (!file_exists($tempFile)) {
            abort(500, 'Не удалось создать ZIP файл');
        }

        return Response::download($tempFile, 'evidence_' . $categoryCode . '_' . $cycle->year . '.zip')->deleteFileAfterSend();
    }

    /**
     * Сгенерировать имя файла Word
     * Пример: GreenMetric_2026_WR_Water.doc
     */
    private function generateWordFilename(Cycle $cycle, Category $category): string
    {
        $safeCategoryName = preg_replace('/[^A-Za-z0-9_]/', '_', $category->name);
        return "GreenMetric_{$cycle->year}_{$category->code}_{$safeCategoryName}";
    }

    /**
     * Экспорт DOCX на базе внешнего размеченного шаблона.
     */
    private function exportWordFromTemplate(string $templatePath, Cycle $cycle, Category $category, $data, $profile, string $filename)
    {
        $template = new TemplateProcessor($templatePath);

        // Базовые переменные профиля (как в шаблоне заказчика).
        $template->setValue('total_area_hectares', (string) ($profile->total_area_hectares ?? ''));
        $template->setValue('green_area_percent', (string) ($profile->green_area_percent ?? ''));
        $template->setValue('total_buildings', (string) ($profile->total_buildings ?? ''));
        $template->setValue('total_students', (string) ($profile->total_students ?? ''));
        $template->setValue('total_staff', (string) ($profile->total_staff ?? ''));
        $template->setValue('total_researchers', (string) ($profile->total_researchers ?? ''));

        // Доп. общие данные.
        $template->setValue('year', (string) $cycle->year);
        $template->setValue('category_code', (string) $category->code);
        $template->setValue('category_name', (string) $category->name);

        $units = [];
        $evidenceLinks = [];
        $photos = [];

        foreach ($data as $response) {
            if ($response->indicator && $response->indicator->unit) {
                $units[] = $response->indicator->unit;
            }

            foreach ($response->files as $file) {
                if ($file->file_type === 'link') {
                    if ($file->external_url) {
                        $evidenceLinks[] = $file->external_url;
                    }
                    continue;
                }

                $storagePath = storage_path('app/private/' . $file->file_path_storage);
                if (!file_exists($storagePath)) {
                    continue;
                }

                $ext = strtolower(pathinfo($storagePath, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                    $photos[] = $storagePath;
                }
            }
        }

        $template->setValue('unit', implode(', ', array_values(array_unique(array_filter($units)))));
        $template->setValue('evidence_links', implode("\n", $evidenceLinks));

        $normalizedPhotos = $this->normalizePhotosForDocx($photos);
        $this->injectTemplateImages($template, $normalizedPhotos);

        $tmpDocx = storage_path('app/temp/' . $filename . '_' . time() . '.docx');
        if (!is_dir(dirname($tmpDocx))) {
            mkdir(dirname($tmpDocx), 0755, true);
        }

        $template->saveAs($tmpDocx);
        $binary = file_get_contents($tmpDocx);
        @unlink($tmpDocx);
        foreach ($normalizedPhotos as $tmpImage) {
            if (str_contains($tmpImage, storage_path('app/temp'))) {
                @unlink($tmpImage);
            }
        }

        return Response::make($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => "attachment; filename=\"{$filename}.docx\"",
        ]);
    }

    /**
     * Определяем путь к "master_template.docx".
     */
    private function resolveMasterTemplatePath(): ?string
    {
        $candidates = [
            'C:\\Users\\fairs\\Downloads\\Telegram Desktop\\master_template.docx',
            storage_path('app/templates/master_template.docx'),
            base_path('master_template.docx'),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Динамическая вставка изображений:
     * - если есть плейсхолдеры вида photo_1..photo_N / image_1..image_N, заполняем их;
     * - если есть одиночный photo/image, ставим первое фото.
     */
    private function injectTemplateImages(TemplateProcessor $template, array $photos): void
    {
        if (empty($photos)) {
            return;
        }

        $vars = $template->getVariables();
        $slotVars = array_values(array_filter($vars, function ($v) {
            return preg_match('/^(photo|image)(_\\d+)?$/i', $v) === 1;
        }));

        if (empty($slotVars)) {
            return;
        }

        usort($slotVars, function ($a, $b) {
            $an = preg_match('/_(\\d+)$/', $a, $am) ? (int) $am[1] : 1;
            $bn = preg_match('/_(\\d+)$/', $b, $bm) ? (int) $bm[1] : 1;
            return $an <=> $bn;
        });

        $idx = 0;
        foreach ($slotVars as $slot) {
            if (!isset($photos[$idx])) {
                $template->setValue($slot, '');
                continue;
            }

            $template->setImageValue($slot, [
                'path' => $photos[$idx],
                'width' => 300,
                'height' => 200,
                'ratio' => true,
            ]);
            $idx++;
        }
    }

    /**
     * Подготовка фото под DOCX-рендереры:
     * - преобразуем в JPEG с белым фоном (без прозрачности),
     * - мягко ограничиваем размер.
     */
    private function normalizePhotosForDocx(array $photos): array
    {
        $result = [];
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        foreach ($photos as $path) {
            $raw = @file_get_contents($path);
            if ($raw === false) {
                continue;
            }

            $src = @imagecreatefromstring($raw);
            if (!$src) {
                $result[] = $path;
                continue;
            }

            $srcW = imagesx($src);
            $srcH = imagesy($src);

            $maxW = 1400;
            $maxH = 1000;
            $scale = min($maxW / max($srcW, 1), $maxH / max($srcH, 1), 1);
            $newW = max((int) floor($srcW * $scale), 1);
            $newH = max((int) floor($srcH * $scale), 1);

            $dst = imagecreatetruecolor($newW, $newH);
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $white);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

            $tmpPath = $tempDir . DIRECTORY_SEPARATOR . 'docx_photo_' . md5($path . microtime(true)) . '.jpg';
            imagejpeg($dst, $tmpPath, 88);

            imagedestroy($src);
            imagedestroy($dst);

            $result[] = $tmpPath;
        }

        return $result;
    }

    /**
     * Генерация содержимого Word документа
     */
    private function generateWordContent(Cycle $cycle, Category $category, $data): string
    {
        // Создаём простой HTML, который Word сможет открыть
        $html = $this->generateHtmlContent($cycle, $category, $data);

        return $html;
    }

    /**
     * Генерация HTML содержимого (Word может открывать HTML как DOCX)
     */
    private function generateHtmlContent(Cycle $cycle, Category $category, $data): string
    {
        $greenMetricLogo = $this->imageAsDataUri(public_path('images/greenmetric-logo.png'));
        $universityLogo = $this->imageAsDataUri(public_path('images/university-logo.png'));

        $html = '<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="UTF-8">
    <title>GreenMetric ' . $cycle->year . ' - ' . htmlspecialchars($category->name) . '</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #2E7D32; }
        h2 { color: #1565C0; margin-top: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #E8F5E9; font-weight: bold; }
        .info { background-color: #E3F2FD; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .section { margin: 20px 0; page-break-inside: avoid; }
        .header-table td { vertical-align: middle; }
        .logo { max-height: 80px; max-width: 180px; }
        .title-block { text-align: center; }
    </style>
</head>
<body>';

        // Header with logos
        $html .= '<table class="header-table" style="border: 0; width: 100%; margin-bottom: 12px;"><tr>';
        $html .= '<td style="border:0; width:25%; text-align:left;">';
        if ($greenMetricLogo) {
            $html .= $this->buildWordImageTag($greenMetricLogo, 'GreenMetric', 160);
        } else {
            $html .= '<strong>UI GreenMetric</strong>';
        }
        $html .= '</td>';
        $html .= '<td class="title-block" style="border:0; width:50%;">';
        $html .= '<div style="font-size: 18px; font-weight: bold; color:#1565C0;">University Name</div>';
        $html .= '<div style="font-size: 12px; color:#666;">Cycle: ' . $cycle->year . '</div>';
        $html .= '</td>';
        $html .= '<td style="border:0; width:25%; text-align:right;">';
        if ($universityLogo) {
            $html .= $this->buildWordImageTag($universityLogo, 'University', 160);
        } else {
            $html .= '<strong>University Logo</strong>';
        }
        $html .= '</td></tr></table>';

        // Title information
        $html .= '<h1>UI GreenMetric World University Rankings ' . $cycle->year . '</h1>';
        $html .= '<h2>Category: ' . htmlspecialchars($category->name) . ' (' . $category->code . ')</h2>';
        $html .= '<div class="info">';
        $html .= '<p><strong>Data Period:</strong> ' . $cycle->data_period_start->format('d.m.Y') . ' — ' . $cycle->data_period_end->format('d.m.Y') . '</p>';
        $html .= '<p><strong>Generated:</strong> ' . now()->format('d.m.Y H:i') . '</p>';
        $html .= '</div>';

        // Indicators table
        $html .= '<div class="section">';
        $html .= '<h3>Indicators</h3>';
        $html .= '<table>';
        $html .= '<thead><tr><th>No.</th><th>Question</th><th>Unit</th><th>Value</th><th>Files</th></tr></thead>';
        $html .= '<tbody>';

        foreach ($data as $response) {
            $indicator = $response->indicator;
            $html .= '<tr>';
            $html .= '<td>' . $indicator->code_in_category . '</td>';
            $html .= '<td>' . htmlspecialchars($indicator->question_text) . '</td>';
            $html .= '<td>' . htmlspecialchars($indicator->unit ?? '-') . '</td>';

            // Value
            if ($indicator->input_type === 'computed') {
                $computedValue = $indicator->computeValue($cycle->id);
                if ($computedValue !== null) {
                    $html .= '<td style="color: blue;">' . number_format($computedValue, 2) . '</td>';
                } else {
                    $html .= '<td style="color: orange;">Insufficient data</td>';
                }
            } elseif ($indicator->input_type === 'select') {
                $html .= '<td>' . htmlspecialchars($response->selected_option_text ?? 'Not selected') . '</td>';
            } else {
                $html .= '<td>' . htmlspecialchars($response->value_numeric ?? $response->value_text ?? 'Not filled') . '</td>';
            }

            // Files
            $files = [];
            foreach ($response->files as $file) {
                $files[] = $file->file_name_original;
            }
            $html .= '<td>' . (count($files) > 0 ? htmlspecialchars(implode(', ', $files)) : 'No files') . '</td>';

            $html .= '</tr>';
        }

        $html .= '</tbody></table></div>';

        // Programs and initiatives description
        $html .= '<div class="section">';
        $html .= '<h3>Programs and Initiatives Description</h3>';

        foreach ($data as $response) {
            $indicator = $response->indicator;
            $html .= '<div style="margin: 15px 0; padding: 10px; border-left: 3px solid #2E7D32;">';
            $html .= '<p><strong>' . $indicator->code_in_category . '. ' . htmlspecialchars($indicator->question_text) . '</strong></p>';
            
            // Description first
            if ($response->program_description) {
                $html .= '<p>' . nl2br(htmlspecialchars($response->program_description)) . '</p>';
            } else {
                $html .= '<p style="color: #999; font-style: italic;">[Enter program description here...]</p>';
            }
            
            // Links after description
            $links = [];
            foreach ($response->files as $file) {
                if ($file->file_type === 'link' && $file->external_url) {
                    $links[] = $file->external_url;
                }
            }
            if (!empty($links)) {
                $html .= '<p style="margin-top: 10px;"><strong>Evidence Links:</strong><br>' . htmlspecialchars(implode('<br>', $links)) . '</p>';
            }
            
            $html .= '</div>';
        }

        $html .= '</div>';

        // Instructions
        $html .= '<div class="section">';
        $html .= '<h3>Instructions</h3>';
        $html .= '<ol>';
        $html .= '<li>Review all numerical values in the table above</li>';
        $html .= '<li>For each indicator, fill in the program description (if not already filled)</li>';
        $html .= '<li>Ensure all evidence files are attached</li>';
        $html .= '<li>Save the file and upload it to the UI GreenMetric portal</li>';
        $html .= '</ol>';
        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Преобразует локальное изображение в data URI для встраивания в DOC.
     */
    private function imageAsDataUri(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            default => null,
        };

        if (!$mime) {
            return null;
        }

        $data = base64_encode(file_get_contents($path));
        return "data:{$mime};base64,{$data}";
    }

    /**
     * Генерация img-тега с явными атрибутами размера.
     * Для Word-рендереров атрибуты width/height обычно надёжнее CSS max-*.
     */
    private function buildWordImageTag(string $dataUri, string $alt, int $targetWidthPx = 160): string
    {
        return '<img src="' . $dataUri . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" width="' . $targetWidthPx . '" style="width:' . $targetWidthPx . 'px;height:auto;display:block;">';
    }
}
