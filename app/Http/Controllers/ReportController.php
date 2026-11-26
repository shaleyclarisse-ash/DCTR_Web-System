<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\Section;
use App\Models\Student;
use App\Models\Trainee;
use Illuminate\Http\Request;

/** Automated academic, training and financial reports (screen / print / CSV). */
class ReportController extends Controller
{
    private const REPORTS = [
        'enrollment' => ['Enrollment list', 'All SHS and driving-course enrollments.'],
        'academic'   => ['Academic performance', 'Quarterly grades and final average per student and subject.'],
        'training'   => ['Driving-training progress', 'Completed sessions, average score and status per trainee.'],
        'financial'  => ['Balances and accounts', 'Total charges, payments and outstanding balance per account.'],
        'collections' => ['Collections', 'Payments received within a date range.'],
    ];

    /** Which department(s) the logged-in user may report on: all | shs | driving. */
    private function dept(): string
    {
        return match (auth()->user()->role) {
            'shs_staff'     => 'shs',
            'driving_staff' => 'driving',
            default         => 'all',
        };
    }

    private function available(): array
    {
        $dept = $this->dept();

        return collect(self::REPORTS)->filter(fn ($r, $key) => match ($key) {
            'academic' => $dept !== 'driving',
            'training' => $dept !== 'shs',
            default    => true,
        })->all();
    }

    public function index()
    {
        return view('reports.index', ['reports' => $this->available()]);
    }

    public function show(Request $request, string $type)
    {
        abort_unless(isset($this->available()[$type]), 404);

        $report = match ($type) {
            'enrollment'  => $this->enrollment(),
            'academic'    => $this->academic($request),
            'training'    => $this->training($request),
            'financial'   => $this->financial($request),
            'collections' => $this->collections($request),
        };

        if ($request->query('export') === 'csv') {
            return response()->streamDownload(function () use ($report) {
                $out = fopen('php://output', 'w');
                fwrite($out, "\xEF\xBB\xBF");                       // UTF-8 BOM so Excel shows ₱ and accents
                fputcsv($out, $report['headers']);
                foreach ($report['rows'] as $row) {
                    fputcsv($out, $row);
                }
                fclose($out);
            }, $type.'-report-'.date('Ymd').'.csv', ['Content-Type' => 'text/csv']);
        }

        return view('reports.show', [
            'type'    => $type,
            'title'   => self::REPORTS[$type][0],
            'headers' => $report['headers'],
            'rows'    => $report['rows'],
            'filters' => $report['filters'] ?? [],
        ]);
    }

    private function money($v): string
    {
        return number_format((float) $v, 2);
    }

    private function enrollment(): array
    {
        $dept = $this->dept();
        $rows = [];

        if ($dept !== 'driving') {
            foreach (Enrollment::with('student', 'section')->get()->sortBy(fn ($e) => $e->student->last_name) as $e) {
                $rows[] = ['Senior High School', $e->student->full_name, $e->section->label, "SY {$e->school_year} - {$e->semester} Sem", ucfirst($e->status)];
            }
        }
        if ($dept !== 'shs') {
            foreach (CourseEnrollment::with('trainee', 'course')->get()->sortBy(fn ($e) => $e->trainee->last_name) as $e) {
                $rows[] = ['Driving School', $e->trainee->full_name, $e->course->code.' - '.$e->course->name, $e->enrolled_at->format('M d, Y'), ucfirst($e->status)];
            }
        }

        return ['headers' => ['Department', 'Name', 'Program / section', 'Period / date', 'Status'], 'rows' => $rows];
    }

