<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Model;

class AnnouncementController extends CrudController
{
    protected function model(): string { return Announcement::class; }
    protected function title(): string { return 'Announcements'; }
    protected function route(): string { return 'announcements'; }
    protected function search(): array { return ['title', 'body']; }
    protected function with(): array { return ['author']; }

    protected function columns(): array
    {
        return [
            'Title'     => 'title',
            'Audience'  => fn ($r) => ['all' => 'Everyone', 'shs' => 'Senior High School', 'driving' => 'Driving School'][$r->audience] ?? $r->audience,
            'Posted by' => fn ($r) => $r->author->name ?? '-',
            'Date'      => fn ($r) => $r->created_at->format('M d, Y'),
        ];
    }

    protected function fields(?Model $record = null): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'rules' => 'required|string|max:200'],
            ['name' => 'audience', 'label' => 'Audience', 'type' => 'select', 'rules' => 'required',
                'options' => ['all' => 'Everyone', 'shs' => 'Senior High School only', 'driving' => 'Driving School only']],
            ['name' => 'body', 'label' => 'Message', 'type' => 'textarea', 'rules' => 'required|string|max:2000', 'col' => 12],
        ];
    }

    protected function saving(array $data, ?Model $record): array
    {
        $data['created_by'] = $record?->created_by ?? auth()->id();

        return $data;
    }
}
