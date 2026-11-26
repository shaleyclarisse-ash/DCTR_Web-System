@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="page-head"><div><h1>Reports</h1><div class="sub">View, print or export to CSV (opens in Excel).</div></div></div>
<div class="row g-3">
    @foreach ($reports as $key => [$title, $desc])
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('reports.show', $key) }}" class="panel panel-pad d-block text-decoration-none text-reset h-100">
                <h2 class="mb-1">{{ $title }}</h2>
                <div class="text-secondary">{{ $desc }}</div>
            </a>
        </div>
    @endforeach
</div>
@endsection
