@extends('layouts.app')
@section('title', 'Payments')
@section('content')
@php $user = auth()->user(); @endphp
<div class="page-head">
    <div><h1>Payments and balances</h1><div class="sub">{{ $type === 'student' ? 'Senior High School students' : 'Driving school trainees' }}</div></div>
    @if ($user->role === 'admin')
        <div class="btn-group">
            <a class="btn btn-{{ $type === 'student' ? 'ink' : 'outline-secondary' }}" href="{{ route('payments.index', ['type' => 'student']) }}">Students</a>
            <a class="btn btn-{{ $type === 'trainee' ? 'ink' : 'outline-secondary' }}" href="{{ route('payments.index', ['type' => 'trainee']) }}">Trainees</a>
        </div>
    @endif
</div>

<div class="stat-strip">
    <div class="stat"><div class="num">₱{{ number_format($charged, 2) }}</div><div class="lbl">Total charges</div></div>
    <div class="stat"><div class="num">₱{{ number_format($collected, 2) }}</div><div class="lbl">Total collected</div></div>
    <div class="stat warn"><div class="num">₱{{ number_format($charged - $collected, 2) }}</div><div class="lbl">Outstanding</div></div>
</div>

<form method="GET" class="row g-2 mb-3" style="max-width:640px">
    <input type="hidden" name="type" value="{{ $type }}">
    <div class="col-sm-6"><label class="visually-hidden" for="payment-search">Search accounts by name</label><input id="payment-search" type="search" name="q" value="{{ $term }}" class="form-control" placeholder="Search name..."></div>
    <div class="col-sm-4">
        <select name="filter" class="form-select" onchange="this.form.submit()">
            <option value="">All accounts</option>
            <option value="balance" @selected($filter === 'balance')>With balance</option>
            <option value="settled" @selected($filter === 'settled')>Settled</option>
        </select>
    </div>
    <div class="col-sm-2"><button class="btn btn-ink w-100"><i class="bi bi-search" aria-hidden="true"></i><span class="ms-1">Search</span></button></div>
</form>

<p class="small text-secondary mb-2" role="status">{{ $accounts->total() }} {{ $accounts->total() === 1 ? 'account' : 'accounts' }}</p>
<div class="panel table-responsive">
    <table class="table table-hover">
        <thead><tr><th scope="col">Name</th><th scope="col" class="text-end">Charges</th><th scope="col" class="text-end">Paid</th><th scope="col" class="text-end">Balance</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        @forelse ($accounts as $a)
            @php $balance = (float) $a->total_charges - (float) $a->total_paid; @endphp
            <tr>
                <td>{{ $a->full_name }}</td>
                <td class="text-end">₱{{ number_format($a->total_charges, 2) }}</td>
                <td class="text-end">₱{{ number_format($a->total_paid, 2) }}</td>
                <td class="text-end fw-semibold">₱{{ number_format($balance, 2) }}</td>
                <td><span class="badge text-bg-{{ $balance > 0 ? 'warning' : 'success' }}">{{ $balance > 0 ? 'With balance' : 'Settled' }}</span></td>
                <td class="text-end"><a class="btn btn-sm btn-outline-primary" aria-label="Open account for {{ $a->full_name }}" href="{{ route('payments.account', [$type, $a->id]) }}">Open account</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="empty">No accounts found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $accounts->links() }}</div>
@endsection
