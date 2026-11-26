@extends('layouts.app')
@section('title', 'My classes')
@section('content')
<div class="page-head">
    <div><h1>My classes</h1><div class="sub">Your weekly teaching schedule</div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('grades.index') }}" class="btn btn-ink"><i class="bi bi-award"></i> Record grades</a>
        <a href="{{ route('attendance.index') }}" class="btn btn-accent"><i class="bi bi-clipboard-check"></i> Take attendance</a>
    </div>
</div>

@include('partials.announcements')

<div class="panel table-responsive">
    <table class="table">
        <thead><tr><th>Day</th><th>Time</th><th>Section</th><th>Subject</th><th>Room</th></tr></thead>
        <tbody>
        @forelse ($schedule as $s)
            <tr class="{{ $s->day === now()->format('l') ? 'table-warning' : '' }}">
                <td>{{ $s->day }}</td><td>{{ $s->time_range }}</td><td>{{ $s->section->label }}</td>
                <td>{{ $s->subject->code }} - {{ $s->subject->name }}</td><td>{{ $s->room ?: '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">No classes assigned to you yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
