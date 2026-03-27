<?php

namespace App\Services;

use App\Models\Cycle;
use App\Models\Indicator;
use App\Models\IndicatorResponse;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;

class GreenMetricTemplateExporter
{
    /**
     * Переводы для select опций (RU -> EN)
     */
    private array $selectTranslations = [
        // SI.1 - Тип организации
        'Университет полного цикла (Comprehensive)' => 'Comprehensive University',
        'Специализированное высшее учебное заведение' => 'Specialized Higher Education Institution',
        
        // SI.2 - Климат
        'Тропический влажный' => 'Tropical humid',
        'Тропический с сухой зимой' => 'Tropical with dry winter',
        'Полупустынный' => 'Semi-arid',
        'Пустынный' => 'Arid/Desert',
        'Средиземноморский' => 'Mediterranean',
        'Влажный субтропический' => 'Humid subtropical',
        'Морской западный / океанический' => 'Maritime west coast / Oceanic',
        'Влажный континентальный' => 'Humid continental',
        'Субарктический' => 'Subarctic',
        
        // SI.4 - Тип расположения кампуса
        'Сельская местность' => 'Rural area',
        'Пригород' => 'Suburban',
        'Город' => 'Urban',
        'Центр города' => 'City center',
        'Район высотных зданий' => 'High-rise district',
        
        // SI.5 - Инфраструктура для людей с инвалидностью
        'Нет' => 'None',
        'Политика действует' => 'Policy in place',
        'Инфраструктура на стадии планирования' => 'Infrastructure in planning stage',
        'Инфраструктура доступна частично и функционирует' => 'Infrastructure partially available and functional',
        'Инфраструктура есть во всех зданиях и полностью функционирует' => 'Infrastructure available in all buildings and fully functional',
        
        // SI.6 - Инфраструктура безопасности
        'Пассивная система безопасности и охраны' => 'Passive security system',
        'Инфраструктура безопасности и охраны (CCTV, экстренная линия/кнопка) доступна и полностью функционирует' => 'Security infrastructure (CCTV, emergency line/button) available and fully functional',
        'Инфраструктура безопасности и охраны (CCTV, экстренная линия/кнопка, сертифицированный персонал, огнетушитель, гидрант) доступна и полностью функционирует' => 'Security infrastructure (CCTV, emergency line/button, certified personnel, fire extinguisher, hydrant) available and fully functional',
        'Инфраструктура безопасности и охраны доступна и полностью функционирует, а время реагирования на несчастные случаи, преступления, пожар и природные бедствия составляет более 5 минут' => 'Security infrastructure available and fully functional, response time > 5 minutes',
        'Инфраструктура безопасности и охраны доступна и полностью функционирует, а время реагирования на несчастные случаи, преступления, пожар и природные бедствия составляет менее 5 минут' => 'Security infrastructure available and fully functional, response time < 5 minutes',
        
        // SI.7 - Инфраструктура здоровья
        'Инфраструктура здоровья (первая помощь) отсутствует' => 'No health infrastructure',
        'Инфраструктура здоровья (первая помощь, приемный покой/неотложная помощь, клиника и персонал) доступна' => 'Health infrastructure (first aid, emergency room/clinic, staff) available',
        'Инфраструктура здоровья (первая помощь, приемный покой/неотложная помощь, клиника и сертифицированный персонал) доступна' => 'Health infrastructure (first aid, emergency room/clinic, certified staff) available',
        'Инфраструктура здоровья (первая помощь, приемный покой/неотложная помощь, клиника, больница и сертифицированный персонал) доступна' => 'Health infrastructure (first aid, emergency room, clinic, hospital, certified staff) available',
        'Инфраструктура здоровья (первая помощь, приемный покой/неотложная помощь, клиника, больница и сертифицированный персонал) доступна, систематизирована и доступна для общественности' => 'Health infrastructure available, systematic, and open to public',
        
        // SI.15 - Вклад в SDGs
        'Низкое влияние (поддерживает 1-2 SDGs)' => 'Low impact (supports 1-2 SDGs)',
        'Умеренное влияние (поддерживает 3-5 SDGs)' => 'Moderate impact (supports 3-5 SDGs)',
        'Существенное влияние (поддерживает 6-9 SDGs)' => 'Significant impact (supports 6-9 SDGs)',
        'Высокое влияние (поддерживает 10-13 SDGs)' => 'High impact (supports 10-13 SDGs)',
        'Очень высокое влияние (поддерживает 14-17 SDGs)' => 'Very high impact (supports 14-17 SDGs)',
    ];
    
