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

            @include('partials.tree.tree', ['tree' => $tree])
        </div>



    </div>

    @include('partials.scripts')
</body>

</html>
