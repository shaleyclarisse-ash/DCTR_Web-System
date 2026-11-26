@extends('layouts.app')
@section('title', $student->full_name)
@section('content')
<div class="page-head">
    <div><h1>{{ $student->full_name }}</h1>
        <div class="sub">LRN {{ $student->lrn ?: '-' }} &middot; Grade {{ $student->grade_level }} {{ $student->strand }}
            &middot; <span class="badge text-bg-{{ $student->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($student->status) }}</span></div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">Back</a>
        <a href="{{ route('students.edit', $student->id) }}" class="btn btn-accent"><i class="bi bi-pencil"></i> Edit</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="panel panel-pad">
            <h2>Personal information</h2>
            <dl class="row mb-0">
                @foreach ([
                    'Learner type' => $student->learner_entry_type, 'Birthdate' => $student->birthdate?->format('M d, Y'),
                    'Place of birth' => $student->place_of_birth, 'Gender' => $student->sex, 'Religion' => $student->religion,
                    'Program track' => $student->program_track, 'Strand / specialization' => $student->strand,
                    'Classification' => implode(', ', $student->learner_classifications ?? []),
                    'Current address' => $student->address, 'Permanent address' => $student->permanent_address,
                    'Contact no.' => $student->contact_no, 'Email' => $student->email,
                    'Father' => $student->father_name, 'Mother (maiden name)' => $student->mother_maiden_name,
                    'Guardian' => $student->guardian_name, 'Guardian contact' => $student->guardian_contact,
                    'Guardian address' => $student->guardian_address,
                    'Last grade completed' => $student->last_grade_level_completed,
                    'Last school attended' => $student->last_school_attended,
                    'Last school year completed' => $student->last_school_year_completed,
                    'Documents submitted' => implode(', ', $student->documents_submitted ?? []),
                    'Portal login' => $student->user ? $student->user->email : 'None',
                ] as $k => $v)
                    <dt class="col-5 fw-semibold small">{{ $k }}</dt><dd class="col-7">{{ $v ?: '-' }}</dd>
                @endforeach
            </dl>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-head"><h2>Enrollment history</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>School year</th><th>Semester</th><th>Section</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($student->enrollments->sortByDesc('id') as $e)
                    <tr><td>{{ $e->school_year }}</td><td>{{ $e->semester }}</td><td>{{ $e->section->label }}</td><td>{{ ucfirst($e->status) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="empty">Not enrolled yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
        @include('partials.account-summary', ['owner' => $student, 'type' => 'student'])
    </div>
</div>
@include('partials.documents', ['owner' => $student, 'type' => 'student'])
@endsection
