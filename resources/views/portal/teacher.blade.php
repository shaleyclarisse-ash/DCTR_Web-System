@extends('layouts.app')
@section('title', 'My classes')
@section('content')
@php $todayClasses = $schedule->where('day', now()->format('l')); @endphp
<section class="portal-hero portal-hero-shs" aria-labelledby="portal-title">
    <div><div class="portal-kicker">Teacher workspace</div><h1 id="portal-title">Your teaching day</h1>
        <p>{{ now()->format('l, F j, Y') }}<span class="portal-divider">·</span>{{ $schedule->count() }} {{ \Illuminate\Support\Str::plural('class', $schedule->count()) }} this week</p>
    </div><div class="portal-hero-icon" aria-hidden="true"><i class="bi bi-easel2"></i></div>
</section>

<nav class="portal-actions" aria-label="Teaching tasks">
    <a class="btn btn-ink" href="{{ route('grades.index') }}"><i class="bi bi-award" aria-hidden="true"></i> Record grades</a>
    <a class="btn btn-accent" href="{{ route('attendance.index') }}"><i class="bi bi-clipboard-check" aria-hidden="true"></i> Take attendance</a>
</nav>

<section class="portal-today mb-3" aria-labelledby="today-classes-title">
    <div class="portal-section-head"><h2 id="today-classes-title">Today’s classes</h2><span>{{ $todayClasses->count() }} scheduled</span></div>
    @if ($todayClasses->isNotEmpty())
        <div class="portal-class-grid">
            @foreach ($todayClasses as $s)
                <article class="portal-class-card">
                    <div class="portal-class-time"><i class="bi bi-clock" aria-hidden="true"></i> {{ $s->time_range }}</div>
                    <h3>{{ $s->subject->code }} <span>·</span> {{ $s->section->label }}</h3>
                    <div class="text-secondary">{{ $s->subject->name }}</div>
                    <div class="portal-class-room"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $s->room ?: 'Room not set' }}</div>
                </article>
            @endforeach
        </div>
    @else
        <div class="panel portal-empty"><i class="bi bi-calendar-check" aria-hidden="true"></i><span>No classes scheduled today.</span></div>
    @endif
</section>

@include('partials.announcements')

<div class="portal-section-head"><h2>Weekly schedule</h2><span>All assigned sections</span></div>
<div class="panel table-responsive">
    <table class="table">
        <thead><tr><th scope="col">Day</th><th scope="col">Time</th><th scope="col">Section</th><th scope="col">Subject</th><th scope="col">Room</th></tr></thead>
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