    /**
     * Получить английский перевод для select опции
     */
    private function getEnglishTranslation(string $russianText): string
    {
        return $this->selectTranslations[$russianText] ?? $russianText;
    }
    public function __construct()
    {
        // Отключаем автоматическое экранирование - будем использовать своё
        Settings::setOutputEscapingEnabled(false);
    }

    public function resolveTemplatePath(): ?string
    {
        $candidates = [
            storage_path('app/templates/master_template.docx'),
            base_path('master_template.docx'),
            base_path('storage/app/templates/master_template.docx'),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    public function buildIndicatorDocxBytes(
        string $templatePath,
        Cycle $cycle,
        IndicatorResponse $response,
        $profile = null
    ): string {
        $indicator = $response->indicator;
        
        // Проверяем целостность шаблона
        if (!$this->isValidDocx($templatePath)) {
            throw new \RuntimeException('Шаблон документа повреждён или не является корректным DOCX файлом');
        }
        
        $template = new TemplateProcessor($templatePath);

        // Helper для безопасной установки значений с UTF-8
        $setValue = function($key, $value) use ($template) {
            $template->setValue($key, $this->escapeXml($value ?? ''));
        };

        // Устанавливаем все переменные из шаблона
        $setValue('category_id', $indicator->category->code ?? '');
        $setValue('category_name', $indicator->category->name ?? '');
        $setValue('code_in_category', $indicator->code_in_category ?? '');
        $setValue('filename_slug', $indicator->filename_slug ?? 'indicator');
        $setValue('unit', $indicator->unit ?? '');
        $setValue('description', $response->program_description ?? '');
        $setValue('parameters', $this->buildParametersText($cycle, $indicator, $response));

        // Ссылки на доказательства - только если есть (максимум 10)
        $links = $this->collectLinks($response);
        $links = array_slice($links, 0, 10); // Ограничиваем 10 ссылок
        $linkDescriptions = $this->collectLinkDescriptions($response);
        $linkDescriptions = array_slice($linkDescriptions, 0, 10); // Ограничиваем 10 описаний
        
        // Устанавливаем ссылки с описаниями
        $this->injectEvidenceLinks($template, $links, $linkDescriptions);

        // Обработка фото (максимум 4)
        $photos = $this->collectPhotos($response);
        $photos = array_slice($photos, 0, 4); // Ограничиваем 4 фото
        $photoDescriptions = $this->collectPhotoDescriptions($response);
        $photoDescriptions = array_slice($photoDescriptions, 0, 4); // Ограничиваем 4 описания
        $normalizedPhotos = $this->normalizePhotosForDocx($photos);
        $this->injectTemplateImages($template, $normalizedPhotos, $photoDescriptions);

        $tmpDocx = storage_path('app/temp/' . $this->buildIndicatorFileName($cycle, $indicator) . '_' . time() . '.docx');
        if (!is_dir(dirname($tmpDocx))) {
            mkdir(dirname($tmpDocx), 0755, true);
        }
        
        // Сохраняем с проверкой
        $template->saveAs($tmpDocx);
        
        // Проверяем результат
        if (!file_exists($tmpDocx) || filesize($tmpDocx) === 0) {
            throw new \RuntimeException('Не удалось сохранить документ DOCX');
        }
        
        if (!$this->isValidDocx($tmpDocx)) {
            throw new \RuntimeException('Сгенерированный документ DOCX повреждён');
        }

        $binary = file_get_contents($tmpDocx);
        @unlink($tmpDocx);
        foreach ($normalizedPhotos as $tmpImage) {
            if (str_contains($tmpImage, storage_path('app/temp'))) {
                @unlink($tmpImage);
            }
        }

        return $binary ?: '';
    }

    /**
     * Собирает ссылки на доказательства из ответа
     */
    private function collectLinks(IndicatorResponse $response): array
    {
        $links = [];
        foreach ($response->files as $file) {
            if ($file->file_type === 'link' && $file->external_url) {
                $links[] = $file->external_url;
            }
        }
        return $links;
    }

    /**
     * Собирает описания для ссылок
     */
    private function collectLinkDescriptions(IndicatorResponse $response): array
    {
        $descriptions = [];
        foreach ($response->files as $file) {
            if ($file->file_type === 'link') {
                $descriptions[] = $file->description ?? '';
            }
        }
        return $descriptions;
    }

    /**
     * Вставляет ссылки и описания в плейсхолдеры
     */
    private function injectEvidenceLinks(TemplateProcessor $template, array $links, array $linkDescriptions): void
    {
        $vars = $template->getVariables();
        
        // Ищем плейсхолдеры для ссылок: evidence_link, evidence_link_1, evidence_link_2, ...
        $linkSlots = [];
        foreach ($vars as $var) {
            if (preg_match('/^evidence_link(_\d+)?$/i', $var)) {
                $linkSlots[] = $var;
            }
        }
        
        // Ищем плейсхолдеры для описаний ссылок: evidence_link_desc, evidence_link_1_desc, ...
        $descSlots = [];
        foreach ($vars as $var) {
            if (preg_match('/^evidence_link(_\d+)?_desc$/i', $var)) {
                $descSlots[] = $var;
            }
        }
        
        if (empty($linkSlots)) {
            // Если нет плейсхолдеров для ссылок - не вставляем
            return;
        }
        
        // Сортируем ссылки: evidence_link, evidence_link_1, evidence_link_2, ...
        usort($linkSlots, function ($a, $b) {
            $an = preg_match('/_(\d+)$/', $a, $am) ? (int) $am[1] : 0;
            $bn = preg_match('/_(\d+)$/', $b, $bm) ? (int) $bm[1] : 0;
            return $an <=> $bn;
        });
        
        // Сортируем описания: evidence_link_desc, evidence_link_1_desc, ...
        usort($descSlots, function ($a, $b) {
            $an = preg_match('/_(\d+)_desc$/', $a, $am) ? (int) $am[1] : 0;
            $bn = preg_match('/_(\d+)_desc$/', $b, $bm) ? (int) $bm[1] : 0;
            return $an <=> $bn;
        });

        // Вставляем ссылки и описания
        $idx = 0;
        foreach ($linkSlots as $slot) {
            if (!isset($links[$idx])) {
                // Если ссылки закончились - очищаем плейсхолдеры
                $template->setValue($slot, '');
                if (isset($descSlots[$idx])) {
                    $template->setValue($descSlots[$idx], '');
                }
                continue;
            }

            // Вставляем ссылку
            $template->setValue($slot, $links[$idx]);
            
            // Вставляем описание
            $description = $linkDescriptions[$idx] ?? '';
            if (isset($descSlots[$idx])) {
                $template->setValue($descSlots[$idx], $description);
            }
            
            $idx++;
        }
        
        // Очищаем оставшиеся неиспользованные описания
        for ($i = $idx; $i < count($descSlots); $i++) {
            $template->setValue($descSlots[$i], '');
        }
    }

    /**
     * Собирает пути к фотографиям из ответа
     */
    private function collectPhotos(IndicatorResponse $response): array
    {
        $photos = [];
        \Log::info('Collecting photos', ['response_id' => $response->id, 'files_count' => $response->files->count()]);
        
        foreach ($response->files as $file) {
            if ($file->file_type === 'link') {
                continue;
            }

            $path = storage_path('app/private/' . $file->file_path_storage);
            \Log::info('Checking file path', ['file_id' => $file->id, 'storage_path' => $file->file_path_storage, 'full_path' => $path, 'exists' => file_exists($path)]);
            
            if (!file_exists($path)) {
                continue;
            }
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                $photos[] = $path;
            }
        }
        
        \Log::info('Collected photos count', ['count' => count($photos)]);
        return $photos;
    }

    /**
     * Собирает описания для фотографий
     */
    private function collectPhotoDescriptions(IndicatorResponse $response): array
    {
        $descriptions = [];
        foreach ($response->files as $file) {
            if ($file->file_type === 'link') {
                continue;
            }
            $descriptions[] = $file->description ?? '';
        }
        return $descriptions;
    }

    /**
     * Проверяет, является ли файл корректным DOCX (ZIP с правильной структурой)
     */
    private function isValidDocx(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return false;
        }

        // DOCX должен содержать [Content_Types].xml и word/document.xml
        $hasContentTypes = $zip->locateName('[Content_Types].xml') !== false;
        $hasDocument = $zip->locateName('word/document.xml') !== false;

        $zip->close();

        return $hasContentTypes && $hasDocument;
    }

