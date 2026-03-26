<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'response_id',
        'uploaded_by',
        'file_name_original',
        'file_path_storage',
        'file_type',
        'external_url',
        'description',
        'file_size_bytes',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(IndicatorResponse::class, 'response_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Получить URL для скачивания файла
     */
    public function getDownloadUrlAttribute(): string
    {
        if ($this->file_type === 'link') {
            return $this->external_url ?? '#';
        }

        return route('files.download', $this->id);
    }

    /**
     * Проверить, является ли файл ссылкой
     */
    public function getIsLinkAttribute(): bool
    {
        return $this->file_type === 'link';
    }
}
