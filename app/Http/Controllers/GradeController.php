<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\Grade;
use Illuminate\Http\Request;

/** Quarterly grade recording per class (section + subject). */
class GradeController extends Controller
{
    /** Classes the user may grade: all for staff, only own classes for teachers. */
    private function classes()
    {
        $user = auth()->user();

        return ClassSchedule::with('section', 'subject')
            ->when($user->role === 'teacher', fn ($q) => $q->where('teacher_id', $user->id))
            ->get()
            ->unique(fn ($s) => $s->section_id.'-'.$s->subject_id)
            ->mapWithKeys(fn ($s) => [$s->section_id.'-'.$s->subject_id => $s->section->label.'  |  '.$s->subject->code.' '.$s->subject->name]);
    }

    public function index(Request $request)
    {
        $classes = $this->classes();
        $key     = $request->query('class');
        $rows    = collect();
        $grades  = collect();

        if ($key && $classes->has($key)) {
            [$sectionId, $subjectId] = explode('-', $key);

            $rows = Enrollment::with('student')->where('section_id', $sectionId)->where('status', 'enrolled')
                ->get()->sortBy(fn ($e) => $e->student->last_name)->values();

            $grades = Grade::where('subject_id', $subjectId)->whereIn('enrollment_id', $rows->pluck('id'))
                ->get()->groupBy('enrollment_id')->map(fn ($g) => $g->keyBy('period'));
        }

        return view('grades.index', compact('classes', 'key', 'rows', 'grades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class'      => 'required',
            'grades'     => 'nullable|array',
            'grades.*.*' => 'nullable|numeric|between:0,100',
        ]);

        $key = $request->input('class');
        abort_unless($this->classes()->has($key), 403);

        [$sectionId, $subjectId] = explode('-', $key);
        $validIds = Enrollment::where('section_id', $sectionId)->pluck('id')->all();

        foreach ($request->input('grades', []) as $enrollmentId => $periods) {
            if (! in_array((int) $enrollmentId, $validIds, true)) {
                continue;
            }
            foreach ($periods as $period => $value) {
                if (! in_array((int) $period, [1, 2, 3, 4], true)) {
                    continue;
                }
                $where = ['enrollment_id' => $enrollmentId, 'subject_id' => $subjectId, 'period' => $period];

                if ($value === null || $value === '') {
                    Grade::where($where)->delete();
                } else {
                    Grade::updateOrCreate($where, ['grade' => $value]);
                }
            }
        }

        return redirect()->route('grades.index', ['class' => $key])->with('success', 'Grades saved.');
    }
}
