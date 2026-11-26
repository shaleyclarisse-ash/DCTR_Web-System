<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->string('preferred_day', 15)->nullable();
            $table->date('preferred_date')->nullable();
            $table->time('preferred_time')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['preferred_day', 'preferred_date', 'preferred_time']);
        });
    }
};
