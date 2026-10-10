<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_habit_journals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('journal_date');
            $table->time('wake_up_time');
            $table->json('worship_items')->nullable();
            $table->text('exercise_activity');
            $table->text('meal_breakfast');
            $table->text('meal_lunch');
            $table->text('meal_dinner');
            $table->text('learning_activity');
            $table->text('social_activity');
            $table->time('sleep_time');
            $table->timestamps();

            $table->unique(['student_id', 'journal_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_habit_journals');
    }
};
