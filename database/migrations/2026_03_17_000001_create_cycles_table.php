<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cycles', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique(); // Год рейтинга (2026)
            $table->date('data_period_start'); // Начало периода данных (2025-01-01)
            $table->date('data_period_end');   // Конец периода данных (2025-12-31)
            $table->date('submission_start')->nullable(); // Начало приёма (2026-02-26)
            $table->date('submission_end')->nullable();   // Дедлайн (2026-06-30)
            $table->enum('status', ['draft', 'open', 'closed', 'submitted'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cycles');
    }
};
