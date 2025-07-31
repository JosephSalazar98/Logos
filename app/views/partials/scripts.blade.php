<!-- Solana Web3 -->
<script src="https://unpkg.com/@solana/web3.js@latest/lib/index.iife.min.js"></script>

<script>
    const statusText = document.getElementById('isConnected');
    let isConnected = false;

    async function toggleWalletConnection() {
        const statusText = document.getElementById('isConnected');

        if (!window.solana || !window.solana.isPhantom) {
            statusText.textContent = 'Install Phantom Wallet';
            return;
        }

        if (isConnected) {
            await fetch('/api/auth/logout', {
                method: 'POST'
            });
            await window.solana.disconnect();

            window.userWallet = "";
            document.getElementById('manualSignatureBtn').style.display = 'none';

            isConnected = false;
            statusText.textContent = 'Connect wallet';

            document.getElementById('walletIconConnected').style.display = 'none';
            document.getElementById('walletIconDisconnected').style.display = 'inline';
            document.getElementById('walletAddress').style.display = 'none';
            document.getElementById('walletCredits').style.display = 'none';

            return;
        }

        try {
            const resp = await window.solana.connect();
            const publicKey = resp.publicKey.toString();

            const message = `Login to Semantic Explorer\n${new Date().toISOString()}`;
            const encodedMessage = new TextEncoder().encode(message);
            const signed = await window.solana.signMessage(encodedMessage, 'utf8');

            const body = {
                address: publicKey,
                message,
                signature: Array.from(signed.signature),
            };

            const response = await fetch('/api/auth/web3', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(body)
            });

            const result = await response.json();

            if (response.ok) {
                window.userWallet = publicKey;
                isConnected = true;

                document.getElementById('manualSignatureBtn').style.display = 'block';
                statusText.textContent = 'Disconnect';

                document.getElementById('walletIconConnected').style.display = 'inline';
                document.getElementById('walletIconDisconnected').style.display = 'none';

                const shortAddr = `${publicKey.slice(0, 4)}...${publicKey.slice(-4)}`;
                document.getElementById('walletAddress').textContent = `${shortAddr}`;
                document.getElementById('walletAddress').style.display = 'block';

                document.getElementById('walletCredits').textContent = `Credits: ${result.credits}`;
                document.getElementById('walletCredits').style.display = 'block';
            } else {
                statusText.textContent = 'Auth failed';
                console.error(result);
            }

        } catch (err) {
            console.error('Connection error:', err);
            statusText.textContent = 'Connection error';
        }
    }

    async function disconnectWallet() {
        window.userWallet = "";
        sessionStorage.clear();
        await window.solana.disconnect();
        document.getElementById('manualSignatureBtn').style.display = 'none';
        statusText.textContent = 'Connect wallet';
    }

    async function submitSignatureManually() {
        const dialog = document.getElementById("signatureDialog");
        const sigInput = document.getElementById("signatureInput");
        const resultBox = document.getElementById("signatureResult");
        const submitBtn = document.getElementById("submitSignatureBtn");
        const cancelBtn = document.getElementById("cancelSignatureBtn");
        const closeBtn = document.getElementById("dialogCloseBtn");

        // Reset estado
        sigInput.value = "";
        resultBox.style.display = "none";
        submitBtn.style.display = "inline-block";
        cancelBtn.style.display = "inline-block";
        closeBtn.style.display = "none";

        dialog.showModal();

        submitBtn.onclick = async () => {
            const signature = sigInput.value.trim();
            if (!signature) return;

            resultBox.innerHTML = "Processing...";
            resultBox.style.display = "block";

            const res = await fetch("/api/pay/confirm", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "include",
                body: JSON.stringify({
                    signature,
                    amount: 0.01,
                    token: "SOL"
                }),
            });

            const result = await res.json();

            if (res.ok) {
                resultBox.innerHTML = `<strong>You have </strong> ${result.credits} !`;
            } else {
                resultBox.innerHTML = `<strong>Error:</strong> ${result.error || "Check console"}`;
                console.error(result);
            }

            // Oculta botones de entrada y muestra solo Close
            submitBtn.style.display = "none";
            cancelBtn.style.display = "none";
            closeBtn.style.display = "inline-block";
        };

        cancelBtn.onclick = () => dialog.close();
        closeBtn.onclick = () => dialog.close();
    }



    document.addEventListener("DOMContentLoaded", () => {
        const dialog = document.getElementById("creditsDialog");
        const closeBtn = document.getElementById("dialogCloseBtn");

        closeBtn.addEventListener("click", () => {
            dialog.close();
        });
    });

    document.addEventListener('DOMContentLoaded', async () => {
        document.getElementById('connectBtn')?.addEventListener('click', toggleWalletConnection);
        document.getElementById('manualSignatureBtn')?.addEventListener('click', submitSignatureManually);

        try {
            const res = await fetch('/api/auth/session', {
                credentials: 'include'
            });
            const data = await res.json();

            if (data.loggedIn) {
                window.userWallet = data.wallet;
                isConnected = true;

                document.getElementById('manualSignatureBtn').style.display = 'block';
                statusText.textContent = 'Disconnect';

                document.getElementById('walletIconConnected').style.display = 'inline';
                document.getElementById('walletIconDisconnected').style.display = 'none';

                const shortAddr = `${data.wallet.slice(0, 4)}...${data.wallet.slice(-4)}`;
                document.getElementById('walletAddress').textContent = `${shortAddr}`;
                document.getElementById('walletAddress').style.display = 'block';

                document.getElementById('walletCredits').textContent = `Credits: ${data.credits}`;
                document.getElementById('walletCredits').style.display = 'block';
            }
        } catch (err) {
            console.error('Failed to restore session:', err);
        }
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const copyBtn = document.getElementById('ca');
        const textEl = copyBtn.querySelector('p');

        copyBtn.addEventListener('click', () => {
            const originalText = textEl.textContent.trim();

            navigator.clipboard.writeText(originalText)
                .then(() => {
                    textEl.textContent = 'Copied!';
                    setTimeout(() => {
                        textEl.textContent = originalText;
                    }, 1500);
                })
                .catch(err => {
                    console.error('Failed to copy:', err);
                });
        });
    });
</script>
