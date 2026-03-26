<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('university_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users');
            
            // Setting & Infrastructure
            $table->decimal('total_area_hectares', 10, 2)->nullable();
            $table->decimal('green_area_percent', 5, 2)->nullable();
            $table->integer('total_buildings')->nullable();
            
            // Общие метрики
            $table->integer('total_students')->nullable();
            $table->integer('total_staff')->nullable();
            $table->integer('total_researchers')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('university_profiles');
    }
};
