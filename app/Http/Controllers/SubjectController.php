<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Support\Options;
use Illuminate\Database\Eloquent\Model;

class SubjectController extends CrudController
{
    protected function model(): string { return Subject::class; }
    protected function title(): string { return 'Subjects'; }
    protected function route(): string { return 'subjects'; }
    protected function search(): array { return ['code', 'name', 'strand']; }
    protected function order($q): \Illuminate\Database\Eloquent\Builder { return $q->orderBy('grade_level')->orderBy('code'); }

    protected function columns(): array
    {
        return [
            'Code'     => 'code',
            'Subject'  => 'name',
            'Grade'    => fn ($r) => 'Grade '.$r->grade_level,
            'Strand'   => fn ($r) => $r->strand ?: 'Core (all strands)',
            'Semester' => 'semester',
        ];
    }

    protected function fields(?Model $record = null): array
    {
        return [
            ['name' => 'code', 'label' => 'Subject code', 'rules' => 'required|string|max:30', 'unique' => true],
            ['name' => 'name', 'label' => 'Subject name', 'rules' => 'required|string|max:150'],
            ['name' => 'grade_level', 'label' => 'Grade level', 'type' => 'select', 'rules' => 'required', 'options' => ['11' => 'Grade 11', '12' => 'Grade 12']],
            ['name' => 'strand', 'label' => 'Strand', 'type' => 'select', 'blank' => 'Core (all strands)', 'options' => Options::strands()],
            ['name' => 'semester', 'label' => 'Semester', 'type' => 'select', 'rules' => 'required', 'options' => Options::semesters()],
        ];
    }
}
