<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in | DCTR SMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="login-page">
<div class="login-wrap">
    <section class="login-side">
        <div class="login-brand">
            <img class="login-logo" src="{{ asset('images/dctr-logo.jpg') }}" alt="Dela Calzada Tanchado Rioja College and Driving School seal">
            <div class="login-brand-name">DCTR <span>COLLEGE &amp; DRIVING SCHOOL</span></div>
        </div>
        <div class="login-brand-copy">
            <div class="login-kicker">Digos City · Since 2019</div>
            <h1>Learn with confidence.<br>Drive toward what’s next.</h1>
            <p>Welcome to the DCTR school and driving training management system.</p>
        </div>
        <div class="login-departments" aria-label="DCTR departments">
            <span><i class="bi bi-mortarboard" aria-hidden="true"></i> Senior High School</span>
            <span><i class="bi bi-sign-turn-right" aria-hidden="true"></i> Driving School</span>
        </div>
        <div class="login-road" aria-hidden="true"><span></span></div>
    </section>
    <section class="login-form">
        <form class="box" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="login-form-heading">
                <div class="login-form-icon" aria-hidden="true"><i class="bi bi-person-lock"></i></div>
                <div class="login-kicker">DCTR portal</div>
                <h2 class="h3 fw-bold mb-2">Welcome back</h2>
                <p>Sign in to continue to your account.</p>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="username" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label" for="remember">Keep me logged in</label>
            </div>
            <button class="btn btn-accent w-100 py-2 login-submit">Log in <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></button>
            <div class="login-secure"><i class="bi bi-shield-lock" aria-hidden="true"></i> Sign in with your DCTR account</div>
        </form>
    </section>
</div>
</body>
</html>
