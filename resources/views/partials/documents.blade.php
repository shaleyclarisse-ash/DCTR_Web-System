{{-- expects $owner (Student|Trainee) and $type ('student'|'trainee') --}}
<div class="panel mt-3">
    <div class="panel-head"><h2>Documents / requirements</h2></div>
    <div class="table-responsive"><table class="table">
        <tbody>
        @forelse ($owner->documents as $doc)
            <tr>
                <td><i class="bi bi-file-earmark"></i> <a href="{{ route('documents.download', $doc->id) }}">{{ $doc->title }}</a>
                    <div class="small text-secondary">{{ $doc->original_name }} &middot; {{ $doc->created_at->format('M d, Y') }}</div></td>
                <td class="text-end">
                    <form method="POST" action="{{ route('documents.destroy', $doc->id) }}" onsubmit="return confirm('Delete this document?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td class="empty">No documents uploaded yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <form method="POST" enctype="multipart/form-data" action="{{ route('documents.store', [$type, $owner->id]) }}" class="panel-pad border-top row g-2">
        @csrf
        <div class="col-md-4"><input name="title" class="form-control" placeholder="Document title (e.g. Birth certificate)" required></div>
        <div class="col-md-5"><input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required></div>
        <div class="col-md-3"><button class="btn btn-ink w-100"><i class="bi bi-upload"></i> Upload</button></div>
        @if ($errors->has('file') || $errors->has('title'))
            <div class="text-danger small">{{ $errors->first('file') ?: $errors->first('title') }}</div>
        @endif
    </form>
</div>
