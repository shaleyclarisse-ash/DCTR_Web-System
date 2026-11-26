@extends('layouts.app')
@php
    $page = $page ?? 'dashboard';
    $pageTitles = ['announcements' => 'Announcements', 'sessions' => 'Upcoming Sessions', 'records' => 'Training Records', 'payments' => 'Payment History'];
@endphp
@section('title', $pageTitles[$page] ?? 'Dashboard')
@section('content')
@php
    $balance = $trainee->balance;
@endphp
@if ($page === 'dashboard')
<section class="portal-hero portal-hero-driving" aria-labelledby="portal-title">
    <div><div class="portal-kicker">Driving School trainee</div><h1 id="portal-title">Welcome, {{ $trainee->first_name }}</h1>
        <p>{{ $upcoming->count() }} upcoming {{ \Illuminate\Support\Str::plural('session', $upcoming->count()) }}<span class="portal-divider">·</span>{{ $enrollments->count() }} active {{ \Illuminate\Support\Str::plural('course', $enrollments->count()) }}</p>
    </div><div class="portal-hero-icon" aria-hidden="true"><i class="bi bi-car-front"></i></div>
</section>
@else
<div class="page-head">
    <div><div class="sub">Driving School trainee</div><h1>{{ $pageTitles[$page] ?? 'Dashboard' }}</h1></div>
    <a class="btn btn-outline-secondary" href="{{ route('portal.trainee') }}"><i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard</a>
</div>
@endif

@if (in_array($page, ['dashboard', 'announcements'], true))
<div id="announcements" class="dashboard-anchor">@include('partials.announcements')</div>
@if ($page === 'announcements' && $announcements->isEmpty())
    <div class="panel empty">No announcements are available right now.</div>
@endif
@endif

@if (in_array($page, ['dashboard', 'sessions'], true))
<div id="class-schedule" class="panel mb-3 dashboard-anchor">
    <div class="panel-head"><h2>Upcoming sessions</h2><span class="small text-secondary">Next lessons</span></div>
    <div class="table-responsive"><table class="table">
        <thead><tr><th scope="col">Date</th><th scope="col">Time</th><th scope="col">Course</th><th scope="col">Instructor</th><th scope="col">Vehicle</th></tr></thead>
        <tbody>
        @forelse ($upcoming as $s)
            <tr><td>{{ $s->session_date->format('D, M d') }}</td><td>{{ $s->time_range }}</td><td>{{ $s->courseEnrollment->course->code }}</td><td>{{ $s->instructor->name }}</td><td>{{ $s->vehicle->plate_no ?? '-' }}</td></tr>
        @empty
            <tr><td colspan="5" class="empty">No upcoming sessions.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endif

@if ($page === 'dashboard')
<div class="portal-section-head"><h2>Your courses</h2><span>Training progress</span></div>
<div class="row g-3 mb-3">
    @forelse ($enrollments as $e)
        <div class="col-md-6">
            <div class="panel panel-pad">
                <div class="d-flex justify-content-between"><strong>{{ $e->course->code }} - {{ $e->course->name }}</strong>
                    <span class="badge text-bg-{{ $e->status === 'completed' ? 'success' : 'primary' }}">{{ ucfirst($e->status) }}</span></div>
                <div class="text-secondary small mb-2">{{ $e->completed_sessions_count }} of {{ $e->course->required_sessions }} sessions completed</div>
                <div class="bar" role="progressbar" aria-label="{{ $e->course->name }} progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $e->progress }}"><span class="{{ $e->progress >= 100 ? 'done' : '' }}" style="width:{{ $e->progress }}%"></span></div>
                <div class="small mt-1">{{ $e->progress }}% complete</div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="panel empty">You are not enrolled in a driving course yet.</div></div>
    @endforelse
</div>
@endif

@if (in_array($page, ['dashboard', 'payments'], true))
<section aria-labelledby="trainee-summary-title" class="portal-summary">
<div class="portal-section-head"><h2 id="trainee-summary-title">Your account</h2><span>Training fees and payments</span></div>
<div class="stat-strip portal-stat-strip">
    <div class="stat"><div class="num">₱{{ number_format($trainee->total_charges, 2) }}</div><div class="lbl">Total charges</div></div>
    <div class="stat"><div class="num">₱{{ number_format($trainee->total_paid, 2) }}</div><div class="lbl">Total paid</div></div>
    <div class="stat {{ $balance > 0 ? 'warn' : '' }}"><div class="num">₱{{ number_format($balance, 2) }}</div><div class="lbl">{{ $balance > 0 ? 'Remaining balance' : 'Settled' }}</div></div>
</div>
</section>
@endif

<div class="row g-3">
    @if (in_array($page, ['dashboard', 'records'], true))
    <div id="training-records" class="col-xl-6 dashboard-anchor">
        <div class="panel">
            <div class="panel-head"><h2>Session history</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th scope="col">Date</th><th scope="col">Course</th><th scope="col">Result</th><th scope="col">Score</th><th scope="col">Remarks</th></tr></thead>
                <tbody>
                @forelse ($history as $s)
                    <tr><td>{{ $s->session_date->format('M d, Y') }}</td><td>{{ $s->courseEnrollment->course->code }}</td>
                        <td>{{ \Illuminate\Support\Str::headline($s->status) }}</td><td>{{ $s->score ?? '-' }}</td><td>{{ $s->remarks ?: '-' }}</td></tr>
                @empty
                    <tr><td colspan="5" class="empty">No completed sessions yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    @endif
    @if (in_array($page, ['dashboard', 'payments'], true))
    <div id="payment-history" class="col-xl-6 dashboard-anchor">
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
@endsection
