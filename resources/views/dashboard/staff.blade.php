@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
    $peso  = fn ($v) => '₱'.number_format($v, 2);
    $label = fn ($c) => $c->keys()->map(fn ($k) => \Illuminate\Support\Str::headline($k))->values();
@endphp

<div class="page-head">
    <div>
        <h1>Good day, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</h1>
        <div class="sub">{{ now()->format('l, F j, Y') }} &middot;
            {{ $d['showShs'] && $d['showDriving'] ? 'Senior High School and Driving School' : ($d['showShs'] ? 'Senior High School' : 'Driving School') }}
        </div>
    </div>
</div>

{{-- ================= key numbers ================= --}}
<div class="stat-strip">
    @if ($d['showShs'])
        <div class="stat"><div class="num">{{ $d['students'] }}</div><div class="lbl">Active students</div></div>
        <div class="stat"><div class="num">{{ $d['enrolled'] }}</div><div class="lbl">Enrolled this school year</div></div>
    @endif
    @if ($d['showDriving'])
        <div class="stat"><div class="num">{{ $d['trainees'] }}</div><div class="lbl">Active trainees</div></div>
        <div class="stat"><div class="num">{{ $d['ongoing'] }}</div><div class="lbl">Ongoing driving courses</div></div>
        <div class="stat"><div class="num">{{ $d['instructors'] }}</div><div class="lbl">Active instructors</div></div>
        <div class="stat"><div class="num">{{ $d['vehicles'] }}<small class="fs-6 text-secondary"> / {{ $d['vehicleTotal'] }}</small></div>
            <div class="lbl">Vehicles available ({{ $d['vehiclesToday'] }} in use today)</div></div>
    @endif
    <div class="stat"><div class="num">{{ $peso($d['collected']) }}</div><div class="lbl">Total collected</div></div>
    <div class="stat warn"><div class="num">{{ $peso($d['outstanding']) }}</div><div class="lbl">Outstanding balances ({{ $d['withBalance'] }} accounts)</div></div>
</div>

{{-- ================= analytics ================= --}}
<div class="row g-3 mb-3">
    @if ($d['showShs'])
        <div class="col-lg-4"><div class="panel panel-pad"><h2>Students per strand</h2><div class="chart-box"><canvas id="cStrand"></canvas></div></div></div>
        <div class="col-lg-4"><div class="panel panel-pad"><h2>Average grade per subject</h2><div class="chart-box"><canvas id="cGrades"></canvas></div></div></div>
        <div class="col-lg-4"><div class="panel panel-pad"><h2>Student attendance</h2><div class="chart-box"><canvas id="cShsAtt"></canvas></div></div></div>
    @endif
    @if ($d['showDriving'])
        <div class="col-lg-4"><div class="panel panel-pad"><h2>Enrollments per driving course</h2><div class="chart-box"><canvas id="cCourse"></canvas></div></div></div>
        <div class="col-lg-4"><div class="panel panel-pad"><h2>Training sessions by status</h2><div class="chart-box"><canvas id="cSessions"></canvas></div></div></div>
    @endif
    <div class="col-lg-4"><div class="panel panel-pad"><h2>Collections, last 6 months (₱)</h2><div class="chart-box"><canvas id="cMoney"></canvas></div></div></div>
</div>

