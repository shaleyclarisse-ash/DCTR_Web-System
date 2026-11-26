<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Support\Options;
use Illuminate\Database\Eloquent\Model;

class SectionController extends CrudController
{
    protected function model(): string { return Section::class; }
    protected function title(): string { return 'Sections'; }
    protected function route(): string { return 'sections'; }
    protected function search(): array { return ['name', 'strand', 'adviser', 'room']; }
    protected function counts(): array { return ['enrollments']; }

    protected function columns(): array
    {
        return [
            'Section'  => 'name',
            'Grade'    => fn ($r) => 'Grade '.$r->grade_level,
            'Strand'   => 'strand',
            'Adviser'  => 'adviser',
            'Room'     => 'room',
            'Enrolled' => fn ($r) => $r->enrollments_count.' / '.$r->capacity,
        ];
    }

    protected function fields(?Model $record = null): array
    {
        return [
            ['name' => 'name', 'label' => 'Section name', 'rules' => 'required|string|max:100'],
            ['name' => 'grade_level', 'label' => 'Grade level', 'type' => 'select', 'rules' => 'required', 'options' => ['11' => 'Grade 11', '12' => 'Grade 12']],
            ['name' => 'strand', 'label' => 'Strand', 'type' => 'select', 'rules' => 'required', 'options' => Options::strands()],
            ['name' => 'adviser', 'label' => 'Adviser', 'rules' => 'nullable|string|max:150'],
            ['name' => 'room', 'label' => 'Room', 'rules' => 'nullable|string|max:50'],
            ['name' => 'capacity', 'label' => 'Capacity', 'type' => 'number', 'rules' => 'required|integer|min:1|max:100', 'default' => 40],
        ];
    }
}
