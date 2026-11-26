@extends('layouts.app')
@section('title', ($record->exists ? 'Edit ' : 'Add ').strtolower($cfg['singular']))
@section('content')
<div class="page-head">
    <h1>{{ $record->exists ? 'Edit' : 'Add' }} {{ strtolower($cfg['singular']) }}</h1>
    <a href="{{ route($cfg['route'].'.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" class="panel panel-pad crud-form"
      action="{{ $record->exists ? route($cfg['route'].'.update', $record->id) : route($cfg['route'].'.store') }}">
    @csrf
    @if ($record->exists) @method('PUT') @endif

    <div class="row g-3">
    @foreach ($fields as $f)
        @php
            $name = $f['name'];
            $type = $f['type'] ?? 'text';
            $val  = old($name, $record->{$name} ?? ($f['default'] ?? null));
            if ($val instanceof \DateTimeInterface) { $val = $val->format('Y-m-d'); }
            if ($type === 'time' && $val) { $val = substr($val, 0, 5); }
            if ($type === 'password') { $val = ''; }
        @endphp
        @if ($type === 'section')
            <div class="col-12"><h2 class="form-section">{{ $f['label'] }}</h2></div>
        @else
        <div class="col-md-{{ $f['col'] ?? 6 }}">
            @if ($type === 'age')
                <label class="form-label" for="{{ $name }}">{{ $f['label'] }}</label>
                <output id="{{ $name }}" class="form-control bg-light" data-age-for="{{ $f['birthdate'] }}">Enter birthdate</output>
            @elseif ($type === 'checkboxes')
                <label class="form-label d-block">{{ $f['label'] }}</label>
                <div class="row g-2">
                    @foreach ($f['options'] as $key => $label)
                        <div class="col-sm-6">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="{{ $name }}[]" value="{{ $key }}"
                                    @checked(in_array((string) $key, array_map('strval', is_array($val) ? $val : []), true))>
                                <span class="form-check-label">{{ $label }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            @elseif ($type === 'checkbox')
                <input type="hidden" name="{{ $name }}" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1" @checked((bool) $val)>
                    <label class="form-check-label" for="{{ $name }}">{{ $f['label'] }}</label>
                </div>
            @else
                <label class="form-label" for="{{ $name }}">{{ $f['label'] }}</label>
                @if ($type === 'select')
                    <select id="{{ $name }}" name="{{ $name }}" class="form-select @error($name) is-invalid @enderror" @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror>
                        <option value="">{{ $f['blank'] ?? '-- Select --' }}</option>
                        @foreach ($f['options'] as $k => $label)
                            <option value="{{ $k }}" @selected((string) $val === (string) $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                @elseif ($type === 'textarea')
                    <textarea id="{{ $name }}" name="{{ $name }}" rows="3" class="form-control @error($name) is-invalid @enderror" @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror>{{ $val }}</textarea>
                @else
                    <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $val }}"
                           @if (isset($f['step'])) step="{{ $f['step'] }}" @endif
                           @if ($type === 'number') min="0" @endif
                           class="form-control @error($name) is-invalid @enderror" autocomplete="off" @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror>
                @endif
            @endif
            @error($name)<div id="{{ $name }}-error" class="text-danger small mt-1" role="alert">{{ $message }}</div>@enderror
        </div>
        @endif
    @endforeach
    </div>

    <div class="mt-4 d-flex gap-2">
        <button class="btn btn-accent px-4">Save</button>
        <a href="{{ route($cfg['route'].'.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@push('scripts')
<script>
document.querySelectorAll('[data-age-for]').forEach((ageOutput) => {
    const birthdate = document.getElementById(ageOutput.dataset.ageFor);
    const updateAge = () => {
        if (!birthdate?.value) {
            ageOutput.textContent = 'Enter birthdate';
            return;
        }

        const date = new Date(`${birthdate.value}T00:00:00`);
        const today = new Date();
        let years = today.getFullYear() - date.getFullYear();
        if (today.getMonth() < date.getMonth() || (today.getMonth() === date.getMonth() && today.getDate() < date.getDate())) years--;
        ageOutput.textContent = years >= 0 ? `${years} years` : 'Check birthdate';
    };

    birthdate?.addEventListener('input', updateAge);
    birthdate?.addEventListener('change', updateAge);
    updateAge();
});
</script>
@endpush
@endsection
