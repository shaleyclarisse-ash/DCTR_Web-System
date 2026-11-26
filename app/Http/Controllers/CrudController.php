<?php

namespace App\Http\Controllers;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * Base class for the simple "list / add / edit / delete" screens.
 * A child controller only describes its model, table columns and form fields;
 * the generic views resources/views/crud/{index,form}.blade.php do the rest.
 */
abstract class CrudController extends Controller
{
    abstract protected function model(): string;                 // Student::class
    abstract protected function title(): string;                 // 'Students'
    abstract protected function route(): string;                 // 'students'  (route name prefix)
    abstract protected function columns(): array;                // ['Label' => 'attribute' | fn ($row) => ...]
    abstract protected function fields(?Model $record = null): array;

    // ---- optional hooks ---------------------------------------------------
    protected function search(): array { return []; }            // columns used by the search box
    protected function with(): array { return []; }              // eager loads
    protected function counts(): array { return []; }            // withCount relations
    protected function hasShow(): bool { return false; }
    protected function order(Builder $q): Builder { return $q->latest('id'); }
    protected function searchable(): bool { return count($this->search()) > 0; }
    protected function validateExtra(array $data, ?Model $record): void {}
    protected function saving(array $data, ?Model $record): array { return $data; }
    protected function saved(Model $record, array $data, bool $created, Request $request): ?string { return null; }
    protected function deleteGuard(Model $record): ?string { return null; }

    protected function applySearch(Builder $q, string $term): void
    {
        $q->where(function ($w) use ($term) {
            foreach ($this->search() as $column) {
                $w->orWhere($column, 'like', "%{$term}%");
            }
        });
    }

    // ---- helpers ----------------------------------------------------------
    /** Normalise "8:00" / "08:00" to "08:00:00" so time comparisons are reliable. */
    protected function hms(string $time): string
    {
        return Carbon::parse($time)->format('H:i:s');
    }

    /** Coloured status label that can be used inside a table column. */
    protected function badge(?string $text): HtmlString
    {
        $key = strtolower((string) $text);
        $color = match (true) {
            in_array($key, ['active', 'enrolled', 'completed', 'available', 'present', 'graduated']) => 'success',
            in_array($key, ['inactive', 'dropped', 'cancelled', 'absent', 'unavailable', 'no_show', 'maintenance']) => 'danger',
            in_array($key, ['scheduled', 'ongoing']) => 'primary',
            default => 'secondary',
        };

        return new HtmlString('<span class="badge text-bg-'.$color.'">'.e(Str::headline((string) $text)).'</span>');
    }

    private function cfg(): array
    {
        return [
            'title'      => $this->title(),
            'singular'   => Str::singular($this->title()),
            'route'      => $this->route(),
            'columns'    => $this->columns(),
            'searchable' => $this->searchable(),
            'show'       => $this->hasShow(),
        ];
    }

    private function resolvedFields(?Model $record): array
    {
        return array_map(function ($f) {
            if (isset($f['options']) && $f['options'] instanceof Closure) {
                $f['options'] = ($f['options'])();
            }
            return $f;
        }, $this->fields($record));
    }

    private function virtualNames(?Model $record): array
    {
        return collect($this->resolvedFields($record))->where('virtual', true)->pluck('name')->all();
    }

    private function rules(?Model $record): array
    {
        $table = app($this->model())->getTable();
        $rules = [];

        foreach ($this->resolvedFields($record) as $f) {
            if (in_array(($f['type'] ?? 'text'), ['section', 'age'], true)) {
                continue;
            }

            $r = $f['rules'] ?? 'nullable';
            $r = is_array($r) ? $r : explode('|', $r);

            if (($f['type'] ?? 'text') === 'checkboxes') {
                $rules[$f['name']] = $r;
                $rules[$f['name'].'.*'] = [Rule::in(array_keys($f['options'] ?? []))];
                continue;
            }

            if (($f['type'] ?? 'text') === 'select' && isset($f['options'])) {
                $r[] = Rule::in(array_keys($f['options']));
            }
            if (! empty($f['unique'])) {
                $r[] = Rule::unique($table, $f['name'])->ignore($record?->getKey());
            }
            $rules[$f['name']] = $r;
        }

        return $rules;
    }

    private function validatedData(Request $request, ?Model $record): array
    {
        foreach ($this->resolvedFields($record) as $field) {
            if (($field['type'] ?? 'text') === 'checkboxes' && ! $request->exists($field['name'])) {
                $request->merge([$field['name'] => []]);
            }
        }

        return $request->validate($this->rules($record));
    }

    // ---- actions ----------------------------------------------------------
    public function index(Request $request)
    {
        $term  = trim((string) $request->query('q', ''));
        $class = $this->model();
        $query = $class::with($this->with());

        if ($this->counts()) {
            $query->withCount($this->counts());
        }
        if ($term !== '') {
            $this->applySearch($query, $term);
        }

        $rows = $this->order($query)->paginate(15)->withQueryString();

        return view('crud.index', ['cfg' => $this->cfg(), 'rows' => $rows, 'term' => $term]);
    }

    public function create()
    {
        $class = $this->model();

        return view('crud.form', [
            'cfg' => $this->cfg(), 'record' => new $class, 'fields' => $this->resolvedFields(null),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request, null);
        $this->validateExtra($data, null);

        $attrs = Arr::except($this->saving($data, null), $this->virtualNames(null));
        $class = $this->model();
        $record = $class::create($attrs);
        $extra = $this->saved($record, $data, true, $request);

        return redirect()->route($this->route().'.index')
            ->with('success', trim(Str::singular($this->title()).' added successfully. '.$extra));
    }

    public function edit($id)
    {
        $class  = $this->model();
        $record = $class::findOrFail($id);

        return view('crud.form', [
            'cfg' => $this->cfg(), 'record' => $record, 'fields' => $this->resolvedFields($record),
        ]);
    }

    public function update(Request $request, $id)
    {
        $class  = $this->model();
        $record = $class::findOrFail($id);

        $data = $this->validatedData($request, $record);
        $this->validateExtra($data, $record);

        $attrs = Arr::except($this->saving($data, $record), $this->virtualNames($record));
        $record->update($attrs);
        $extra = $this->saved($record, $data, false, $request);

        return redirect()->route($this->route().'.index')
            ->with('success', trim(Str::singular($this->title()).' updated successfully. '.$extra));
    }

    public function destroy($id)
    {
        $class  = $this->model();
        $record = $class::findOrFail($id);

        if ($message = $this->deleteGuard($record)) {
            return back()->with('error', $message);
        }

        try {
            $record->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'This record is still being used by other records and cannot be deleted.');
        }

        return back()->with('success', Str::singular($this->title()).' deleted.');
    }
}
