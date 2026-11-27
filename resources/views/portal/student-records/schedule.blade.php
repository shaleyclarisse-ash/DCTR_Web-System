@php
    $days = collect(\App\Support\Options::DAYS)->take(5)
        ->merge($schedule->pluck('day')->unique())->unique();
    $timeSlots = $schedule->sortBy([['start_time', 'asc'], ['end_time', 'asc']])->groupBy('time_range');
@endphp

<section class="student-record" aria-labelledby="student-schedule-title">
    <header class="student-record-heading">
        <h1 id="student-schedule-title">Class Schedule</h1>
        @if ($enrollment)
            <p>{{ $enrollment->section->label }} &middot; {{ $enrollment->semester }} semester &middot; SY {{ $enrollment->school_year }}</p>
        @endif
    </header>

    @if ($schedule->isEmpty())
        <div class="panel empty">No schedule available yet.</div>
    @else
        <div class="panel table-responsive" tabindex="0" role="region" aria-label="Weekly class schedule">
            <table class="table student-record-table student-timetable">
                <thead>
                    <tr>
                        <th scope="col">Time</th>
                        @foreach ($days as $day)
                            <th scope="col">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($timeSlots as $time => $classes)
                        @php $classesByDay = $classes->groupBy('day'); @endphp
                        <tr>
                            <th scope="row" class="student-timetable-time">{{ $time }}</th>
                            @foreach ($days as $day)
                                <td>
                                    @forelse ($classesByDay->get($day, []) as $class)
                                        <div class="student-timetable-class">
                                            <strong>{{ $class->subject->name }}</strong>
                                            @if ($class->teacher)<span>{{ $class->teacher->name }}</span>@endif
                                            @if ($class->room)<span>Room {{ $class->room }}</span>@endif
                                        </div>
                                    @empty
                                        <span class="text-secondary" aria-hidden="true">&mdash;</span><span class="visually-hidden">No class</span>
                                    @endforelse
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
