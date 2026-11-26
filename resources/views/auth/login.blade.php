<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in | DCTR SMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
    <section class="login-side">
        <img class="login-logo" src="{{ asset('images/dctr-logo.jpg') }}" alt="DCTR College &amp; Driving School logo">
        <div class="roadline" style="margin:0 0 1.5rem;width:96px"></div>
        <h1>One system for the classroom and the road.</h1>
        <p>Integrated School and Driving Training Management System of DCTR College &amp; Driving School, Inc., Digos City.</p>
    </section>
    <section class="login-form">
        <form class="box" method="POST" action="{{ route('login') }}">
            @csrf
            <h2 class="h4 fw-bold mb-4">Log in to your account</h2>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input id="password" type="password" name="password" class="form-control" required>
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label" for="remember">Keep me logged in</label>
            </div>
            <button class="btn btn-accent w-100 py-2">Log in</button>
        </form>
    </section>
</div>
</body>
</html>
