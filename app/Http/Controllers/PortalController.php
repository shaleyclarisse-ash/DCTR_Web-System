<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\ClassSchedule;
use App\Models\Grade;
use App\Models\StudentAttendance;
use App\Models\TrainingSession;
use App\Support\Options;
use Illuminate\Http\Request;

/** Self-service pages for students, trainees, teachers and instructors. */
class PortalController extends Controller
{
    private function sortByDay($schedules)
    {
        return $schedules->sortBy(fn ($s) => [array_search($s->day, Options::DAYS), $s->start_time])->values();
    }

    public function student()
    {
        $user    = auth()->user();
        $student = $user->student;
        abort_unless($student, 404, 'No student record is linked to this account.');

        $enrollment = $student->enrollments()->with('section')->latest('id')->first();

        return view('portal.student', [
            'student'       => $student,
            'enrollment'    => $enrollment,
            'schedule'      => $enrollment
                ? $this->sortByDay(ClassSchedule::with('subject', 'teacher')->where('section_id', $enrollment->section_id)->get())
                : collect(),
            'grades'        => $enrollment
                ? Grade::with('subject')->where('enrollment_id', $enrollment->id)->get()->groupBy('subject_id')
                : collect(),
            'attendance'    => $enrollment
                ? StudentAttendance::where('enrollment_id', $enrollment->id)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')
                : collect(),
            'payments'      => $student->payments()->latest('paid_at')->take(8)->get(),
            'announcements' => Announcement::visibleTo($user)->latest()->take(4)->get(),
        ]);
    }

    public function trainee()
    {
        $user    = auth()->user();
        $trainee = $user->trainee;
        abort_unless($trainee, 404, 'No trainee record is linked to this account.');

        $enrollments = $trainee->courseEnrollments()->with('course')->withCount('completedSessions')->get();
        $ids         = $enrollments->pluck('id');

        return view('portal.trainee', [
            'trainee'       => $trainee,
            'enrollments'   => $enrollments,
            'upcoming'      => TrainingSession::with('courseEnrollment.course', 'instructor', 'vehicle')->whereIn('course_enrollment_id', $ids)
                ->where('status', 'scheduled')->whereDate('session_date', '>=', today())->orderBy('session_date')->orderBy('start_time')->get(),
            'history'       => TrainingSession::with('courseEnrollment.course', 'instructor')->whereIn('course_enrollment_id', $ids)
                ->where('status', '!=', 'scheduled')->latest('session_date')->take(10)->get(),
            'payments'      => $trainee->payments()->latest('paid_at')->take(8)->get(),
            'announcements' => Announcement::visibleTo($user)->latest()->take(4)->get(),
        ]);
    }

    public function teacher()
    {
        $user = auth()->user();

        return view('portal.teacher', [
            'schedule'      => $this->sortByDay(ClassSchedule::with('section', 'subject')->where('teacher_id', $user->id)->get()),
            'announcements' => Announcement::visibleTo($user)->latest()->take(4)->get(),
        ]);
    }

    public function instructor()
    {
        $user       = auth()->user();
        $instructor = $user->instructor;
        abort_unless($instructor, 404, 'No instructor profile is linked to this account. Ask the administrator to link it.');

        $sessions = $instructor->sessions()->with('courseEnrollment.trainee', 'courseEnrollment.course', 'vehicle')
            ->whereDate('session_date', '>=', today()->subDays(7))->whereDate('session_date', '<=', today()->addDays(14))
            ->orderBy('session_date')->orderBy('start_time')->get();

        return view('portal.instructor', [
            'instructor'    => $instructor,
            'needsUpdate'   => $sessions->filter(fn ($s) => $s->status === 'scheduled' && $s->session_date->lt(today())),
            'today'         => $sessions->filter(fn ($s) => $s->session_date->isToday()),
            'upcoming'      => $sessions->filter(fn ($s) => $s->status === 'scheduled' && $s->session_date->gt(today())),
            'announcements' => Announcement::visibleTo($user)->latest()->take(4)->get(),
        ]);
    }

    /** Instructors record attendance, result and score of their own sessions. */
    public function updateSession(Request $request, $id)
    {
        $session = TrainingSession::findOrFail($id);
        abort_unless((int) $session->instructor_id === (int) auth()->user()->instructor?->id, 403);

        $data = $request->validate([
            'status'     => 'required|in:scheduled,completed,cancelled,no_show',
            'attendance' => 'nullable|in:present,absent',
            'score'      => 'nullable|integer|between:0,100',
            'remarks'    => 'nullable|string|max:255',
        ]);

        $session->update($data);
        $done = $session->refreshEnrollmentStatus();

        return back()->with('success', 'Session updated.'.($done ? ' The trainee has completed all required sessions.' : ''));
    }
}
