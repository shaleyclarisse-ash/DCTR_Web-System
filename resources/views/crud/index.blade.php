@extends('layouts.app')
@section('title', $cfg['title'])
@section('content')
<div class="page-head">
    <h1>{{ $cfg['title'] }}</h1>
    <a href="{{ route($cfg['route'].'.create') }}" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Add {{ strtolower($cfg['singular']) }}</a>
</div>

@if ($cfg['route'] === 'enrollments')
    <div class="mb-3">
        <a href="{{ asset('forms/sshs-enrolment-form.pdf') }}" class="btn btn-sm btn-outline-secondary" download>
            <i class="bi bi-file-earmark-arrow-down"></i> Download SSHS enrolment form
        </a>
    </div>
@elseif ($cfg['route'] === 'course-enrollments')
    <div class="mb-3">
        <a href="{{ asset('forms/lto-registration-form-2026.pdf') }}" class="btn btn-sm btn-outline-secondary" download>
            <i class="bi bi-file-earmark-arrow-down"></i> Download 2026 LTO registration form
        </a>
    </div>
@endif

@if ($cfg['searchable'])
    <form method="GET" class="search-bar">
        <div class="input-group">
            <label class="visually-hidden" for="record-search">Search {{ strtolower($cfg['title']) }}</label>
            <input id="record-search" type="search" name="q" value="{{ $term }}" class="form-control" placeholder="Search {{ strtolower($cfg['title']) }}...">
            <button class="btn btn-ink"><i class="bi bi-search" aria-hidden="true"></i><span class="ms-1">Search</span></button>
        </div>
    </form>
@endif

<p class="small text-secondary mb-2" role="status">{{ $rows->total() }} {{ $rows->total() === 1 ? 'record' : 'records' }}</p>
<div class="panel table-responsive mobile-card-table">
    <table class="table table-hover">
        <thead>
            <tr>
                @foreach ($cfg['columns'] as $label => $col)<th scope="col">{{ $label }}</th>@endforeach
                <th scope="col" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            <tr>
                @foreach ($cfg['columns'] as $label => $col)
                    <td data-label="{{ $label }}">{{ $col instanceof \Closure ? $col($row) : data_get($row, $col) }}</td>
                @endforeach
                <td class="text-end text-nowrap actions-cell" aria-label="Actions">
                    @if ($cfg['show'])
                        <a href="{{ route($cfg['route'].'.show', $row->id) }}" class="btn btn-sm btn-outline-secondary" aria-label="View record {{ $row->id }}" title="View"><i class="bi bi-eye" aria-hidden="true"></i></a>
                    @endif
                    <a href="{{ route($cfg['route'].'.edit', $row->id) }}" class="btn btn-sm btn-outline-primary" aria-label="Edit record {{ $row->id }}" title="Edit"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                    <form method="POST" action="{{ route($cfg['route'].'.destroy', $row->id) }}" class="d-inline"
                          onsubmit="return confirm('Delete this record? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" aria-label="Delete record {{ $row->id }}" title="Delete"><i class="bi bi-trash" aria-hidden="true"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ count($cfg['columns']) + 1 }}" class="empty">No records found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $rows->links() }}</div>
@endsection
