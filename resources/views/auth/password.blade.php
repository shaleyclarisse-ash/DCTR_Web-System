@extends('layouts.app')
@section('title', 'Change password')
@section('content')
<div class="page-head"><h1>Change password</h1></div>
<form method="POST" action="{{ route('password.update') }}" class="panel panel-pad" style="max-width:460px">
    @csrf
    @foreach ([['current_password', 'Current password'], ['password', 'New password (min. 8 characters)'], ['password_confirmation', 'Confirm new password']] as [$name, $label])
        <div class="mb-3">
            <label class="form-label" for="{{ $name }}">{{ $label }}</label>
            <input id="{{ $name }}" type="password" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror" required>
            @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endforeach
    <button class="btn btn-accent">Update password</button>
</form>
@endsection
