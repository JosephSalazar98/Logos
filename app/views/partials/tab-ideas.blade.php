<div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 2rem">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M19.0901 5.87322L20.3323 4.50635C20.5288 4.2849 20.63 3.99494 20.6141 3.69933C20.5982 3.40373 20.4664 3.1263 20.2473 2.92721C20.0283 2.72811 19.7395 2.62339 19.4438 2.63575C19.148 2.6481 18.869 2.77655 18.6673 2.99322L17.4261 4.35822C15.5577 3.0404 13.2721 2.45077 10.9994 2.70027C8.72672 2.94976 6.62352 4.02119 5.08555 5.71295C3.54757 7.40472 2.68083 9.6002 2.6484 11.8863C2.61598 14.1725 3.42011 16.3916 4.90949 18.1263L3.6673 19.4932C3.56586 19.6022 3.48706 19.7302 3.43546 19.8698C3.38386 20.0095 3.3605 20.158 3.36671 20.3067C3.37292 20.4555 3.4086 20.6015 3.47167 20.7364C3.53473 20.8712 3.62394 20.9922 3.73411 21.0923C3.84429 21.1924 3.97324 21.2697 4.11349 21.3196C4.25374 21.3696 4.40251 21.3911 4.55117 21.3831C4.69982 21.3751 4.84541 21.3377 4.9795 21.273C5.11359 21.2084 5.23351 21.1177 5.3323 21.0063L6.57355 19.6413C8.44192 20.9592 10.7275 21.5488 13.0002 21.2993C15.2729 21.0498 17.3761 19.9784 18.9141 18.2866C20.452 16.5949 21.3188 14.3994 21.3512 12.1132C21.3836 9.8271 20.5795 7.60792 19.0901 5.87322ZM4.8748 11.9998C4.87372 10.7153 5.22017 9.45442 5.87744 8.35082C6.53471 7.24722 7.47831 6.342 8.60824 5.7311C9.73817 5.1202 11.0123 4.82638 12.2957 4.88078C13.579 4.93518 14.8237 5.33577 15.8979 6.0401L6.43761 16.4463C5.42467 15.1858 4.87322 13.6169 4.8748 11.9998ZM11.9998 19.1248C10.6144 19.1262 9.259 18.721 8.10168 17.9595L17.562 7.55322C18.4013 8.60054 18.9275 9.86377 19.0799 11.1972C19.2322 12.5307 19.0045 13.8801 18.4231 15.0897C17.8416 16.2994 16.9301 17.32 15.7936 18.034C14.6571 18.7479 13.3419 19.126 11.9998 19.1248Z"
            fill="#C6C9FA" />
    </svg>


    <h1 class="bridges__title">Bank of ideas</h1>
</div>
<div class="strange-ideas__grid">
    @foreach ($strangeIdeas as $idea)
        <div class="strange-ideas__card">
            <div class="strange-ideas__tag">[ IDEA: {{ $idea->id }} ]</div>


            <p class="strange-ideas__desc">
                {{ $idea->idea }}
            </p>

            <div style="margin-top: auto; display: flex; flex-direction: column; gap: 0.5rem">
                {{-- <div class="strange-ideas__file">
                    ↳ file: <a href="/trees/{{ $idea->source }}">{{ $idea->source }}</a>
                </div> --}}

                <div class="strange-ideas__footer">
                    <span>{{ $idea->node->topic ?? 'Unnamed node' }}</span>
                    <span>confidence: {{ number_format($idea->confidence, 2) }}</span>
                </div>
            </div>


        </div>
    @endforeach
</div>

<div class="pagination">
    @foreach ($strangeIdeas->getUrlRange(1, $strangeIdeas->lastPage()) as $page => $url)
        @if ($page == $strangeIdeas->currentPage())
            <span class="page-active">{{ $page }}</span>
        @else
            <a class="page-link" href="/ideas/ideas?page={{ $page }}"
                @click.prevent="$root.load('ideas?page={{ $page }}')">{{ $page }}</a>
        @endif
    @endforeach
</div>