    /**
     * Экранирование XML для безопасной вставки в DOCX
     */
    private function escapeXml(string $text): string
    {
        // Удаляем некорректные XML символы
        $text = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text ?? '');
        
        // Простое экранирование специальных символов XML
        return str_replace(
            ['&', '<', '>', '"', "'"],
            ['&amp;', '&lt;', '&gt;', '&quot;', '&apos;'],
            $text
        );
    }

    public function buildIndicatorFileName(Cycle $cycle, Indicator $indicator): string
    {
        return "GreenMetric_{$cycle->year}_{$indicator->category->code}_{$indicator->code_in_category}_{$indicator->filename_slug}";
    }

    private function buildParametersText(Cycle $cycle, Indicator $indicator, IndicatorResponse $response): string
    {
        $rows = [];

        // For computed indicators: show formula and result
        if ($indicator->is_computed && $indicator->formula) {
            $computedValue = $indicator->computeValue($cycle->id);
            
            $rows[] = 'Formula: ' . $indicator->formula;
            
            if ($computedValue !== null) {
                $rows[] = 'Result: ' . number_format($computedValue, 2, '.', ' ');
            } else {
                $rows[] = 'Result: insufficient data';
            }
        } else {
            // For regular indicators: show value only (no duplicates)
            if ($indicator->input_type === 'select' && $response->selected_option !== null) {
                // For select: show English translation
                if (isset($indicator->options[$response->selected_option - 1])) {
                    $russianText = $indicator->options[$response->selected_option - 1];
                    $englishText = $this->getEnglishTranslation($russianText);
                    $rows[] = $englishText;
                }
            } elseif ($response->value_numeric !== null) {
                $rows[] = 'Value: ' . number_format($response->value_numeric, 2, '.', ' ');
            } elseif ($response->value_text !== null && $response->value_text !== '') {
                $rows[] = $response->value_text;
            } elseif ($response->value_boolean !== null) {
                $rows[] = $response->value_boolean ? 'Yes' : 'No';
            }
        }

        return implode("\n", $rows);
    }

    private function injectTemplateImages(TemplateProcessor $template, array $photos, array $photoDescriptions = []): void
    {
        \Log::info('Injecting images', ['photos_count' => count($photos), 'descriptions_count' => count($photoDescriptions)]);
        
        $vars = $template->getVariables();
        \Log::info('Template variables', ['vars' => $vars]);

        // Ищем все плейсхолдеры для фото: photo, photo_1, photo_2, ...
        $photoSlots = [];
        foreach ($vars as $var) {
            if (preg_match('/^photo(_\d+)?$/i', $var)) {
                $photoSlots[] = $var;
            }
        }

        // Ищем плейсхолдеры для описаний: photo_desc, photo_1_desc, photo_2_desc, ...
        $descSlots = [];
        foreach ($vars as $var) {
            if (preg_match('/^photo(_\d+)?_desc$/i', $var)) {
                $descSlots[] = $var;
            }
        }
        
        \Log::info('Photo slots found', ['slots' => $photoSlots, 'desc_slots' => $descSlots]);

        if (empty($photoSlots)) {
            // Если нет плейсхолдеров для фото - не вставляем
            \Log::warning('No photo placeholders in template');
            return;
        }

        // Сортируем фото: photo, photo_1, photo_2, ...
        usort($photoSlots, function ($a, $b) {
            $an = preg_match('/_(\d+)$/', $a, $am) ? (int) $am[1] : 0;
            $bn = preg_match('/_(\d+)$/', $b, $bm) ? (int) $bm[1] : 0;
            return $an <=> $bn;
        });

        // Сортируем описания: photo_desc, photo_1_desc, photo_2_desc, ...
        usort($descSlots, function ($a, $b) {
            $an = preg_match('/_(\d+)_desc$/', $a, $am) ? (int) $am[1] : 0;
            $bn = preg_match('/_(\d+)_desc$/', $b, $bm) ? (int) $bm[1] : 0;
            return $an <=> $bn;
        });

        // Вставляем фото и описания
        $idx = 0;
        foreach ($photoSlots as $slot) {
            \Log::info('Processing photo slot', ['slot' => $slot, 'idx' => $idx, 'has_photo' => isset($photos[$idx])]);
            
            if (!isset($photos[$idx])) {
                // Если фото закончились - очищаем плейсхолдеры
                $template->setValue($slot, '');
                // Очищаем соответствующее описание
                if (isset($descSlots[$idx])) {
                    $template->setValue($descSlots[$idx], '');
                }
                continue;
            }

            // Вставляем фото напрямую через setImageValue
            $imagePath = $photos[$idx];
            \Log::info('Setting image', ['slot' => $slot, 'path' => $imagePath, 'exists' => file_exists($imagePath)]);
            
            try {
                $template->setImageValue($slot, [
                    'path' => $imagePath,
                    'width' => 600,
                    'height' => 400,
                    'ratio' => true,
                ]);
                \Log::info('Image set successfully', ['slot' => $slot]);
            } catch (\Exception $e) {
                \Log::error('Failed to set image', ['slot' => $slot, 'error' => $e->getMessage()]);
            }

            // Вставляем описание
            $description = $photoDescriptions[$idx] ?? '';
            if (isset($descSlots[$idx])) {
                $template->setValue($descSlots[$idx], $description);
                \Log::info('Set description', ['slot' => $descSlots[$idx], 'description' => $description]);
            }

            $idx++;
        }

        // Очищаем оставшиеся неиспользованные описания
        for ($i = $idx; $i < count($descSlots); $i++) {
            $template->setValue($descSlots[$i], '');
        }
    }

    private function normalizePhotosForDocx(array $photos): array
    {
        // Проверяем наличие GD расширения
        if (!extension_loaded('gd')) {
            // Если GD нет, возвращаем оригинальные пути
            // Word сможет обработать их как есть
            \Log::warning('GD extension not loaded, using original paths');
            return $photos;
        }

        // Проверяем, есть ли поддержка imagejpeg
        if (!function_exists('imagejpeg')) {
            \Log::warning('imagejpeg function not available, using original paths');
            return $photos;
        }

        $result = [];
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        foreach ($photos as $path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            
            // PNG файлы не конвертируем - вставляем как есть
            if ($ext === 'png') {
                $result[] = $path;
                \Log::info('Keeping PNG file as-is', ['path' => $path]);
                continue;
            }
            
            // JPEG файлы пытаемся оптимизировать
            $raw = @file_get_contents($path);
            if ($raw === false) {
                $result[] = $path;
                continue;
            }
            $src = @imagecreatefromstring($raw);
            if (!$src) {
                $result[] = $path;
                continue;
            }

            $srcW = imagesx($src);
            $srcH = imagesy($src);
            $scale = min(1400 / max($srcW, 1), 1000 / max($srcH, 1), 1);
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
}

