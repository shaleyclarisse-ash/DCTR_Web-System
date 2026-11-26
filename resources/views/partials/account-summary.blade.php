{{-- expects $owner, $type, $charges, $payments --}}
@php $charged = $charges->sum('amount'); $paid = $payments->sum('amount'); $balance = $charged - $paid; @endphp
<div class="panel mt-3">
    <div class="panel-head">
        <h2>Account</h2>
        @if (auth()->user()->hasRole('admin', 'shs_staff', 'driving_staff'))
            <a class="btn btn-sm btn-accent" href="{{ route('payments.account', [$type, $owner->id]) }}">Open account / record payment</a>
        @endif
    </div>
    <div class="stat-strip mb-0" style="border:0;border-radius:0">
        <div class="stat"><div class="num">₱{{ number_format($charged, 2) }}</div><div class="lbl">Total charges</div></div>
        <div class="stat"><div class="num">₱{{ number_format($paid, 2) }}</div><div class="lbl">Total paid</div></div>
        <div class="stat {{ $balance > 0 ? 'warn' : '' }}"><div class="num">₱{{ number_format($balance, 2) }}</div><div class="lbl">{{ $balance > 0 ? 'Remaining balance' : 'Settled' }}</div></div>
    </div>
</div>
