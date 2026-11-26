@extends('layouts.app')
@section('title', 'My portal')
@section('content')
@php
    $charged = $student->total_charges; $paid = $student->total_paid; $balance = $charged - $paid;
@endphp
<div class="page-head">
    <div><h1>{{ $student->first_name }} {{ $student->last_name }}</h1>
        <div class="sub">LRN {{ $student->lrn ?: '-' }}
            @if ($enrollment) &middot; {{ $enrollment->section->label }} &middot; SY {{ $enrollment->school_year }}, {{ $enrollment->semester }} Sem @endif</div></div>
</div>

@include('partials.announcements')

<div class="stat-strip">
    <div class="stat"><div class="num">₱{{ number_format($charged, 2) }}</div><div class="lbl">Total charges</div></div>
    <div class="stat"><div class="num">₱{{ number_format($paid, 2) }}</div><div class="lbl">Total paid</div></div>
    <div class="stat {{ $balance > 0 ? 'warn' : '' }}"><div class="num">₱{{ number_format($balance, 2) }}</div><div class="lbl">{{ $balance > 0 ? 'Remaining balance' : 'Settled' }}</div></div>
    @foreach (['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late'] as $k => $v)
        <div class="stat"><div class="num">{{ $attendance[$k] ?? 0 }}</div><div class="lbl">{{ $v }} (days)</div></div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-xl-6">
        <div class="panel">
            <div class="panel-head"><h2>Class schedule</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>Day</th><th>Time</th><th>Subject</th><th>Teacher</th><th>Room</th></tr></thead>
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
    <div class="col-xl-6">
        <div class="panel">
            <div class="panel-head"><h2>Grades</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>Subject</th><th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th><th>Average</th></tr></thead>
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
