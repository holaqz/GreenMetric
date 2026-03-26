<?php

namespace App\Http\Controllers;

use App\Models\EvidenceFile;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Скачать файл доказательства
     */
    public function download(EvidenceFile $file)
    {
        if ($file->file_type === 'link') {
            return redirect()->away($file->external_url);
        }

        if (!Storage::disk('private')->exists($file->file_path_storage)) {
            abort(404, 'Файл не найден');
        }

        return Storage::disk('private')->download(
            $file->file_path_storage,
            $file->file_name_original
        );
    }

    /**
     * Просмотр файла (для изображений)
     */
    public function show(EvidenceFile $file)
    {
        if ($file->file_type === 'link') {
            return redirect()->away($file->external_url);
        }

        if (!Storage::disk('private')->exists($file->file_path_storage)) {
            abort(404, 'Файл не найден');
        }

        $fileContent = Storage::disk('private')->get($file->file_path_storage);
        $mimeType = Storage::disk('private')->mimeType($file->file_path_storage);

        return response($fileContent)->header('Content-Type', $mimeType);
    }
}
