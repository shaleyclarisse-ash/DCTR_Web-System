@extends('layouts.app')
@section('title', $trainee->full_name)
@section('content')
<div class="page-head">
    <div><h1>{{ $trainee->full_name }}</h1>
        <div class="sub">Permit/License {{ $trainee->license_no ?: '-' }}
            &middot; <span class="badge text-bg-{{ $trainee->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($trainee->status) }}</span></div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('trainees.index') }}" class="btn btn-outline-secondary">Back</a>
        <a href="{{ route('trainees.edit', $trainee->id) }}" class="btn btn-accent"><i class="bi bi-pencil"></i> Edit</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="panel panel-pad">
            <h2>Personal information</h2>
            <dl class="row mb-0">
                @foreach ([
                    'Middle initial' => $trainee->middle_initial,
                    'Birthdate' => $trainee->birthdate?->format('M d, Y'), 'Place of birth' => $trainee->place_of_birth,
                    'Gender' => $trainee->sex, 'Marital status' => $trainee->marital_status,
                    'Address' => $trainee->address, 'ZIP / area code' => $trainee->zip_code,
                    'Contact no.' => $trainee->contact_no, 'Email' => $trainee->email,
                    'Highest education' => $trainee->highest_education, 'Facebook account' => $trainee->facebook_account,
                    'LTO client ID' => $trainee->lto_client_id,
                    'Contact person' => $trainee->contact_person, 'Contact person no.' => $trainee->contact_person_no,
                    'Portal login' => $trainee->user ? $trainee->user->email : 'None',
                ] as $k => $v)
                    <dt class="col-5 fw-semibold small">{{ $k }}</dt><dd class="col-7">{{ $v ?: '-' }}</dd>
                @endforeach
            </dl>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-head"><h2>Course enrollments and progress</h2></div>
            <div class="table-responsive"><table class="table">
                <tbody>
                @forelse ($trainee->courseEnrollments as $ce)
                    <tr>
                        <td>{{ $ce->course->code }} - {{ $ce->course->name }}
                            @if ($ce->program_type)<div class="small text-secondary">Program type: {{ $ce->program_type }}</div>@endif
                            <div class="small text-secondary">{{ $ce->completed_sessions_count }}/{{ $ce->course->required_sessions }} sessions &middot; {{ ucfirst($ce->status) }}</div>
                            @if ($ce->training_purpose)<div class="small text-secondary">Purpose: {{ $ce->training_purpose }}</div>@endif
                            @if ($ce->preferred_day || $ce->preferred_date || $ce->preferred_time)
                                <div class="small text-secondary">Requested session: {{ $ce->preferred_day }}{{ $ce->preferred_date ? ' · '.\Illuminate\Support\Carbon::parse($ce->preferred_date)->format('M d, Y') : '' }}{{ $ce->preferred_time ? ' · '.\Illuminate\Support\Carbon::parse($ce->preferred_time)->format('g:i A') : '' }}</div>
                            @endif
                            @if ($ce->preferred_schedule || $ce->preferred_transmission)
                                <div class="small text-secondary">Preference: {{ $ce->preferred_schedule }} {{ $ce->preferred_transmission ? '· '.$ce->preferred_transmission : '' }}</div>
                            @endif
                        </td>
                        <td style="width:35%"><div class="bar"><span class="{{ $ce->progress >= 100 ? 'done' : '' }}" style="width:{{ $ce->progress }}%"></span></div></td>
                        <td class="text-end">{{ $ce->progress }}%</td>
                    </tr>
                @empty
                    <tr><td class="empty">Not enrolled in any course yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
        @include('partials.account-summary', ['owner' => $trainee, 'type' => 'trainee'])
    </div>
</div>
@include('partials.documents', ['owner' => $trainee, 'type' => 'trainee'])
@endsection
