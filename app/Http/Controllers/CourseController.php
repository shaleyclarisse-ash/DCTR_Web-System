<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Database\Eloquent\Model;

/** Driving courses, e.g. Theoretical Driving Course (TDC) and Practical Driving Course (PDC). */
class CourseController extends CrudController
{
    protected function model(): string { return Course::class; }
    protected function title(): string { return 'Courses'; }
    protected function route(): string { return 'courses'; }
    protected function search(): array { return ['code', 'name']; }
    protected function counts(): array { return ['courseEnrollments']; }

    protected function columns(): array
    {
        return [
            'Code'              => 'code',
            'Course'            => 'name',
            'Required sessions' => 'required_sessions',
            'Fee'               => fn ($r) => '₱'.number_format($r->fee, 2),
            'Enrollees'         => 'course_enrollments_count',
        ];
    }

    protected function fields(?Model $record = null): array
    {
        return [
            ['name' => 'code', 'label' => 'Course code (e.g. TDC, PDC)', 'rules' => 'required|alpha_num|max:20', 'unique' => true],
            ['name' => 'name', 'label' => 'Course name', 'rules' => 'required|string|max:150'],
            ['name' => 'required_sessions', 'label' => 'Required training sessions', 'type' => 'number', 'rules' => 'required|integer|min:1|max:100'],
            ['name' => 'fee', 'label' => 'Course fee (₱)', 'type' => 'number', 'step' => '0.01', 'rules' => 'required|numeric|min:0'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string|max:1000', 'col' => 12],
        ];
    }
}
