@extends('layouts.app')
@section('title', 'Account - '.$owner->full_name)
@section('content')
@php $charged = $charges->sum('amount'); $paid = $payments->sum('amount'); $balance = round($charged - $paid, 2); @endphp
<div class="page-head">
    <div><h1>{{ $owner->full_name }}</h1><div class="sub">{{ ucfirst($type) }} account</div></div>
    <a href="{{ route('payments.index', ['type' => $type]) }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="stat-strip">
    <div class="stat"><div class="num">₱{{ number_format($charged, 2) }}</div><div class="lbl">Total charges</div></div>
    <div class="stat"><div class="num">₱{{ number_format($paid, 2) }}</div><div class="lbl">Total paid</div></div>
    <div class="stat {{ $balance > 0 ? 'warn' : '' }}"><div class="num">₱{{ number_format($balance, 2) }}</div><div class="lbl">{{ $balance > 0 ? 'Remaining balance' : 'Account settled' }}</div></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-head"><h2>Charges</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>Description</th><th>Due</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                @forelse ($charges as $c)
                    <tr><td>{{ $c->description }}</td><td>{{ $c->due_date?->format('M d, Y') ?? '-' }}</td><td class="text-end">₱{{ number_format($c->amount, 2) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="empty">No charges.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            <form method="POST" action="{{ route('payments.charge', [$type, $owner->id]) }}" class="panel-pad border-top row g-2">
                @csrf
                <div class="col-12 fw-semibold small">Add charge</div>
                <div class="col-md-5"><input name="description" class="form-control" placeholder="Description" required value="{{ old('description') }}"></div>
                <div class="col-md-3"><input name="amount" type="number" step="0.01" min="0.01" class="form-control" placeholder="Amount" required value="{{ old('amount') }}"></div>
                <div class="col-md-4"><input name="due_date" type="date" class="form-control" title="Due date (optional)"></div>
                <div class="col-12"><button class="btn btn-sm btn-ink">Add charge</button></div>
                @if ($errors->hasAny(['description', 'amount', 'due_date']))<div class="text-danger small">{{ $errors->first() }}</div>@endif
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-head"><h2>Payments</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>OR no.</th><th>Date</th><th>Method</th><th class="text-end">Amount</th><th></th></tr></thead>
                <tbody>
                @forelse ($payments as $p)
                    <tr><td>{{ $p->or_number }}</td><td>{{ $p->paid_at->format('M d, Y') }}</td><td>{{ strtoupper($p->method) }}</td>
                        <td class="text-end">₱{{ number_format($p->amount, 2) }}</td>
                        <td class="text-end"><a target="_blank" href="{{ route('payments.receipt', $p->id) }}" title="Print receipt"><i class="bi bi-printer"></i></a></td></tr>
                @empty
                    <tr><td colspan="5" class="empty">No payments yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            @if ($balance > 0)
            <form method="POST" action="{{ route('payments.pay', [$type, $owner->id]) }}" class="panel-pad border-top row g-2">
                @csrf
                <div class="col-12 fw-semibold small">Record payment</div>
                <div class="col-md-4"><input name="or_number" class="form-control" placeholder="OR number" required value="{{ old('or_number', $nextOr) }}"></div>
                <div class="col-md-4"><input name="amount" type="number" step="0.01" min="0.01" max="{{ $balance }}" class="form-control" required value="{{ old('amount', $balance) }}"></div>
                <div class="col-md-4"><input name="paid_at" type="date" class="form-control" required value="{{ old('paid_at', now()->toDateString()) }}"></div>
                <div class="col-md-4">
                    <select name="method" class="form-select">
                        @foreach (['cash' => 'Cash', 'gcash' => 'GCash', 'bank' => 'Bank transfer', 'other' => 'Other'] as $k => $v)
                            <option value="{{ $k }}" @selected(old('method') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8"><input name="remarks" class="form-control" placeholder="Remarks (optional)" value="{{ old('remarks') }}"></div>
                <div class="col-12"><button class="btn btn-accent">Record payment</button></div>
                @if ($errors->hasAny(['or_number', 'amount', 'paid_at', 'method']))<div class="text-danger small">{{ $errors->first() }}</div>@endif
            </form>
            @else
                <div class="panel-pad border-top text-success"><i class="bi bi-check-circle"></i> This account is fully settled.</div>
            @endif
        </div>
    </div>
</div>
@endsection
