<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Charge;
use App\Models\CourseEnrollment;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Trainee;
use App\Models\TrainingSession;
use App\Models\Vehicle;
use App\Support\Options;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return match ($user->role) {
            'student'    => redirect()->route('portal.student'),
            'trainee'    => redirect()->route('portal.trainee'),
            'teacher'    => redirect()->route('portal.teacher'),
            'instructor' => redirect()->route('portal.instructor'),
            default      => $this->staff($user),
        };
    }

    /** Administrative dashboard (admin sees both departments, staff see their own). */
    private function staff($user)
    {
        $showShs     = in_array($user->role, ['admin', 'shs_staff']);
        $showDriving = in_array($user->role, ['admin', 'driving_staff']);
        $types       = collect([$showShs ? 'student' : null, $showDriving ? 'trainee' : null])->filter()->values()->all();

        $d = ['showShs' => $showShs, 'showDriving' => $showDriving];

        // ---------------- Senior High School ----------------
        if ($showShs) {
            $d['students'] = Student::where('status', 'active')->count();
            $d['enrolled'] = Enrollment::where('status', 'enrolled')->where('school_year', Options::schoolYear())->count();

            $d['byStrand'] = Student::where('status', 'active')
                ->selectRaw('coalesce(strand, ?) as label, count(*) as total', ['Unassigned'])
                ->groupBy('strand')->pluck('total', 'label');

            $d['shsAttendance'] = StudentAttendance::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

            $d['avgBySubject'] = Grade::join('subjects', 'subjects.id', '=', 'grades.subject_id')
                ->selectRaw('subjects.code as label, round(avg(grades.grade), 1) as average')
                ->groupBy('subjects.code')->pluck('average', 'label');

            // students whose average is below the passing grade (75) may need academic assistance
            $d['atRisk'] = Enrollment::with('student', 'section')->where('status', 'enrolled')
                ->whereHas('grades')->withAvg('grades', 'grade')->get()
                ->filter(fn ($e) => $e->grades_avg_grade < 75)->sortBy('grades_avg_grade')->take(6);
        }

        // ---------------- Driving School ----------------
        if ($showDriving) {
            $d['trainees']    = Trainee::where('status', 'active')->count();
            $d['ongoing']     = CourseEnrollment::where('status', 'ongoing')->count();
            $d['instructors'] = Instructor::where('status', 'active')->count();
            $d['vehicles']    = Vehicle::where('status', 'available')->count();
            $d['vehicleTotal'] = Vehicle::count();
            $d['vehiclesToday'] = TrainingSession::whereDate('session_date', today())->where('status', '!=', 'cancelled')
                ->whereNotNull('vehicle_id')->distinct()->count('vehicle_id');

            $d['byCourse'] = CourseEnrollment::join('courses', 'courses.id', '=', 'course_enrollments.course_id')
                ->selectRaw('courses.code as label, count(*) as total')->groupBy('courses.code')->pluck('total', 'label');

            $d['sessionStatus'] = TrainingSession::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

            $d['upcoming'] = TrainingSession::with('courseEnrollment.trainee', 'courseEnrollment.course', 'instructor', 'vehicle')
                ->where('status', 'scheduled')->whereBetween('session_date', [today(), today()->addDays(7)])
                ->orderBy('session_date')->orderBy('start_time')->take(8)->get();

            $d['progress'] = CourseEnrollment::with('trainee', 'course')->withCount('completedSessions')
                ->where('status', 'ongoing')->latest('id')->take(6)->get();
        }

        // ---------------- Finance (limited to the department) ----------------
        $charged   = (float) Charge::whereIn('payable_type', $types)->sum('amount');
        $collected = (float) Payment::whereIn('payable_type', $types)->sum('amount');
        $d['charged']     = $charged;
        $d['collected']   = $collected;
        $d['outstanding'] = $charged - $collected;

        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
        $byMonth = Payment::whereIn('payable_type', $types)->where('paid_at', '>=', $months->first())
            ->get(['amount', 'paid_at'])->groupBy(fn ($p) => $p->paid_at->format('Y-m'));
        $d['monthLabels'] = $months->map(fn ($m) => $m->format('M Y'))->all();
        $d['monthValues'] = $months->map(fn ($m) => (float) ($byMonth[$m->format('Y-m')] ?? collect())->sum('amount'))->all();

        $accounts = collect();
        foreach ($types as $type) {
            $class = $type === 'student' ? Student::class : Trainee::class;
            $class::withSum('charges as charged', 'amount')->withSum('payments as paid', 'amount')->get()
                ->each(fn ($a) => $accounts->push([
                    'type' => $type, 'id' => $a->id, 'name' => $a->full_name, 'balance' => $a->charged - $a->paid,
                ]));
        }
        $d['withBalance'] = $accounts->where('balance', '>', 0)->count();
        $d['topBalances'] = $accounts->where('balance', '>', 0)->sortByDesc('balance')->take(6);

        $d['announcements'] = Announcement::visibleTo($user)->latest()->take(4)->get();

        return view('dashboard.staff', ['d' => $d]);
    }
}
