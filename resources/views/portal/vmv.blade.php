@extends('layouts.app')
@section('title', 'About DCTR')
@section('content')
<div class="page-head vmv-page-head">
    <div><h1>DCTR background, mission, vision &amp; values</h1><div class="sub">Official school background and VMV document</div></div>
    <a class="btn btn-ink" href="{{ asset('documents/dctr-background-vmv.pdf') }}" download>
        <i class="bi bi-download" aria-hidden="true"></i> Download PDF
    </a>
</div>

<div class="vmv-pdf panel">
    <iframe src="{{ asset('documents/dctr-background-vmv.pdf') }}#view=FitH" title="DCTR school background, mission, vision, and core values PDF">
        <p>Your browser can’t display the PDF. <a href="{{ asset('documents/dctr-background-vmv.pdf') }}">Download the DCTR background and VMV PDF</a>.</p>
    </iframe>
</div>
@endsection
