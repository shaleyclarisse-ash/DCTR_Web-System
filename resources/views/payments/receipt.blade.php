@extends('layouts.app')
@section('title', 'Receipt '.$payment->or_number)
@section('content')
<div class="page-head no-print"><h1>Receipt</h1>
    <button class="btn btn-accent" onclick="window.print()"><i class="bi bi-printer"></i> Print</button></div>
<div class="receipt">
    <div class="text-center mb-3">
        <h2 class="h5 fw-bold mb-0">DCTR College &amp; Driving School, Inc.</h2>
        <div class="text-secondary small">Digos City &middot; Official payment receipt (acknowledgment)</div>
        <div class="roadline" style="margin:.8rem auto;width:80px"></div>
    </div>
    <table class="table table-sm">
        <tr><th>OR number</th><td>{{ $payment->or_number }}</td></tr>
        <tr><th>Date</th><td>{{ $payment->paid_at->format('F d, Y') }}</td></tr>
        <tr><th>Received from</th><td>{{ $owner->full_name }} ({{ ucfirst($payment->payable_type) }})</td></tr>
        <tr><th>Amount</th><td class="fw-bold">₱{{ number_format($payment->amount, 2) }}</td></tr>
        <tr><th>Method</th><td>{{ strtoupper($payment->method) }}</td></tr>
        <tr><th>Remaining balance</th><td>₱{{ number_format($owner->balance, 2) }}</td></tr>
        <tr><th>Received by</th><td>{{ $payment->receiver->name ?? '-' }}</td></tr>
        @if ($payment->remarks)<tr><th>Remarks</th><td>{{ $payment->remarks }}</td></tr>@endif
    </table>
</div>
@endsection
