@extends('layouts.app')
@section('title', 'Grades')
@section('content')
<div class="page-head"><div><h1>Grades</h1><div class="sub">Enter quarterly grades per class. Passing grade is 75.</div></div></div>

<form method="GET" class="panel panel-pad mb-3 d-flex gap-2 flex-wrap">
    <select name="class" class="form-select" style="max-width:640px" onchange="this.form.submit()">
        <option value="">-- Select a class (section | subject) --</option>
        @foreach ($classes as $k => $label)
            <option value="{{ $k }}" @selected($key === (string) $k)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="btn btn-ink">Load</button>
</form>

@if ($key && $rows->isNotEmpty())
<form method="POST" action="{{ route('grades.store') }}">
    @csrf
    <input type="hidden" name="class" value="{{ $key }}">
    <div class="panel table-responsive">
        <table class="table">
            <thead><tr><th>Student</th><th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th><th>Final average</th><th>Remarks</th></tr></thead>
            <tbody>
            @foreach ($rows as $e)
                <tr data-row>
                    <td>{{ $e->student->full_name }}</td>
                    @foreach ([1, 2, 3, 4] as $p)
                        <td style="width:95px">
                            <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm" data-q
                                   name="grades[{{ $e->id }}][{{ $p }}]" value="{{ $grades[$e->id][$p]->grade ?? '' }}">
                        </td>
                    @endforeach
                    <td class="fw-semibold" data-avg>-</td>
                    <td data-remark></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <button class="btn btn-accent mt-3 px-4">Save grades</button>
</form>
@push('scripts')
<script>
document.querySelectorAll('[data-row]').forEach(row => {
    const calc = () => {
        const v = [...row.querySelectorAll('[data-q]')].map(i => parseFloat(i.value)).filter(n => !isNaN(n));
        const avg = v.length ? v.reduce((a, b) => a + b, 0) / v.length : null;
        row.querySelector('[data-avg]').textContent = avg === null ? '-' : avg.toFixed(2);
        const r = row.querySelector('[data-remark]');
        r.textContent = avg === null ? '' : (avg >= 75 ? 'Passed' : 'Needs assistance');
        r.className = avg === null ? '' : (avg >= 75 ? 'text-success' : 'text-danger');
    };
    row.addEventListener('input', calc); calc();
});
</script>
@endpush
@elseif ($key)
    <div class="panel empty">No enrolled students found in this section.</div>
@endif
@endsection
