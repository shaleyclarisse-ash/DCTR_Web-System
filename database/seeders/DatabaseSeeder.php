<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Trainee;
use App\Models\TrainingSession;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\Options;
use Illuminate\Database\Seeder;

/**
 * Demo data so every screen has something to show.
 * All demo accounts use the password:  password
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $make = fn ($name, $email, $role) => User::updateOrCreate(
    ['email' => $email],
    [
        'name' => $name,
        'password' => 'password',
        'role' => $role,
    ]
);

        $make('System Administrator', 'admin@dctr.test', 'admin');
        $make('SHS Staff', 'shs@dctr.test', 'shs_staff');
        $make('Driving School Staff', 'driving@dctr.test', 'driving_staff');
        $teacher    = $make('Maria Teacher', 'teacher@dctr.test', 'teacher');
        $instrUser  = $make('Ramon Instructor', 'instructor@dctr.test', 'instructor');
        $studentUsr = $make('Juan Dela Cruz', 'student@dctr.test', 'student');
        $traineeUsr = $make('Ana Reyes', 'trainee@dctr.test', 'trainee');

        // ------------------------------------------------ Senior High School
        $sy = Options::schoolYear();
        $section = Section::create(['name' => 'Integrity', 'grade_level' => '11', 'strand' => 'STEM', 'adviser' => 'Maria Teacher', 'room' => 'Room 101', 'capacity' => 40]);
        $section2 = Section::create(['name' => 'Diligence', 'grade_level' => '11', 'strand' => 'ABM', 'adviser' => 'Maria Teacher', 'room' => 'Room 102', 'capacity' => 40]);

        $subjects = collect([
            ['GENMATH', 'General Mathematics'], ['ORALCOM', 'Oral Communication'], ['EARTHSCI', 'Earth and Life Science'], ['PE1', 'Physical Education 1'],
        ])->map(fn ($s) => Subject::create(['code' => $s[0], 'name' => $s[1], 'grade_level' => '11', 'strand' => null, 'semester' => '1st']));

        foreach ($subjects as $i => $subject) {
            ClassSchedule::create([
                'section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'room' => 'Room 101',
                'day' => Options::DAYS[$i], 'start_time' => '08:00:00', 'end_time' => '09:00:00',
            ]);
        }

        $names = [['Juan', 'Dela Cruz'], ['Maria', 'Santos'], ['Pedro', 'Garcia'], ['Liza', 'Mendoza'], ['Carlo', 'Ramos'], ['Ella', 'Torres'], ['Noel', 'Bautista'], ['Grace', 'Flores']];
        $students = collect($names)->map(function ($n, $i) use ($studentUsr, $section, $sy) {
            $student = Student::create([
                'user_id' => $i === 0 ? $studentUsr->id : null,
                'lrn' => '1234567890'.str_pad((string) ($i + 10), 2, '0', STR_PAD_LEFT),
                'first_name' => $n[0], 'last_name' => $n[1], 'sex' => $i % 2 ? 'Female' : 'Male', 'grade_level' => '11', 'strand' => 'STEM',
                'address' => 'Digos City', 'contact_no' => '0917000'.str_pad((string) $i, 4, '0', STR_PAD_LEFT), 'email' => $i === 0 ? 'student@dctr.test' : null,
                'guardian_name' => 'Parent of '.$n[0], 'guardian_contact' => '09180000000',
            ]);
            $enrollment = Enrollment::create(['student_id' => $student->id, 'section_id' => $section->id, 'school_year' => $sy, 'semester' => '1st', 'fee' => 8500, 'status' => 'enrolled']);
            $student->charges()->create(['description' => "Tuition/fees SY {$sy} - 1st Sem", 'amount' => 8500]);

            return $enrollment;
        });

        $schedule = ClassSchedule::first();
        foreach ($students as $i => $enrollment) {
            foreach ($subjects as $subject) {
                foreach ([1, 2] as $q) {
                    Grade::create(['enrollment_id' => $enrollment->id, 'subject_id' => $subject->id, 'period' => $q, 'grade' => $i === 3 ? rand(68, 74) : rand(80, 96)]);
                }
            }
            foreach ([0, 1, 2] as $day) {
                StudentAttendance::create(['enrollment_id' => $enrollment->id, 'class_schedule_id' => $schedule->id, 'date' => now()->subDays($day + 1)->toDateString(),
                    'status' => ['present', 'present', 'absent', 'late'][($i + $day) % 4]]);
            }
            if ($i < 5) {
                $enrollment->student->payments()->create(['or_number' => 'OR-DEMO-S'.($i + 1), 'amount' => $i < 3 ? 8500 : 4000, 'method' => 'cash',
                    'paid_at' => now()->subDays($i * 20)->toDateString(), 'received_by' => 2]);
            }
        }

        // ------------------------------------------------ Driving School
        $tdc = Course::create(['code' => 'TDC', 'name' => 'Theoretical Driving Course', 'description' => '15-hour classroom course (LTO requirement).', 'required_sessions' => 5, 'fee' => 1500]);
        $pdc = Course::create(['code' => 'PDC', 'name' => 'Practical Driving Course', 'description' => '8-hour behind-the-wheel course.', 'required_sessions' => 4, 'fee' => 3500]);

        $instructor  = Instructor::create(['user_id' => $instrUser->id, 'name' => 'Ramon Instructor', 'license_no' => 'N01-23-000111', 'contact_no' => '09171112222', 'specialization' => 'Both']);
        $instructor2 = Instructor::create(['name' => 'Elena Castillo', 'license_no' => 'N01-23-000222', 'contact_no' => '09173334444', 'specialization' => 'PDC']);

        $car1 = Vehicle::create(['plate_no' => 'DAV 1234', 'model' => 'Toyota Vios', 'type' => 'Car', 'transmission' => 'Manual']);
        Vehicle::create(['plate_no' => 'DAV 5678', 'model' => 'Mitsubishi Mirage', 'type' => 'Car', 'transmission' => 'Automatic']);
        Vehicle::create(['plate_no' => 'DM 9012', 'model' => 'Honda TMX 155', 'type' => 'Motorcycle', 'transmission' => 'Manual', 'status' => 'maintenance']);

        $traineeNames = [['Ana', 'Reyes'], ['Mark', 'Villanueva'], ['Joy', 'Navarro'], ['Paolo', 'Aquino']];
        foreach ($traineeNames as $i => $n) {
            $trainee = Trainee::create([
                'user_id' => $i === 0 ? $traineeUsr->id : null, 'first_name' => $n[0], 'last_name' => $n[1], 'sex' => $i % 2 ? 'Male' : 'Female',
                'address' => 'Digos City', 'contact_no' => '0928000'.str_pad((string) $i, 4, '0', STR_PAD_LEFT), 'email' => $i === 0 ? 'trainee@dctr.test' : null,
                'license_no' => 'SP-'.(1000 + $i),
            ]);

            $course = $i % 2 ? $pdc : $tdc;
            $ce = CourseEnrollment::create(['trainee_id' => $trainee->id, 'course_id' => $course->id, 'enrolled_at' => now()->subDays(14)->toDateString(), 'status' => 'ongoing']);
            $trainee->charges()->create(['description' => 'Course fee - '.$course->code, 'amount' => $course->fee]);

            // two completed sessions in the past, two scheduled ahead
            foreach ([-6, -3, 1 + $i, 4 + $i] as $k => $offset) {
                $past = $offset < 0;
                TrainingSession::create([
                    'course_enrollment_id' => $ce->id, 'instructor_id' => $i % 2 ? $instructor2->id : $instructor->id,
                    'vehicle_id' => $course->code === 'PDC' ? $car1->id : null,
                    'session_date' => now()->addDays($offset)->toDateString(),
                    'start_time' => sprintf('%02d:00:00', 9 + $i * 2), 'end_time' => sprintf('%02d:00:00', 11 + $i * 2),
                    'status' => $past ? 'completed' : 'scheduled', 'attendance' => $past ? 'present' : null, 'score' => $past ? rand(78, 95) : null,
                ]);
            }
            if ($i === 0) {
                $trainee->payments()->create(['or_number' => 'OR-DEMO-T1', 'amount' => 1000, 'method' => 'gcash', 'paid_at' => now()->subDays(10)->toDateString(), 'received_by' => 3]);
            }
        }

        Announcement::create(['title' => 'Welcome to the new system', 'body' => 'Records, schedules and payments are now managed in one place.', 'audience' => 'all', 'created_by' => 1]);
        Announcement::create(['title' => 'Midterm examinations', 'body' => 'Midterm exams for Grade 11 start next month. Please settle outstanding balances.', 'audience' => 'shs', 'created_by' => 2]);
        Announcement::create(['title' => 'PDC schedule reminder', 'body' => 'Trainees must arrive 15 minutes before their practical session.', 'audience' => 'driving', 'created_by' => 3]);
    }
}
