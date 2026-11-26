<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;

/** Daily student attendance per class schedule. */
class AttendanceController extends Controller
{
    private function schedules()
    {
        $user = auth()->user();

        return ClassSchedule::with('section', 'subject')
            ->when($user->role === 'teacher', fn ($q) => $q->where('teacher_id', $user->id))
            ->orderBy('section_id')->orderBy('day')->orderBy('start_time')->get()
            ->mapWithKeys(fn ($s) => [$s->id => $s->section->label.'  |  '.$s->subject->code.'  |  '.$s->day.' '.$s->time_range]);
    }

    public function index(Request $request)
    {
        $schedules  = $this->schedules();
        $scheduleId = $request->query('schedule');
        $date       = $request->query('date', now()->toDateString());
        $rows       = collect();
        $existing   = collect();

        if ($scheduleId && $schedules->has((int) $scheduleId)) {
            $schedule = ClassSchedule::findOrFail($scheduleId);

            $rows = Enrollment::with('student')->where('section_id', $schedule->section_id)->where('status', 'enrolled')
                ->get()->sortBy(fn ($e) => $e->student->last_name)->values();

            $existing = StudentAttendance::where('class_schedule_id', $schedule->id)->whereDate('date', $date)
                ->pluck('status', 'enrollment_id');
        }

        return view('attendance.index', compact('schedules', 'scheduleId', 'date', 'rows', 'existing'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'schedule'   => 'required|integer',
            'date'       => 'required|date|before_or_equal:today',
            'status'     => 'required|array',
            'status.*'   => 'in:present,absent,late,excused',
        ]);

        abort_unless($this->schedules()->has((int) $request->schedule), 403);

        $schedule = ClassSchedule::findOrFail($request->schedule);
        $validIds = Enrollment::where('section_id', $schedule->section_id)->pluck('id')->all();

        foreach ($request->input('status') as $enrollmentId => $status) {
            if (in_array((int) $enrollmentId, $validIds, true)) {
                StudentAttendance::updateOrCreate(
                    ['enrollment_id' => $enrollmentId, 'class_schedule_id' => $schedule->id, 'date' => $request->date],
                    ['status' => $status]
                );
            }
        }

        return redirect()->route('attendance.index', ['schedule' => $schedule->id, 'date' => $request->date])
            ->with('success', 'Attendance saved.');
    }
}
