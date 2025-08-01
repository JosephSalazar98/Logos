<div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 2rem">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M3 8V3H8M16 3H21V8M21 16V21H16M8 21H3V16" stroke="#C6C9FA" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>

    <h1 class="bridges__title">Index of topics</h1>
</div>
<div class="topics__grid">
    @foreach ($topics as $topic)
        <div class="topic-card-terminal">
            <div class="topic-card-terminal__header">
                [ TOPIC NODE: {{ $topic->id }}]
                <span>{{ $topic->created_at->format('d M Y') }}</span>
            </div>

            <h2 class="topic-card-terminal__title">> {{ strtolower($topic->topic) }}</h2>

            <pre class="topic-card-terminal__desc">{{ mb_strimwidth($topic->description, 0, 200, '...') }}</pre>

            <div class="topic-card-terminal__meta">
                ↳ file: <a href="/trees/{{ $topic->file_path }}">{{ $topic->file_path }}</a>
            </div>

            {{-- <div class="topic-card-terminal__actions">
                <a href="/tree/{{ $topic->id }}">expand tree</a>
                <button @click="openModal({{ $topic->id }})">details</button>
            </div> --}}
        </div>
    @endforeach
</div>

<div class="pagination">
    @foreach ($topics->getUrlRange(1, $topics->lastPage()) as $page => $url)
        @if ($page == $topics->currentPage())
            <span class="page-active">{{ $page }}</span>
        @else
            <a class="page-link" href="/ideas/topics?page={{ $page }}"
                @click.prevent="$root.load('topics?page={{ $page }}')">{{ $page }}</a>
        @endif
    @endforeach
</div>
