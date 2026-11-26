<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\User;
use App\Support\Options;
use Illuminate\Database\Eloquent\Model;

class InstructorController extends CrudController
{
    protected function model(): string { return Instructor::class; }
    protected function title(): string { return 'Instructors'; }
    protected function route(): string { return 'instructors'; }
    protected function search(): array { return ['name', 'license_no', 'contact_no', 'specialization']; }

    protected function columns(): array
    {
        return [
            'Name'           => 'name',
            'License no.'    => 'license_no',
            'Contact'        => 'contact_no',
            'Specialization' => 'specialization',
            'Status'         => fn ($r) => $this->badge($r->status),
        ];
    }

    protected function fields(?Model $record = null): array
    {
        return [
            ['name' => 'name', 'label' => 'Full name', 'rules' => 'required|string|max:150'],
            ['name' => 'license_no', 'label' => 'Professional license no.', 'rules' => 'nullable|string|max:50'],
            ['name' => 'contact_no', 'label' => 'Contact no.', 'rules' => 'nullable|string|max:30'],
            ['name' => 'specialization', 'label' => 'Specialization', 'type' => 'select', 'rules' => 'required', 'options' => Options::make(['TDC', 'PDC', 'Both'])],
            ['name' => 'user_id', 'label' => 'Login account (create it first under Users with the Instructor role)', 'type' => 'select',
                'options' => fn () => User::where('role', 'instructor')->orderBy('name')->pluck('name', 'id')->all()],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'rules' => 'required', 'options' => Options::make(['active', 'inactive'])],
        ];
    }
}
