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

            <a target="_blank" href="/trees/semantic_tree_{{ $topic->id }}.json" class="btn--primary">View json</a>
            @include('partials.tree.tree', ['tree' => $tree])
        </div>
    </div>


    @include('partials.scripts')
</body>

</html>
