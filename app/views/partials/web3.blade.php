<div class="header__wallet">
    <div id="connectBtn" class="btn--primary">
        <svg id="walletIconDisconnected" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-wallet w-4 h-4">
            <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path>
            <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path>
            <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"></path>
        </svg>
        {{-- Si está conectado --}}
        <svg id="walletIconConnected" style="display: none" xmlns="http://www.w3.org/2000/svg" width="18"
            height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wallet w-4 h-4">
            <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path>
            <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path>
            <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"></path>
        </svg>




        <p id="isConnected">Connect wallet</p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <div id="manualSignatureBtn" class="" style="display: none; margin-top: 1rem;">
            Buy credits
        </div>
        <p id="walletAddress" style="display: none; font-size: 12px; opacity: 0.8;"></p>
        <p id="walletCredits" style="display: none; font-size: 12px;"></p>
    </div>

</div>

<dialog id="signatureDialog" class="dialog">
    <form method="dialog" class="dialog__form" id="signatureForm">
        <div class="dialog__info-block">
            <h3>How to buy credits</h3>
            <p class="dialog__info-text">
                To assign credits, send <strong>0.01 SOL</strong> to:
            </p>
            <p class="dialog__wallet-address" onclick="copyWalletAddress()" title="Click to copy">
                AR6HNvgpaW1zVE15j1k2vG8UzZZeVqE8RZY1ZEagX645
            </p>
            <p class="dialog__info-text">
                Then paste the transaction signature below.<br>
                You’ll receive <strong>1 credit per 0.01 SOL</strong> sent.
            </p>
        </div>


        <label for="signatureInput">Paste the transaction signature:</label>
        <input id="signatureInput" type="text" placeholder="Signature..." class="dialog__input" />

        <div id="signatureActions" style="margin-top: 1rem;">
            <button id="submitSignatureBtn" type="button" class="btn--primary">Submit</button>
            <button type="button" id="cancelSignatureBtn" class="btn--secondary">Cancel</button>
        </div>

        <div id="signatureResult" style="display:none; margin-top: 1rem;"></div>

        <button id="dialogCloseBtn" type="button" class="btn--primary"
            style="margin-top: 1rem; display: none;">Close</button>
    </form>

</dialog>
