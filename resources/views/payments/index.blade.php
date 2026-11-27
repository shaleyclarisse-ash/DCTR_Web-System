@extends('layouts.app')
@section('title', 'Payments')
@section('content')
@php $user = auth()->user(); @endphp
<div class="page-head">
    <div><h1>Payments and balances</h1><div class="sub">{{ $type === 'student' ? 'Senior High School students' : 'Driving school trainees' }}</div></div>
    @if ($user->role === 'admin')
        <nav class="btn-group" aria-label="Payment account type">
            <a class="btn btn-{{ $type === 'student' ? 'ink' : 'outline-primary' }}" href="{{ route('payments.index', ['type' => 'student']) }}" @if ($type === 'student') aria-current="page" @endif>Students</a>
            <a class="btn btn-{{ $type === 'trainee' ? 'ink' : 'outline-primary' }}" href="{{ route('payments.index', ['type' => 'trainee']) }}" @if ($type === 'trainee') aria-current="page" @endif>Trainees</a>
        </nav>
    @endif
</div>

<div class="stat-strip dashboard-stat-strip">
    <div class="stat"><div class="num">₱{{ number_format($charged, 2) }}</div><div class="lbl">Total charges</div></div>
    <div class="stat"><div class="num">₱{{ number_format($collected, 2) }}</div><div class="lbl">Total collected</div></div>
    <div class="stat warn"><div class="num">₱{{ number_format($charged - $collected, 2) }}</div><div class="lbl">Outstanding</div></div>
</div>

<form method="GET" class="row g-2 mb-3" style="max-width:640px">
    <input type="hidden" name="type" value="{{ $type }}">
    <div class="col-sm"><label class="visually-hidden" for="payment-search">Search accounts by name</label><input id="payment-search" type="search" name="q" value="{{ $term }}" class="form-control" placeholder="Search name..."></div>
    <div class="col-sm-auto">
        <label class="visually-hidden" for="payment-status">Account status</label>
        <select id="payment-status" name="filter" class="form-select">
            <option value="">All accounts</option>
            <option value="balance" @selected($filter === 'balance')>With balance</option>
            <option value="settled" @selected($filter === 'settled')>Settled</option>
        </select>
    </div>
    <div class="col-sm-auto"><button class="btn btn-ink w-100"><i class="bi bi-search" aria-hidden="true"></i><span class="ms-1">Search</span></button></div>
</form>

<p class="small text-secondary mb-2" role="status">{{ $accounts->total() }} {{ $accounts->total() === 1 ? 'account' : 'accounts' }}</p>
<div class="panel table-responsive mobile-card-table">
    <table class="table table-hover">
        <thead><tr><th scope="col">Name</th><th scope="col" class="text-end">Charges</th><th scope="col" class="text-end">Paid</th><th scope="col" class="text-end">Balance</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        @forelse ($accounts as $a)
            @php $balance = round((float) $a->charges_total - (float) $a->payments_total, 2); @endphp
            <tr>
                <td data-label="Name">{{ $a->full_name }}</td>
                <td data-label="Charges" class="text-end">₱{{ number_format($a->charges_total ?? 0, 2) }}</td>
                <td data-label="Paid" class="text-end">₱{{ number_format($a->payments_total ?? 0, 2) }}</td>
                <td data-label="Balance" class="text-end fw-semibold">₱{{ number_format($balance, 2) }}</td>
                <td data-label="Status"><span class="badge text-bg-{{ $balance > 0 ? 'warning' : 'success' }}">{{ $balance > 0 ? 'With balance' : 'Settled' }}</span></td>
                <td class="text-end actions-cell"><a class="btn btn-sm btn-outline-primary" aria-label="Open account for {{ $a->full_name }}" href="{{ route('payments.account', [$type, $a->id]) }}">Open account</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="empty">No accounts found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $accounts->links() }}</div>
@endsection
