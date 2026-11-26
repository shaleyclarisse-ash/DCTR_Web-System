<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('learner_entry_type', 40)->nullable();
            $table->string('program_track', 20)->nullable();
            $table->string('place_of_birth', 150)->nullable();
            $table->string('religion', 100)->nullable();
            $table->json('learner_classifications')->nullable();
            $table->string('classification_details')->nullable();

            $table->string('house_no')->nullable();
            $table->string('street_name')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->string('zip_code', 20)->nullable();

            $table->boolean('permanent_address_same_as_current')->nullable();
            $table->string('permanent_house_no')->nullable();
            $table->string('permanent_street_name')->nullable();
            $table->string('permanent_barangay')->nullable();
            $table->string('permanent_city')->nullable();
            $table->string('permanent_province')->nullable();
            $table->string('permanent_country')->nullable();
            $table->string('permanent_zip_code', 20)->nullable();
            $table->text('permanent_address')->nullable();

            $table->string('father_name')->nullable();
            $table->string('mother_maiden_name')->nullable();
            $table->text('guardian_address')->nullable();
            $table->string('last_grade_level_completed', 30)->nullable();
            $table->string('last_school_attended')->nullable();
            $table->string('last_school_year_completed', 9)->nullable();
            $table->json('documents_submitted')->nullable();
        });

        Schema::table('trainees', function (Blueprint $table) {
            $table->string('middle_initial', 10)->nullable();
            $table->string('place_of_birth', 150)->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('house_no')->nullable();
            $table->string('street_name')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('region')->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('highest_education', 100)->nullable();
            $table->string('facebook_account')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_person_no', 30)->nullable();
            $table->string('lto_client_id', 50)->nullable();
        });

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->text('training_purpose')->nullable();
            $table->string('preferred_schedule', 20)->nullable();
            $table->string('preferred_transmission', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['training_purpose', 'preferred_schedule', 'preferred_transmission']);
        });

        Schema::table('trainees', function (Blueprint $table) {
            $table->dropColumn([
                'middle_initial', 'place_of_birth', 'marital_status', 'house_no', 'street_name',
                'barangay', 'city', 'province', 'region', 'zip_code', 'highest_education',
                'facebook_account', 'contact_person', 'contact_person_no', 'lto_client_id',
            ]);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'learner_entry_type', 'program_track', 'place_of_birth', 'religion',
                'learner_classifications', 'classification_details', 'house_no', 'street_name',
                'barangay', 'city', 'province', 'country', 'zip_code', 'permanent_address_same_as_current',
                'permanent_house_no', 'permanent_street_name', 'permanent_barangay', 'permanent_city',
                'permanent_province', 'permanent_country', 'permanent_zip_code', 'permanent_address',
                'father_name', 'mother_maiden_name', 'guardian_address', 'last_grade_level_completed',
                'last_school_attended', 'last_school_year_completed', 'documents_submitted',
            ]);
        });
    }
};
