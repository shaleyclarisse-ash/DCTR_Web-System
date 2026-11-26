<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Document management: scanned requirements attached to a student or trainee. */
class DocumentController extends Controller
{
    private function authorizeType(string $type): void
    {
        abort_unless(in_array($type, ['student', 'trainee'], true), 404);

        $role = auth()->user()->role;
        abort_if($role === 'shs_staff' && $type !== 'student', 403);
        abort_if($role === 'driving_staff' && $type !== 'trainee', 403);
    }

    public function store(Request $request, string $type, int $id)
    {
        $this->authorizeType($type);

        $request->validate([
            'title' => 'required|string|max:150',
            'file'  => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        $class = Relation::getMorphedModel($type);
        $owner = $class::findOrFail($id);
        $file  = $request->file('file');

        $owner->documents()->create([
            'title'         => $request->title,
            'path'          => $file->store('documents'),      // private disk (storage/app)
            'original_name' => $file->getClientOriginalName(),
            'uploaded_by'   => auth()->id(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function download(Document $document)
    {
        $this->authorizeType($document->documentable_type);

        return Storage::download($document->path, $document->original_name);
    }

    public function destroy(Document $document)
    {
        $this->authorizeType($document->documentable_type);

        Storage::delete($document->path);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }
}
