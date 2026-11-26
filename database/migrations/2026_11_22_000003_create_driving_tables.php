<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainees', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('first_name');
            $t->string('middle_name')->nullable();
            $t->string('last_name');
            $t->date('birthdate')->nullable();
            $t->string('sex', 10)->nullable();
            $t->string('address')->nullable();
            $t->string('contact_no', 30)->nullable();
            $t->string('email')->nullable();
            $t->string('license_no', 50)->nullable();   // student permit / license
            $t->string('status', 20)->default('active');
            $t->timestamps();
        });

        Schema::create('courses', function (Blueprint $t) {
            $t->id();
            $t->string('code', 20)->unique();           // TDC, PDC
            $t->string('name');
            $t->text('description')->nullable();
            $t->unsignedInteger('required_sessions')->default(1);
            $t->decimal('fee', 10, 2)->default(0);
            $t->timestamps();
        });

        Schema::create('instructors', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('license_no', 50)->nullable();
            $t->string('contact_no', 30)->nullable();
            $t->string('specialization', 20)->default('Both');   // TDC, PDC, Both
            $t->string('status', 20)->default('active');
            $t->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $t) {
            $t->id();
            $t->string('plate_no', 20)->unique();
            $t->string('model');
            $t->string('type', 20)->default('Car');
            $t->string('transmission', 20)->default('Manual');
            $t->string('status', 20)->default('available');      // available, maintenance, unavailable
            $t->timestamps();
        });

        Schema::create('course_enrollments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('trainee_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->constrained()->restrictOnDelete();
            $t->date('enrolled_at');
            $t->string('status', 20)->default('ongoing');        // ongoing, completed, dropped
            $t->timestamps();
        });

        Schema::create('training_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('course_enrollment_id')->constrained()->cascadeOnDelete();
            $t->foreignId('instructor_id')->constrained()->restrictOnDelete();
            $t->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $t->date('session_date');
            $t->time('start_time');
            $t->time('end_time');
            $t->string('status', 20)->default('scheduled');      // scheduled, completed, cancelled, no_show
            $t->string('attendance', 10)->nullable();            // present, absent
            $t->unsignedTinyInteger('score')->nullable();        // assessment 0-100
            $t->string('remarks')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['training_sessions', 'course_enrollments', 'vehicles', 'instructors', 'courses', 'trainees'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
