<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained()->onDelete('cascade');
            $table->foreignId('indicator_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            
            // Выбранный вариант ответа (1-5 для select-индикаторов)
            $table->integer('selected_option')->nullable();
            
            // Числовое значение (площадь в м², проценты и т.д.)
            $table->decimal('value_numeric', 15, 2)->nullable();
            
            // Текстовое значение (Yes/No, комментарии)
            $table->string('value_text', 255)->nullable();
            
            // Булево значение
            $table->boolean('value_boolean')->nullable();
            
            // Описание программы (то, что пользователь пишет руками)
            $table->text('program_description')->nullable();
            
            // Статус заполнения
            $table->enum('status', ['in_progress', 'ready_for_review', 'approved'])->default('in_progress');
            
            $table->timestamps();

            $table->unique(['cycle_id', 'indicator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_responses');
    }
};
