@extends('layouts.app')
@section('title', 'Attendance')
@section('content')
<div class="page-head"><div><h1>Student attendance</h1><div class="sub">Select a class schedule and date, then mark each student.</div></div></div>

<form method="GET" class="panel panel-pad mb-3 row g-2">
    <div class="col-lg-7">
        <select name="schedule" class="form-select" onchange="this.form.submit()">
            <option value="">-- Select class schedule --</option>
            @foreach ($schedules as $id => $label)
                <option value="{{ $id }}" @selected((string) $scheduleId === (string) $id)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3"><input type="date" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}" class="form-control" onchange="this.form.submit()"></div>
    <div class="col-lg-2"><button class="btn btn-ink w-100">Load</button></div>
</form>

@if ($scheduleId && $rows->isNotEmpty())
<form method="POST" action="{{ route('attendance.store') }}">
    @csrf
    <input type="hidden" name="schedule" value="{{ $scheduleId }}">
    <input type="hidden" name="date" value="{{ $date }}">
    <div class="panel table-responsive">
        <table class="table">
            <thead><tr><th>Student</th><th>Status on {{ \Illuminate\Support\Carbon::parse($date)->format('M d, Y') }}</th></tr></thead>
            <tbody>
            @foreach ($rows as $e)
                @php $current = $existing[$e->id] ?? 'present'; @endphp
                <tr>
                    <td>{{ $e->student->full_name }}</td>
                    <td>
                        @foreach (['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused'] as $val => $text)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status[{{ $e->id }}]" id="s{{ $e->id }}{{ $val }}" value="{{ $val }}" @checked($current === $val)>
                                <label class="form-check-label" for="s{{ $e->id }}{{ $val }}">{{ $text }}</label>
                            </div>
                        @endforeach
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <button class="btn btn-accent mt-3 px-4">Save attendance</button>
</form>
@elseif ($scheduleId)
    <div class="panel empty">No enrolled students found for this class.</div>
@endif
@endsection