    private function academic(Request $request): array
    {
        $sectionId = $request->query('section_id');

        $grades = Grade::with('enrollment.student', 'enrollment.section', 'subject')
            ->when($sectionId, fn ($q) => $q->whereHas('enrollment', fn ($e) => $e->where('section_id', $sectionId)))
            ->get()->groupBy(fn ($g) => $g->enrollment_id.'-'.$g->subject_id);

        $rows = $grades->map(function ($set) {
            $first = $set->first();
            $by    = $set->keyBy('period');
            $avg   = $set->avg('grade');

            return [
                $first->enrollment->student->full_name,
                $first->enrollment->section->label,
                $first->subject->code.' - '.$first->subject->name,
                $by[1]->grade ?? '', $by[2]->grade ?? '', $by[3]->grade ?? '', $by[4]->grade ?? '',
                round($avg, 2),
                $avg >= 75 ? 'Passed' : 'Needs assistance',
            ];
        })->sortBy(0)->values()->all();

        return [
            'headers' => ['Student', 'Section', 'Subject', 'Q1', 'Q2', 'Q3', 'Q4', 'Average', 'Remarks'],
            'rows'    => $rows,
            'filters' => [['name' => 'section_id', 'label' => 'Section', 'type' => 'select', 'value' => $sectionId,
                'options' => Section::orderBy('grade_level')->orderBy('name')->get()->mapWithKeys(fn ($s) => [$s->id => $s->label])->all()]],
        ];
    }

    private function training(Request $request): array
    {
        $courseId = $request->query('course_id');

        $rows = CourseEnrollment::with('trainee', 'course')->withCount('completedSessions')
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))->get()
            ->sortBy(fn ($e) => $e->trainee->last_name)
            ->map(fn ($e) => [
                $e->trainee->full_name,
                $e->course->code,
                $e->course->required_sessions,
                $e->completed_sessions_count,
                $e->progress.'%',
                $e->avg_score !== null ? round($e->avg_score, 1) : '-',
                ucfirst($e->status),
            ])->values()->all();

        return [
            'headers' => ['Trainee', 'Course', 'Required sessions', 'Completed', 'Progress', 'Average score', 'Status'],
            'rows'    => $rows,
            'filters' => [['name' => 'course_id', 'label' => 'Course', 'type' => 'select', 'value' => $courseId,
                'options' => Course::orderBy('code')->pluck('name', 'id')->all()]],
        ];
    }

    private function financial(Request $request): array
    {
        $dept = $this->dept();
        $rows = [];

        $collect = function ($class, $label) use (&$rows) {
            $class::withSum('charges as charged', 'amount')->withSum('payments as paid', 'amount')->orderBy('last_name')->get()
                ->each(function ($a) use (&$rows, $label) {
                    $balance = (float) $a->charged - (float) $a->paid;
                    $rows[] = [$label, $a->full_name, $this->money($a->charged), $this->money($a->paid), $this->money($balance), $balance > 0 ? 'With balance' : 'Settled'];
                });
        };

        if ($dept !== 'driving') {
            $collect(Student::class, 'Student');
        }
        if ($dept !== 'shs') {
            $collect(Trainee::class, 'Trainee');
        }

        return ['headers' => ['Type', 'Name', 'Total charges (₱)', 'Total paid (₱)', 'Balance (₱)', 'Status'], 'rows' => $rows];
    }

    private function collections(Request $request): array
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to', now()->toDateString());
        $dept = $this->dept();

        $payments = Payment::with('payable', 'receiver')->whereBetween('paid_at', [$from, $to])
            ->when($dept === 'shs', fn ($q) => $q->where('payable_type', 'student'))
            ->when($dept === 'driving', fn ($q) => $q->where('payable_type', 'trainee'))
            ->orderBy('paid_at')->get();

        $rows = $payments->map(fn ($p) => [
            $p->or_number, $p->paid_at->format('Y-m-d'), ucfirst($p->payable_type), $p->payable->full_name ?? '-',
            $this->money($p->amount), strtoupper($p->method), $p->receiver->name ?? '-',
        ])->all();
        $rows[] = ['', '', '', 'TOTAL', $this->money($payments->sum('amount')), '', ''];

        return [
            'headers' => ['OR no.', 'Date', 'Type', 'Payer', 'Amount (₱)', 'Method', 'Received by'],
            'rows'    => $rows,
            'filters' => [
                ['name' => 'from', 'label' => 'From', 'type' => 'date', 'value' => $from],
                ['name' => 'to', 'label' => 'To', 'type' => 'date', 'value' => $to],
            ],
        ];
    }
}
