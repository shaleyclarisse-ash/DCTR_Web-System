<section class="student-record student-announcement-feed" aria-labelledby="student-announcements-title">
    <header class="student-record-heading">
        <h1 id="student-announcements-title">Announcements</h1>
        <p>{{ $student->full_name }}@if ($enrollment) &middot; {{ $enrollment->section->label }}@endif</p>
    </header>

    @forelse ($announcements as $announcement)
        <article class="panel student-announcement">
            <header class="student-announcement-header">
                <h2>{{ $announcement->title }}</h2>
                <time datetime="{{ $announcement->created_at->toIso8601String() }}" title="{{ $announcement->created_at->format('M j, Y') }}">
                    {{ $announcement->created_at->diffForHumans() }}
                </time>
            </header>
            <div class="student-announcement-body">{{ $announcement->body }}</div>
        </article>
    @empty
        <div class="panel empty">No announcements are available right now.</div>
    @endforelse

    {{ $announcements->links() }}
</section>
