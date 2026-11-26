<?php

namespace App\Providers;

use App\Models\Student;
use App\Models\Trainee;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Charges, payments and documents belong to either a student or a trainee.
        Relation::enforceMorphMap([
            'student' => Student::class,
            'trainee' => Trainee::class,
        ]);

        Paginator::useBootstrapFive();
    }
}
