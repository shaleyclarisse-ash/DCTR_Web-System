@extends('layouts.app')
@section('title', 'My portal')
@section('content')
@php $balance = $trainee->balance; @endphp
<div class="page-head"><div><h1>{{ $trainee->first_name }} {{ $trainee->last_name }}</h1><div class="sub">Driving trainee</div></div></div>

@include('partials.announcements')

<div class="row g-3 mb-3">
    @forelse ($enrollments as $e)
        <div class="col-md-6">
            <div class="panel panel-pad">
                <div class="d-flex justify-content-between"><strong>{{ $e->course->code }} - {{ $e->course->name }}</strong>
                    <span class="badge text-bg-{{ $e->status === 'completed' ? 'success' : 'primary' }}">{{ ucfirst($e->status) }}</span></div>
                <div class="text-secondary small mb-2">{{ $e->completed_sessions_count }} of {{ $e->course->required_sessions }} sessions completed</div>
                <div class="bar"><span class="{{ $e->progress >= 100 ? 'done' : '' }}" style="width:{{ $e->progress }}%"></span></div>
                <div class="small mt-1">{{ $e->progress }}% complete</div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="panel empty">You are not enrolled in a driving course yet.</div></div>
    @endforelse
</div>

<div class="stat-strip">
    <div class="stat"><div class="num">₱{{ number_format($trainee->total_charges, 2) }}</div><div class="lbl">Total charges</div></div>
    <div class="stat"><div class="num">₱{{ number_format($trainee->total_paid, 2) }}</div><div class="lbl">Total paid</div></div>
    <div class="stat {{ $balance > 0 ? 'warn' : '' }}"><div class="num">₱{{ number_format($balance, 2) }}</div><div class="lbl">{{ $balance > 0 ? 'Remaining balance' : 'Settled' }}</div></div>
</div>

<div class="row g-3">
    <div class="col-xl-6">
        <div class="panel">
            <div class="panel-head"><h2>Upcoming sessions</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>Date</th><th>Time</th><th>Course</th><th>Instructor</th><th>Vehicle</th></tr></thead>
                <tbody>
                @forelse ($upcoming as $s)
                    <tr><td>{{ $s->session_date->format('D, M d') }}</td><td>{{ $s->time_range }}</td><td>{{ $s->courseEnrollment->course->code }}</td>
                        <td>{{ $s->instructor->name }}</td><td>{{ $s->vehicle->plate_no ?? '-' }}</td></tr>
                @empty
                    <tr><td colspan="5" class="empty">No upcoming sessions.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="panel">
            <div class="panel-head"><h2>Session history</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>Date</th><th>Course</th><th>Result</th><th>Score</th><th>Remarks</th></tr></thead>
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
    <div class="col-12">
        <div class="panel">
            <div class="panel-head"><h2>Recent payments</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>OR no.</th><th>Date</th><th>Method</th><th class="text-end">Amount</th></tr></thead>
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
</div>
@endsection
