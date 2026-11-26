@extends('layouts.app')
@section('title', 'My sessions')
@section('content')
<section class="portal-hero portal-hero-driving" aria-labelledby="portal-title">
    <div><div class="portal-kicker">Instructor workspace</div><h1 id="portal-title">Your lesson schedule</h1>
        <p>{{ $instructor->name }}<span class="portal-divider">·</span>{{ $instructor->specialization ?: 'Driving School instructor' }}</p>
    </div><div class="portal-hero-icon" aria-hidden="true"><i class="bi bi-steering-wheel"></i></div>
</section>

@if ($errors->any())
    <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
@endif

<section aria-label="Session overview" class="portal-summary portal-instructor-summary">
    <div class="stat-strip portal-stat-strip">
        <div class="stat"><div class="num">{{ $needsUpdate->count() }}</div><div class="lbl">Past sessions to update</div></div>
        <div class="stat"><div class="num">{{ $today->count() }}</div><div class="lbl">Sessions today</div></div>
        <div class="stat"><div class="num">{{ $upcoming->count() }}</div><div class="lbl">Upcoming sessions</div></div>
    </div>
</section>

@include('partials.announcements')

@foreach ([['Needs your update (past sessions still marked scheduled)', $needsUpdate, true], ['Today', $today, true], ['Upcoming (next 14 days)', $upcoming, false]] as [$heading, $list, $editable])
    @if ($list->isNotEmpty() || $heading === 'Today')
    <div class="panel mb-3">
        <div class="panel-head"><h2>{{ $heading }}</h2><span class="badge text-bg-secondary">{{ $list->count() }}</span></div>
        <div class="table-responsive"><table class="table align-middle">
            <thead><tr><th scope="col">Date</th><th scope="col">Time</th><th scope="col">Trainee</th><th scope="col">Vehicle</th>@if ($editable)<th scope="col" class="instructor-update-heading">Record lesson outcome</th>@endif</tr></thead>
            <tbody>
            @forelse ($list as $s)
                <tr>
                    <td>{{ $s->session_date->format('D, M d') }}</td><td>{{ $s->time_range }}</td>
                    <td>{{ $s->courseEnrollment->trainee->full_name }} <span class="text-secondary">({{ $s->courseEnrollment->course->code }})</span></td>
                    <td>{{ $s->vehicle->plate_no ?? '-' }}</td>
                    @if ($editable)
                    <td>
                        <form method="POST" action="{{ route('portal.instructor.session', $s->id) }}" class="d-flex gap-2 flex-wrap instructor-update-form">
                            @csrf @method('PUT')
                            <label class="visually-hidden" for="session-status-{{ $s->id }}">Session status</label>
                            <select id="session-status-{{ $s->id }}" name="status" class="form-select form-select-sm instructor-status">
                                @foreach (['scheduled', 'completed', 'cancelled', 'no_show'] as $st)
                                    <option value="{{ $st }}" @selected($s->status === $st)>{{ \Illuminate\Support\Str::headline($st) }}</option>
                                @endforeach
                            </select>
                            <label class="visually-hidden" for="session-attendance-{{ $s->id }}">Trainee attendance</label>
                            <select id="session-attendance-{{ $s->id }}" name="attendance" class="form-select form-select-sm instructor-attendance">
                                <option value="">Attendance</option>
                                @foreach (['present', 'absent'] as $a)<option value="{{ $a }}" @selected($s->attendance === $a)>{{ ucfirst($a) }}</option>@endforeach
                            </select>
                            <label class="visually-hidden" for="session-score-{{ $s->id }}">Score from 0 to 100</label>
                            <input id="session-score-{{ $s->id }}" type="number" name="score" min="0" max="100" value="{{ $s->score }}" placeholder="Score" class="form-control form-control-sm instructor-score">
                            <label class="visually-hidden" for="session-remarks-{{ $s->id }}">Lesson remarks</label>
                            <input id="session-remarks-{{ $s->id }}" name="remarks" value="{{ $s->remarks }}" placeholder="Remarks" class="form-control form-control-sm instructor-remarks">
                            <button class="btn btn-sm btn-ink"><i class="bi bi-check2" aria-hidden="true"></i> Save</button>
                        </form>
                    </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No sessions today.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
    @endif
@endforeach
@endsection
