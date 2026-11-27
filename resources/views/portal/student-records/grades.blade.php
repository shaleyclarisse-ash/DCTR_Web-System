<section class="student-record" aria-labelledby="student-grades-title">
    <header class="student-record-heading">
        <h1 id="student-grades-title">Student Grade Report</h1>
    </header>

    <div class="panel">
        <dl class="student-report-details">
            <div><dt>Student</dt><dd>{{ $student->full_name }}</dd></div>
            @if ($student->lrn)
                <div><dt>Learner Reference Number</dt><dd>{{ $student->lrn }}</dd></div>
            @endif
            @if ($enrollment)
                <div><dt>Grade and section</dt><dd>{{ $enrollment->section->label }}</dd></div>
                <div><dt>School year and semester</dt><dd>SY {{ $enrollment->school_year }} &middot; {{ $enrollment->semester }} semester</dd></div>
            @endif
        </dl>
        <div class="table-responsive student-report-table" tabindex="0" role="region" aria-label="Subject grades">
            <table class="table student-record-table">
                <thead>
                    <tr>
                        <th scope="col">Subject code</th>
                        <th scope="col">Subject</th>
                        @foreach (range(1, 4) as $quarter)
                            <th scope="col" class="text-end">Quarter {{ $quarter }}</th>
                        @endforeach
                        <th scope="col" class="text-end">Average to date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grades as $subjectGrades)
                        @php $byQuarter = $subjectGrades->keyBy('period'); $subject = $subjectGrades->first()->subject; @endphp
                        <tr>
                            <td>{{ $subject->code }}</td>
                            <th scope="row">{{ $subject->name }}</th>
                            @foreach (range(1, 4) as $quarter)
                                <td class="text-end">{{ $byQuarter->get($quarter)?->grade ?? '—' }}</td>
                            @endforeach
                            <td class="text-end fw-semibold">{{ number_format($subjectGrades->avg('grade'), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">No grades recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
