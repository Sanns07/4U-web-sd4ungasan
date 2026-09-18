<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 20);
            $table->enum('semester', ['ganjil', 'genap']);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
            $table->unique(['academic_year', 'semester']);
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained()->restrictOnDelete();
            $table->string('name', 80);
            $table->unsignedTinyInteger('grade_level');
            $table->string('room')->nullable();
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('teachers')->restrictOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['academic_period_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
        Schema::dropIfExists('academic_periods');
    }
};
