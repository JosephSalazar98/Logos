<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
    @vite('css/others.css')
</head>

<body>
    @include('partials.sidebarmobile')

    <div class="main-pc">
        <aside class="sidebar">
            @include('partials.sidebar')
        </aside>

        <div class="screen" style="padding: 1rem">
            @include('partials.header')

            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 2rem">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 8V3H8M16 3H21V8M21 16V21H16M8 21H3V16" stroke="#C6C9FA" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <h1 class="bridges__title">Index of topics</h1>
            </div>

            <div class="topics__grid">
                @foreach ($topics as $topic)
                    <a href="/topics/{{ $topic->slug }}" class="topic-card-terminal"
                        style="text-decoration: none; color: inherit;">
                        <div class="topic-card-terminal__header">
                            [ TOPIC NODE: {{ $topic->id }} ]
                            <span>{{ $topic->created_at->format('d M Y') }}</span>
                        </div>

                        <h2 class="topic-card-terminal__title">> {{ strtolower($topic->topic) }}</h2>

                        <pre class="topic-card-terminal__desc">
{{ mb_strimwidth($topic->description, 0, 200, '...') }}
        </pre>

                        <div class="topic-card-terminal__meta">
                            ↳ file:
                            <span>{{ $topic->file_path }}</span>
                        </div>
                    </a>
                @endforeach

            </div>

            @if ($topics->lastPage() > 1)
                <div class="pagination" style="margin-top: 2rem; display: flex; gap: 0.5rem;">
                    @foreach ($topics->getUrlRange(1, $topics->lastPage()) as $page => $url)
                        @if ($page == $topics->currentPage())
                            <span class="page-active"
                                style="font-weight: bold; color: #C6C9FA;">{{ $page }}</span>
                        @else
                            <a class="page-link" href="{{ $url }}" style="color: #888;">{{ $page }}</a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>



    </div>

    @include('partials.scripts')
</body>

</html>
