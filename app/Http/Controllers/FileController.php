<?php

namespace App\Http\Controllers;

use App\Models\EvidenceFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    /**
     * Скачать файл доказательства
     */
    public function download(EvidenceFile $file)
    {
        Log::info('Download file', [
            'file_id' => $file->id,
            'file_type' => $file->file_type,
            'storage_path' => $file->file_path_storage,
            'file_name_original' => $file->file_name_original,
        ]);
        
        if ($file->file_type === 'link') {
            return redirect()->away($file->external_url);
        }

        $fullPath = storage_path('app/private/' . $file->file_path_storage);
        Log::info('Checking file path', ['full_path' => $fullPath, 'exists' => file_exists($fullPath)]);

        if (!file_exists($fullPath)) {
            Log::error('File not found', ['full_path' => $fullPath]);
            abort(404, 'Файл не найден');
        }

        return response()->download($fullPath, $file->file_name_original);
    }

    /**
     * Просмотр файла (для изображений)
     */
    public function show(EvidenceFile $file)
    {
        Log::info('Show file', [
            'file_id' => $file->id,
            'file_type' => $file->file_type,
            'storage_path' => $file->file_path_storage,
        ]);
        
        if ($file->file_type === 'link') {
            return redirect()->away($file->external_url);
        }

        $fullPath = storage_path('app/private/' . $file->file_path_storage);
        Log::info('Checking file path for show', ['full_path' => $fullPath, 'exists' => file_exists($fullPath)]);

        if (!file_exists($fullPath)) {
            Log::error('File not found for show', ['full_path' => $fullPath]);
            abort(404, 'Файл не найден');
        }

        $fileContent = file_get_contents($fullPath);
        $mimeType = mime_content_type($fullPath);

        return response($fileContent)->header('Content-Type', $mimeType);
    }
}
