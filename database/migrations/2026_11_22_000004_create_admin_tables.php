<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Charges and payments belong to a student OR a trainee (polymorphic).
        Schema::create('charges', function (Blueprint $t) {
            $t->id();
            $t->morphs('payable');
            $t->string('description');
            $t->decimal('amount', 10, 2);
            $t->date('due_date')->nullable();
            $t->timestamps();
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->morphs('payable');
            $t->string('or_number', 30)->unique();
            $t->decimal('amount', 10, 2);
            $t->string('method', 20)->default('cash');
            $t->date('paid_at');
            $t->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('remarks')->nullable();
            $t->timestamps();
        });

        Schema::create('announcements', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('body');
            $t->string('audience', 10)->default('all');         // all, shs, driving
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('documents', function (Blueprint $t) {
            $t->id();
            $t->morphs('documentable');
            $t->string('title');
            $t->string('path');
            $t->string('original_name');
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['documents', 'announcements', 'payments', 'charges'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
