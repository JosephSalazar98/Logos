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

            <h1 class="bridges__title" style="margin-bottom: 1rem;">Topic: {{ $topic->topic }}</h1>

            <p style="margin-bottom: 2rem;">{{ $topic->description }}</p>

            {{-- @if ($topic->children->count())
                <h2 style="margin-bottom: 1rem;">Children Nodes</h2>

                <div class="topics__grid">
                    @foreach ($topic->children as $child)
                        <div class="topic-card-terminal">
                            <div class="topic-card-terminal__header">
                                [ NODE: {{ $child->id }} ]
                                <span>{{ $child->created_at->format('d M Y') }}</span>
                            </div>

                            <h2 class="topic-card-terminal__title">> {{ strtolower($child->topic) }}</h2>

                            <pre class="topic-card-terminal__desc">
{{ mb_strimwidth($child->description, 0, 200, '...') }}
                            </pre>

                            <div class="topic-card-terminal__meta">
                                ↳ file:
                                <span>{{ $child->file_path }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p>This topic has no children.</p>
            @endif
            @if ($bridges->count())
                <div style="display: flex; align-items: center; gap: 0.6rem; margin: 2rem 0 1rem;">
                    <svg width="24" height="24" viewBox="0 0 18 18" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="..." fill="#C6C9FA" />
                    </svg>
                    <h1 class="bridges__title">Semantic connections</h1>
                </div>

                <div class="bridges__list">
                    @foreach ($bridges as $bridge)
                        <div class="bridge-card-terminal">
                            <div class="bridge-card-terminal__header">
                                [ BRIDGE ID: {{ $bridge->id }} ] &nbsp;&nbsp;
                                {{ $bridge->created_at->format('d M Y') }}
                            </div>

                            <div class="bridge-card-terminal__title">
                                › {{ $bridge->source->topic }}
                            </div>

                            <div class="bridge-card-terminal__arrow">
                                ⇅ semantic connection ⇅
                            </div>

                            <div class="bridge-card-terminal__title">
                                › {{ $bridge->target->topic }}
                            </div>

                            <div class="bridge-card-terminal__score">
                                ⟶ cosine score: {{ number_format($bridge->cosine_score, 2) }}
                            </div>

                            @if ($bridge->strange_idea_id)
                                <div class="bridge-card-terminal__actions">
                                    ↳ <a href="#"
                                        @click.prevent="openStrangeIdea({{ $bridge->strange_idea_id }})">
                                        view explanation
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif --}}
            @include('partials.tree.tree', ['tree' => $tree])
        </div>
    </div>


    @include('partials.scripts')
</body>

</html>
