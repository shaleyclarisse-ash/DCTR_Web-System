<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $accountTypes = [
            'App\\Models\\Student' => 'student',
            'App\\Models\\Trainee' => 'trainee',
        ];

        foreach (['charges' => 'payable_type', 'payments' => 'payable_type', 'documents' => 'documentable_type'] as $table => $column) {
            foreach ($accountTypes as $class => $alias) {
                DB::table($table)->where($column, $class)->update([$column => $alias]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The aliases were already valid for existing records before normalization.
    }
};
