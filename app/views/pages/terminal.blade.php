<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
    @vite('css/others.css')

</head>

<body>
    <div class="main-pc">
        <aside class="sidebar">
            @include('partials.sidebar')
        </aside>
        @include('partials.sidebarmobile')
        <div class="screen screenpage">
            @include('partials.header')
            @include('partials.terminal')
        </div>
    </div>

    @include('partials.scripts')
</body>

</html>
