<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')->constrained()->restrictOnDelete();
            $table->enum('day_of_week', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'])->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['day_of_week', 'start_time', 'end_time']);
            $table->unique(['teaching_assignment_id', 'day_of_week', 'start_time'], 'schedule_assignment_day_start_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
