<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
</head>

<body>
    <div class="main-pc">
        <aside class="sidebar">
            @include('partials.sidebar')
        </aside>
        @include('partials.sidebarmobile')

        <div class="screen">
            @include('partials.header')
            @include('partials.body')
        </div>
    </div>

    @include('partials.scripts')
</body>

</html>
