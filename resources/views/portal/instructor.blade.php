@extends('layouts.app')
@section('title', 'My sessions')
@section('content')
<div class="page-head"><div><h1>My training sessions</h1><div class="sub">{{ $instructor->name }} &middot; {{ $instructor->specialization }}</div></div></div>

@include('partials.announcements')

@foreach ([['Needs your update (past sessions still marked scheduled)', $needsUpdate, true], ['Today', $today, true], ['Upcoming (next 14 days)', $upcoming, false]] as [$heading, $list, $editable])
    @if ($list->isNotEmpty() || $heading === 'Today')
    <div class="panel mb-3">
        <div class="panel-head"><h2>{{ $heading }}</h2></div>
        <div class="table-responsive"><table class="table align-middle">
            <thead><tr><th>Date</th><th>Time</th><th>Trainee</th><th>Vehicle</th>@if ($editable)<th style="min-width:520px">Update</th>@endif</tr></thead>
            <tbody>
            @forelse ($list as $s)
                <tr>
                    <td>{{ $s->session_date->format('D, M d') }}</td><td>{{ $s->time_range }}</td>
                    <td>{{ $s->courseEnrollment->trainee->full_name }} <span class="text-secondary">({{ $s->courseEnrollment->course->code }})</span></td>
                    <td>{{ $s->vehicle->plate_no ?? '-' }}</td>
                    @if ($editable)
                    <td>
                        <form method="POST" action="{{ route('portal.instructor.session', $s->id) }}" class="d-flex gap-1 flex-wrap">
                            @csrf @method('PUT')
                            <select name="status" class="form-select form-select-sm" style="width:125px">
                                @foreach (['scheduled', 'completed', 'cancelled', 'no_show'] as $st)
                                    <option value="{{ $st }}" @selected($s->status === $st)>{{ \Illuminate\Support\Str::headline($st) }}</option>
                                @endforeach
                            </select>
                            <select name="attendance" class="form-select form-select-sm" style="width:110px">
                                <option value="">Attendance</option>
                                @foreach (['present', 'absent'] as $a)<option value="{{ $a }}" @selected($s->attendance === $a)>{{ ucfirst($a) }}</option>@endforeach
                            </select>
                            <input type="number" name="score" min="0" max="100" value="{{ $s->score }}" placeholder="Score" class="form-control form-control-sm" style="width:80px">
                            <input name="remarks" value="{{ $s->remarks }}" placeholder="Remarks" class="form-control form-control-sm" style="width:140px">
                            <button class="btn btn-sm btn-ink">Save</button>
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
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@endsection
