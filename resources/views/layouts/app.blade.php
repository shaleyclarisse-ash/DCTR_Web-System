<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | DCTR SMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
@php
    $me   = auth()->user();
    $role = $me->role;
    $portalRoles = ['student', 'trainee', 'teacher', 'instructor'];

    // [label, icon, route name, active pattern, roles allowed]  (the administrator sees everything except personal portals)
    $nav = [
        '' => [
            ['Dashboard', 'bi-speedometer2', 'dashboard', 'dashboard', ['shs_staff', 'driving_staff']],
            ['My portal', 'bi-person-badge', 'portal.student', 'portal.student', ['student']],
            ['My portal', 'bi-person-badge', 'portal.trainee', 'portal.trainee', ['trainee']],
            ['My classes', 'bi-calendar3', 'portal.teacher', 'portal.teacher', ['teacher']],
            ['My sessions', 'bi-car-front', 'portal.instructor', 'portal.instructor', ['instructor']],
        ],
        'Senior High School' => [
            ['Students', 'bi-people', 'students.index', 'students.*', ['shs_staff']],
            ['Enrollments', 'bi-journal-check', 'enrollments.index', 'enrollments.*', ['shs_staff']],
            ['Sections', 'bi-diagram-3', 'sections.index', 'sections.*', ['shs_staff']],
            ['Subjects', 'bi-book', 'subjects.index', 'subjects.*', ['shs_staff']],
            ['Class schedules', 'bi-calendar-week', 'class-schedules.index', 'class-schedules.*', ['shs_staff']],
            ['Grades', 'bi-award', 'grades.index', 'grades.*', ['shs_staff', 'teacher']],
            ['Attendance', 'bi-clipboard-check', 'attendance.index', 'attendance.*', ['shs_staff', 'teacher']],
        ],
        'Driving School' => [
            ['Trainees', 'bi-person-vcard', 'trainees.index', 'trainees.*', ['driving_staff']],
            ['Course enrollments', 'bi-journal-check', 'course-enrollments.index', 'course-enrollments.*', ['driving_staff']],
            ['Training sessions', 'bi-signpost-split', 'training-sessions.index', 'training-sessions.*', ['driving_staff']],
            ['Courses', 'bi-collection', 'courses.index', 'courses.*', ['driving_staff']],
            ['Instructors', 'bi-person-workspace', 'instructors.index', 'instructors.*', ['driving_staff']],
            ['Vehicles', 'bi-truck-front', 'vehicles.index', 'vehicles.*', ['driving_staff']],
        ],
        'Administration' => [
            ['Payments', 'bi-cash-coin', 'payments.index', 'payments.*', ['shs_staff', 'driving_staff']],
            ['Reports', 'bi-bar-chart-line', 'reports.index', 'reports.*', ['shs_staff', 'driving_staff']],
            ['Announcements', 'bi-megaphone', 'announcements.index', 'announcements.*', ['shs_staff', 'driving_staff']],
            ['Users', 'bi-shield-lock', 'users.index', 'users.*', []],
        ],
    ];
    $allowed = fn ($roles) => $role === 'admin'
        ? (empty($roles) || ! empty(array_diff($roles, $portalRoles)))
        : in_array($role, $roles, true);
@endphp

<div class="topbar d-lg-none">
    <div class="topbar-brand">
        <img class="school-logo-small" src="{{ asset('images/dctr-logo.jpg') }}" alt="DCTR College &amp; Driving School logo">
        <strong>DCTR SMS</strong>
    </div>
    <button class="btn btn-sm btn-outline-light" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-label="Toggle menu"><i class="bi bi-list"></i></button>
</div>

<aside id="sidebar" class="sidebar collapse d-lg-flex">
    <div class="brand">
        <img class="school-logo" src="{{ asset('images/dctr-logo.jpg') }}" alt="DCTR College &amp; Driving School logo">
        <div>
            <strong>DCTR</strong>
            <small>College &amp; Driving School, Inc.<br>School and Driving Training Management</small>
        </div>
    </div>
    <div class="roadline"></div>

    @foreach ($nav as $group => $items)
        @php $visible = array_filter($items, fn ($i) => $allowed($i[4])); @endphp
        @if ($visible)
            @if ($group)<div class="nav-group">{{ $group }}</div>@endif
            @foreach ($visible as [$label, $icon, $routeName, $pattern])
                <a class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($routeName) }}">
                    <i class="bi {{ $icon }}"></i> {{ $label }}
                </a>
            @endforeach
        @endif
    @endforeach

    <div class="me">
        <div class="fw-semibold">{{ $me->name }}</div>
        <div class="role mb-2">{{ $me->role_label }}</div>
        <a class="d-block text-decoration-none small text-white-50" href="{{ route('password.edit') }}"><i class="bi bi-key"></i> Change password</a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="btn btn-sm btn-outline-light w-100 mt-2"><i class="bi bi-box-arrow-right"></i> Log out</button>
        </form>
    </div>
</aside>

<main class="main">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}
            @if (session('receipt'))
                <a class="alert-link ms-2" target="_blank" href="{{ route('payments.receipt', session('receipt')) }}">Print receipt</a>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
