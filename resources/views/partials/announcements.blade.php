@if ($announcements->isNotEmpty())
<div class="panel panel-pad mb-3">
    <h2>Announcements</h2>
    @foreach ($announcements as $a)
        <div class="note mb-2"><strong>{{ $a->title }}</strong> <span class="text-secondary small">{{ $a->created_at->diffForHumans() }}</span><br>{{ $a->body }}</div>
    @endforeach
</div>
@endif
