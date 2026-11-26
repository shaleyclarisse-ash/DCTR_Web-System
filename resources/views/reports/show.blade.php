@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="page-head">
    <div><h1>{{ $title }}</h1><div class="sub">DCTR College &amp; Driving School, Inc. &middot; generated {{ now()->format('M d, Y h:i A') }}</div></div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Back</a>
        <button class="btn btn-ink" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
        <a class="btn btn-accent" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"><i class="bi bi-filetype-csv"></i> Export CSV</a>
    </div>
</div>

@if ($filters)
<form method="GET" class="row g-2 mb-3 no-print" style="max-width:720px">
    @foreach ($filters as $f)
        <div class="col-sm">
            <label class="form-label">{{ $f['label'] }}</label>
            @if ($f['type'] === 'select')
                <select name="{{ $f['name'] }}" class="form-select">
                    <option value="">All</option>
                    @foreach ($f['options'] as $k => $v)<option value="{{ $k }}" @selected((string) $f['value'] === (string) $k)>{{ $v }}</option>@endforeach
                </select>
            @else
                <input type="{{ $f['type'] }}" name="{{ $f['name'] }}" value="{{ $f['value'] }}" class="form-control">
            @endif
        </div>
    @endforeach
    <div class="col-sm-auto d-flex align-items-end"><button class="btn btn-ink">Apply</button></div>
</form>
@endif

<div class="panel table-responsive">
    <table class="table table-sm">
        <thead><tr>@foreach ($headers as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
        @forelse ($rows as $row)
            <tr @if (($row[3] ?? '') === 'TOTAL') class="fw-bold" @endif>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
        @empty
            <tr><td colspan="{{ count($headers) }}" class="empty">No data for this report.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="text-secondary small mt-2">{{ count($rows) }} row(s)</div>
@endsection
