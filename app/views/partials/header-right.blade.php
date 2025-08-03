<div class="header__right">
    @php
        $current = app()->getRoute()['path'];
    @endphp
    @if ($current == '/')
        <div class="header__content">
            <p>> SEMANTIC NODE EXPLORER</p>
            <p>Hyperlinked Thought Fabric from Latent Conceptual Space</p>
            <div class="btn" id="ca" style="display: flex; gap: 0.5rem; align-items: center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M7 9.667C7 8.95967 7.28099 8.28131 7.78115 7.78115C8.28131 7.28099 8.95967 7 9.667 7H18.333C18.6832 7 19.03 7.06898 19.3536 7.20301C19.6772 7.33704 19.9712 7.53349 20.2189 7.78115C20.4665 8.0288 20.663 8.32281 20.797 8.64638C20.931 8.96996 21 9.31676 21 9.667V18.333C21 18.6832 20.931 19.03 20.797 19.3536C20.663 19.6772 20.4665 19.9712 20.2189 20.2189C19.9712 20.4665 19.6772 20.663 19.3536 20.797C19.03 20.931 18.6832 21 18.333 21H9.667C9.31676 21 8.96996 20.931 8.64638 20.797C8.32281 20.663 8.0288 20.4665 7.78115 20.2189C7.53349 19.9712 7.33704 19.6772 7.20301 19.3536C7.06898 19.03 7 18.6832 7 18.333V9.667Z"
                        stroke="#C6C9FA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path
                        d="M4.012 16.737C3.705 16.5626 3.44965 16.31 3.2719 16.0049C3.09415 15.6998 3.00034 15.3531 3 15V5C3 3.9 3.9 3 5 3H15C15.75 3 16.158 3.385 16.5 4"
                        stroke="#C6C9FA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>

                <p>Ewth31...tKvbonk</p>
            </div>
        </div>

        <div class="header__stats">
            <div class="header__stat">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M6.5 9.75V13.25L10 15.5L13.5 13.25V9.75L10 7.5L6.5 9.75ZM6.5 9.75L3.813 7.18M17.696 19.815L11.728 14.389M18.5 11.5H13.5M7.952 14.184L3.682 18.739M16.318 5.261L12.632 9.192M4.5 6.75L2.5 8L0.5 6.75V4.75L2.5 3.5L4.5 4.75V6.75ZM19.5 4.75L17.5 6L15.5 4.75V2.75L17.5 1.5L19.5 2.75V4.75ZM4.5 21.25L2.5 22.5L0.5 21.25V19.25L2.5 18L4.5 19.25V21.25ZM21 22.25L19 23.5L17 22.25V20.25L19 19L21 20.25V22.25ZM22.5 12.5L20.5 13.75L18.5 12.5V10.5L20.5 9.25L22.5 10.5V12.5Z"
                        stroke="#C6C9FA" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                </svg>

                <p>{{ $newConnections }} New connections</p>
            </div>
            <div class="header__stat">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M8.12078 15.8788C7.84218 15.6002 7.51143 15.3792 7.14742 15.2284C6.78342 15.0777 6.39328 15 5.99928 15C5.60528 15 5.21514 15.0777 4.85113 15.2284C4.48712 15.3792 4.15638 15.6002 3.87778 15.8788C3.31512 16.4415 2.99902 17.2046 2.99902 18.0003C2.99902 18.796 3.31512 19.5591 3.87778 20.1218C4.44043 20.6845 5.20356 21.0006 5.99928 21.0006C6.79499 21.0006 7.55812 20.6845 8.12078 20.1218C8.68343 19.5591 8.99953 18.796 8.99953 18.0003C8.99953 17.2046 8.68343 16.4415 8.12078 15.8788ZM8.12078 15.8788L15.8798 8.1198M15.8798 8.1198C16.1584 8.3984 16.4891 8.6194 16.8531 8.77017C17.2171 8.92095 17.6073 8.99856 18.0013 8.99856C18.3953 8.99856 18.7854 8.92095 19.1494 8.77017C19.5134 8.6194 19.8442 8.3984 20.1228 8.1198C20.4014 7.8412 20.6224 7.51046 20.7731 7.14645C20.9239 6.78244 21.0015 6.3923 21.0015 5.9983C21.0015 5.6043 20.9239 5.21416 20.7731 4.85015C20.6224 4.48615 20.4014 4.1554 20.1228 3.8768C19.5601 3.31414 18.797 2.99805 18.0013 2.99805C17.2056 2.99805 16.4424 3.31414 15.8798 3.8768C15.3171 4.43946 15.001 5.20258 15.001 5.9983C15.001 6.79402 15.3171 7.55714 15.8798 8.1198ZM15.8798 8.1198L15.8838 8.1158"
                        stroke="#C6C9FA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>


                <p>{{ $ideasGenerated }} Ideas generated</p>
            </div>
            <div class="header__stat">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2ZM18 20H6V4H13V9H18V20Z"
                        fill="#C6C9FA" />
                </svg>
                <p>{{ $filesSaved }} Files saved</p>
            </div>
        </div>
        <div class="header__stats">
            <a class="header__stat" href="https://x.com/logosaixyz" target="_blank">

                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_4882_6)">
                        <mask id="mask0_4882_6" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0"
                            width="14" height="14">
                            <path d="M0 0H14V14H0V0Z" fill="white" />
                        </mask>
                        <g mask="url(#mask0_4882_6)">
                            <path
                                d="M11.025 0.65625H13.172L8.482 6.03025L14 13.3442H9.68L6.294 8.90925L2.424 13.3442H0.275L5.291 7.59425L0 0.65725H4.43L7.486 4.71025L11.025 0.65625ZM10.27 12.0562H11.46L3.78 1.87725H2.504L10.27 12.0562Z"
                                fill="#C6C9FA" />
                        </g>
                    </g>
                    <defs>
                        <clipPath id="clip0_4882_6">
                            <rect width="14" height="14" fill="white" />
                        </clipPath>
                    </defs>
                </svg>

                <p>Twitter</p>
            </a>
            {{-- <a class="header__stat">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M6.00009 12H4.12509C3.52509 12 3.03809 12.487 3.03809 13.087V20.163C3.03809 20.763 3.52509 21.25 4.12509 21.25H5.99809C6.59809 21.25 7.08509 20.763 7.08509 20.163V13.087C7.08509 12.487 6.59809 12 5.99809 12M12.9371 7.375H11.0641C10.4641 7.375 9.97709 7.862 9.97709 8.462V20.163C9.97709 20.763 10.4631 21.25 11.0631 21.25H12.9371C13.5371 21.25 14.0241 20.763 14.0241 20.163V8.462C14.0241 7.862 13.5371 7.375 12.9371 7.375ZM19.8741 2.75H18.0011C17.4011 2.75 16.9141 3.237 16.9141 3.837V20.163C16.9141 20.763 17.4011 21.25 18.0011 21.25H19.8741C20.4741 21.25 20.9611 20.763 20.9611 20.163V3.837C20.9611 3.237 20.4741 2.75 19.8741 2.75Z"
                        stroke="#C6C9FA" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />
                </svg>


                <p>DexScreener</p>
            </a> --}}
            <a href="https://t.me/+xBkm8h0ECjI0Njgx" target="_blank" class="header__stat">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 5L2 12.5L9 13.5M21 5L18.5 20L9 13.5M21 5L9 13.5M9 13.5V19L12.249 15.723"
                        stroke="#C6C9FA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>



                <p>Telegram</p>
            </a>

        </div>
    @endif

    <div class="ideaheader">
        @include('partials.web3')
    </div>
</div>