<div class="row g-3">
    {{-- ---------- Driving: upcoming + progress ---------- --}}
    @if ($d['showDriving'])
        <div class="col-xl-7">
            <div class="panel">
                <div class="panel-head"><h2>Upcoming training sessions (next 7 days)</h2><a href="{{ route('training-sessions.index') }}" class="small">View all</a></div>
                <div class="table-responsive"><table class="table">
                    <thead><tr><th>Date</th><th>Time</th><th>Trainee</th><th>Instructor</th><th>Vehicle</th></tr></thead>
                    <tbody>
                    @forelse ($d['upcoming'] as $s)
                        <tr>
                            <td>{{ $s->session_date->format('D, M d') }}</td><td>{{ $s->time_range }}</td>
                            <td>{{ $s->courseEnrollment->trainee->full_name }} <span class="text-secondary">({{ $s->courseEnrollment->course->code }})</span></td>
                            <td>{{ $s->instructor->name }}</td><td>{{ $s->vehicle->plate_no ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">No sessions scheduled for the next 7 days.</td></tr>
                    @endforelse
                    </tbody>
                </table></div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="panel">
                <div class="panel-head"><h2>Driving-training progress</h2></div>
                <div class="table-responsive"><table class="table">
                    <tbody>
                    @forelse ($d['progress'] as $e)
                        <tr>
                            <td>{{ $e->trainee->full_name }}<div class="small text-secondary">{{ $e->course->code }} &middot; {{ $e->completed_sessions_count }}/{{ $e->course->required_sessions }} sessions</div></td>
                            <td style="width:40%"><div class="bar"><span class="{{ $e->progress >= 100 ? 'done' : '' }}" style="width:{{ $e->progress }}%"></span></div></td>
                            <td class="text-end">{{ $e->progress }}%</td>
                        </tr>
                    @empty
                        <tr><td class="empty">No ongoing courses.</td></tr>
                    @endforelse
                    </tbody>
                </table></div>
            </div>
        </div>
    @endif

    {{-- ---------- SHS: at-risk students ---------- --}}
    @if ($d['showShs'])
        <div class="col-xl-6">
            <div class="panel">
                <div class="panel-head"><h2>Students who may need academic assistance</h2></div>
                <div class="table-responsive"><table class="table">
                    <thead><tr><th>Student</th><th>Section</th><th class="text-end">Average</th></tr></thead>
                    <tbody>
                    @forelse ($d['atRisk'] as $e)
                        <tr><td>{{ $e->student->full_name }}</td><td>{{ $e->section->name }}</td>
                            <td class="text-end text-danger fw-semibold">{{ number_format($e->grades_avg_grade, 1) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="empty">No students below the passing average (75).</td></tr>
                    @endforelse
                    </tbody>
                </table></div>
            </div>
        </div>
    @endif

    {{-- ---------- Finance ---------- --}}
    <div class="col-xl-6">
        <div class="panel">
            <div class="panel-head"><h2>Largest outstanding balances</h2><a href="{{ route('payments.index', ['filter' => 'balance']) }}" class="small">View all</a></div>
            <div class="table-responsive"><table class="table">
                <tbody>
                @forelse ($d['topBalances'] as $a)
                    <tr>
                        <td><a href="{{ route('payments.account', [$a['type'], $a['id']]) }}">{{ $a['name'] }}</a>
                            <div class="small text-secondary">{{ ucfirst($a['type']) }}</div></td>
                        <td class="text-end fw-semibold">{{ $peso($a['balance']) }}</td>
                    </tr>
                @empty
                    <tr><td class="empty">All accounts are settled.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>

    {{-- ---------- Announcements ---------- --}}
    <div class="col-xl-6">
        <div class="panel panel-pad">
            <h2>Announcements</h2>
            @forelse ($d['announcements'] as $a)
                <div class="note mb-2"><strong>{{ $a->title }}</strong> <span class="text-secondary small">{{ $a->created_at->diffForHumans() }}</span><br>{{ $a->body }}</div>
            @empty
                <div class="text-secondary">No announcements.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const palette = ['#14263b', '#f5b301', '#3a7ca5', '#81b29a', '#e07a5f', '#8d99ae'];
function draw(id, type, labels, data, name) {
    const el = document.getElementById(id);
    if (!el) return;
    new Chart(el, {
        type,
        data: { labels, datasets: [{ label: name, data, backgroundColor: type === 'bar' ? '#1f3a57' : palette, borderWidth: 0, borderRadius: type === 'bar' ? 4 : 0 }] },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: type === 'doughnut', position: 'bottom' } },
            scales: type === 'bar' ? { y: { beginAtZero: true } } : {}
        }
    });
}
@if ($d['showShs'])
    draw('cStrand', 'bar', @json($d['byStrand']->keys()), @json($d['byStrand']->values()), 'Students');
    draw('cGrades', 'bar', @json($d['avgBySubject']->keys()), @json($d['avgBySubject']->values()), 'Average');
    draw('cShsAtt', 'doughnut', @json($label($d['shsAttendance'])), @json($d['shsAttendance']->values()), 'Attendance');
@endif
@if ($d['showDriving'])
    draw('cCourse', 'bar', @json($d['byCourse']->keys()), @json($d['byCourse']->values()), 'Enrollments');
    draw('cSessions', 'doughnut', @json($label($d['sessionStatus'])), @json($d['sessionStatus']->values()), 'Sessions');
@endif
draw('cMoney', 'bar', @json($d['monthLabels']), @json($d['monthValues']), 'Collected');
</script>
@endpush
