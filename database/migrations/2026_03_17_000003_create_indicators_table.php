<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->integer('code_in_category'); // 1, 2, 3... (для WR1, WR2)
            $table->text('question_text');
            $table->string('unit')->nullable(); // '%', 'kWh', 'm²', 'Yes/No'
            $table->enum('input_type', ['number', 'text', 'boolean', 'select', 'select_with_area', 'computed', 'file_only']);
            $table->string('filename_slug')->nullable(); // 'Water_Conservation_Program_Implementation'
            $table->text('description_help')->nullable();
            $table->json('validation_rules')->nullable(); // {"min": 0, "max": 100, "options": [...], "has_area_input": true}
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'code_in_category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicators');
    }
};
