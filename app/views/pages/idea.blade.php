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


        <div x-data="ideasTabs" x-init="init()" class="screen" style="padding: 1rem">
            @include('partials.header')

            <div class="tabs">
                <button :class="{ 'is-active': tab === 'topics' }" @click="tab = 'topics'; load('topics')">
                    Topics</button>
                <button :class="{ 'is-active': tab === 'bridges' }" @click="tab = 'bridges'; load('bridges')">
                    Bridges</button>
                <button :class="{ 'is-active': tab === 'ideas' }" @click="tab = 'ideas'; load('ideas')">
                    Ideas</button>
            </div>

            <div x-ref="container"></div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ideasTabs', () => ({
                tab: 'topics',
                async load(route) {
                    const res = await fetch('/ideas/' + route);
                    const html = await res.text();

                    Alpine.mutateDom(() => {
                        this.$refs.container.innerHTML = html;
                    });

                    await Alpine.initTree(this.$refs.container);
                    this.attachPaginationHandlers();
                },
                init() {
                    this.load('topics');
                },
                attachPaginationHandlers() {
                    const links = this.$refs.container.querySelectorAll('[data-route]');
                    links.forEach(link => {
                        link.addEventListener('click', e => {
                            e.preventDefault();
                            const route = link.dataset.route;
                            this.tab = route.split('?')[0];
                            this.load(route);
                        });
                    });
                }
            }));
        });
    </script>

    @include('partials.scripts')
</body>

</html>
