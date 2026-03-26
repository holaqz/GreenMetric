<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('indicator_responses')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users');
            
            $table->string('file_name_original');
            $table->string('file_path_storage', 500);
            $table->string('file_type', 50); // 'pdf', 'png', 'jpg', 'link'
            $table->string('external_url', 500)->nullable(); // Если это ссылка
            $table->string('description', 255)->nullable();
            $table->integer('file_size_bytes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_files');
    }
};
