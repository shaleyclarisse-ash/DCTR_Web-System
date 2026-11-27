@extends('layouts.app')
@php
    $page = $page ?? 'dashboard';
    $pageTitles = ['announcements' => 'Announcements', 'schedule' => 'Class Schedule', 'grades' => 'Grades', 'payments' => 'Payment History'];
@endphp
@section('title', $pageTitles[$page] ?? 'Dashboard')
@section('content')
@if (in_array($page, ['announcements', 'schedule', 'grades'], true))
    @include('portal.student-records.'.$page)
@else
@if ($page === 'dashboard')
<section class="portal-hero portal-hero-shs" aria-labelledby="portal-title">
    <div><div class="portal-kicker">Senior High School student</div>
        <h1 id="portal-title">Welcome, {{ $student->first_name }}</h1>
        <p>{{ $student->full_name }}
            @if ($enrollment)<span class="portal-divider">·</span>{{ $enrollment->section->label }}<span class="portal-divider">·</span>SY {{ $enrollment->school_year }}, {{ $enrollment->semester }} semester @endif
        </p>
    </div>
    <div class="portal-hero-icon" aria-hidden="true"><i class="bi bi-mortarboard"></i></div>
</section>
@else
<div class="page-head">
    <div><div class="sub">Senior High School student</div><h1>{{ $pageTitles[$page] ?? 'Dashboard' }}</h1></div>
    <a class="btn btn-outline-secondary" href="{{ route('portal.student') }}"><i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard</a>
</div>
@endif

@if ($page === 'dashboard')
<div id="announcements" class="dashboard-anchor">@include('partials.announcements')</div>
@endif

@if (in_array($page, ['dashboard', 'payments'], true))
@php
    $charged = $student->total_charges; $paid = $student->total_paid; $balance = $charged - $paid;
@endphp
<section aria-labelledby="student-summary-title" class="portal-summary">
    <div class="portal-section-head">
        <h2 id="student-summary-title">{{ $page === 'payments' ? 'Payment summary' : 'Your school snapshot' }}</h2>
        @if ($page === 'dashboard')<span>Attendance and account</span>@endif
    </div>
<div class="stat-strip portal-stat-strip">
    <div class="stat"><div class="num">₱{{ number_format($charged, 2) }}</div><div class="lbl">Total charges</div></div>
    <div class="stat"><div class="num">₱{{ number_format($paid, 2) }}</div><div class="lbl">Total paid</div></div>
    <div class="stat {{ $balance > 0 ? 'warn' : '' }}"><div class="num">₱{{ number_format($balance, 2) }}</div><div class="lbl">{{ $balance > 0 ? 'Remaining balance' : 'Settled' }}</div></div>
    @if ($page === 'dashboard')
        @foreach (['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late'] as $k => $v)
            <div class="stat"><div class="num">{{ $attendance[$k] ?? 0 }}</div><div class="lbl">{{ $v }} (days)</div></div>
        @endforeach
    @endif
</div>
</section>
@endif

<div class="row g-3">
    @if ($page === 'dashboard')
    <div id="class-schedule" class="col-xl-6 dashboard-anchor">
        <div class="panel">
            <div class="panel-head"><h2>Class schedule</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th scope="col">Day</th><th scope="col">Time</th><th scope="col">Subject</th><th scope="col">Teacher</th><th scope="col">Room</th></tr></thead>
                <tbody>
                @forelse ($schedule as $s)
                    <tr><td>{{ $s->day }}</td><td>{{ $s->time_range }}</td><td>{{ $s->subject->name }}</td><td>{{ $s->teacher->name ?? '-' }}</td><td>{{ $s->room ?: '-' }}</td></tr>
                @empty
                    <tr><td colspan="5" class="empty">No schedule available yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    @endif
    @if ($page === 'dashboard')
    <div id="grades" class="col-xl-6 dashboard-anchor">
        <div class="panel">
            <div class="panel-head"><h2>Grades</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th scope="col">Subject</th><th scope="col">Q1</th><th scope="col">Q2</th><th scope="col">Q3</th><th scope="col">Q4</th><th scope="col">Average</th></tr></thead>
                <tbody>
                @forelse ($grades as $set)
                    @php $by = $set->keyBy('period'); $avg = $set->avg('grade'); @endphp
                    <tr><td>{{ $set->first()->subject->name }}</td>
                        @foreach ([1, 2, 3, 4] as $p)<td>{{ $by[$p]->grade ?? '-' }}</td>@endforeach
                        <td class="fw-semibold {{ $avg < 75 ? 'text-danger' : '' }}">{{ number_format($avg, 2) }}</td></tr>
                @empty
                    <tr><td colspan="6" class="empty">No grades recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    @endif
    @if (in_array($page, ['dashboard', 'payments'], true))
    <div id="payment-history" class="col-12 dashboard-anchor">
        <div class="panel">
            <div class="panel-head"><h2>Recent payments</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th scope="col">OR no.</th><th scope="col">Date</th><th scope="col">Method</th><th scope="col" class="text-end">Amount</th></tr></thead>
                <tbody>
                @forelse ($payments as $p)
                    <tr><td>{{ $p->or_number }}</td><td>{{ $p->paid_at->format('M d, Y') }}</td><td>{{ strtoupper($p->method) }}</td><td class="text-end">₱{{ number_format($p->amount, 2) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="empty">No payments yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    @endif
</div>
@endif
@endsection
