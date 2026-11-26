<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('lrn', 12)->nullable()->unique();
            $t->string('first_name');
            $t->string('middle_name')->nullable();
            $t->string('last_name');
            $t->date('birthdate')->nullable();
            $t->string('sex', 10)->nullable();
            $t->string('address')->nullable();
            $t->string('contact_no', 30)->nullable();
            $t->string('email')->nullable();
            $t->string('guardian_name')->nullable();
            $t->string('guardian_contact', 30)->nullable();
            $t->string('grade_level', 2)->default('11');
            $t->string('strand', 20)->nullable();
            $t->string('status', 20)->default('active');
            $t->timestamps();
        });

        Schema::create('sections', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('grade_level', 2);
            $t->string('strand', 20)->nullable();
            $t->string('adviser')->nullable();
            $t->string('room', 50)->nullable();
            $t->unsignedInteger('capacity')->default(40);
            $t->timestamps();
        });

        Schema::create('subjects', function (Blueprint $t) {
            $t->id();
            $t->string('code', 30)->unique();
            $t->string('name');
            $t->string('grade_level', 2);
            $t->string('strand', 20)->nullable();   // null = core subject
            $t->string('semester', 10)->default('1st');
            $t->timestamps();
        });

        Schema::create('enrollments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->foreignId('section_id')->constrained()->restrictOnDelete();
            $t->string('school_year', 9);
            $t->string('semester', 10);
            $t->decimal('fee', 10, 2)->default(0);
            $t->string('status', 20)->default('enrolled');
            $t->timestamps();
            $t->unique(['student_id', 'school_year', 'semester']);
        });

        Schema::create('class_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('section_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained()->restrictOnDelete();
            $t->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('room', 50)->nullable();
            $t->string('day', 12);
            $t->time('start_time');
            $t->time('end_time');
            $t->timestamps();
        });

        Schema::create('grades', function (Blueprint $t) {
            $t->id();
            $t->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('period');       // 1-4 (quarter)
            $t->decimal('grade', 5, 2);
            $t->timestamps();
            $t->unique(['enrollment_id', 'subject_id', 'period']);
        });

        Schema::create('student_attendances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $t->foreignId('class_schedule_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->string('status', 10);               // present, absent, late, excused
            $t->timestamps();
            $t->unique(['enrollment_id', 'class_schedule_id', 'date'], 'attendance_unique');
        });
    }

    public function down(): void
    {
        foreach (['student_attendances', 'grades', 'class_schedules', 'enrollments', 'subjects', 'sections', 'students'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
