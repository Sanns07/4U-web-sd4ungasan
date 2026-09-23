<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'class_id']);
            $table->index(['student_id', 'class_id'], 'enrollment_student_class_index');
        });
    }

    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropIndex('enrollment_student_class_index');
            $table->unique(['student_id', 'class_id']);
        });
    }
};
