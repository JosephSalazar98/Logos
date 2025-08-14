<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
    <style>
        /* -------- Modal (clean, focused) -------- */
        .modal {
            position: fixed;
            inset: 0;
            display: none;
            place-items: center;
            z-index: 80
        }

        .modal.show {
            display: grid
        }

        .modal__overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, .55);
            backdrop-filter: blur(2px)
        }

        .modal__dialog {
            position: relative;
            z-index: 1;
            width: min(520px, 94vw);
            background: linear-gradient(180deg, rgba(255, 255, 255, .06), rgba(255, 255, 255, .03));
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 18px;
            padding: 22px 20px 18px;
            box-shadow: 0 16px 64px rgba(0, 0, 0, .55), 0 0 42px rgba(0, 245, 212, .18);
            color: #e7ecef;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .modal__title {
            margin: 0 0 6px;
            font-weight: 700;
            letter-spacing: .3px
        }

        .modal__desc {
            margin: 0 0 16px;
            color: #b7c6cc
        }

        .modal__close {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .03);
            display: grid;
            place-items: center;
            cursor: pointer;
            color: #cfe;
        }

        .modal__close:hover {
            border-color: rgba(0, 245, 212, .35)
        }

        .modal__actions {
            display: grid;
            gap: 1rem
        }

        .btn {
            border: 1px solid rgba(255, 255, 255, .12);
            padding: 12px 16px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 800;
            letter-spacing: .2px;
            text-align: center;
            text-decoration: none;
            display: block
        }

        .btn--primary {
            border: 0;
            color: #071015;
            background: linear-gradient(135deg, #00f5d4, #00d1ff);
            box-shadow: 0 6px 6px rgba(0, 245, 212, .22), inset 0 0 0 1px rgba(255, 255, 255, .15);
        }

        .btn--secondary {
            color: #cfe;
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .12);
        }

        .modal__hint {
            margin-top: 2px;
            font-size: .85rem;
            color: #8fa4ad;
            text-align: center
        }

        /* Toast (fallback) */
        .toast {
            position: fixed;
            right: 18px;
            bottom: 18px;
            background: rgba(12, 16, 21, .92);
            border: 1px solid rgba(255, 255, 255, .12);
            padding: 10px 14px;
            border-radius: 10px;
            display: none;
            z-index: 90;
            color: #e7ecef
        }

        .toast.show {
            display: block;
            animation: fadeToast 2.1s ease both
        }

        @keyframes fadeToast {
            0% {
                opacity: 0;
                transform: translateY(6px)
            }

            10%,
            90% {
                opacity: 1;
                transform: translateY(0)
            }

            100% {
                opacity: 0;
                transform: translateY(6px)
            }
        }

        :root {
            --bg: #0a0c10;
            --panel: #0f1318;
            --text: #e7ecef;
            --muted: #a9b3ba;
            --accent: #00f5d4;
            --accent-2: #00d1ff;
            --danger: #ff5c7a;
            --ring: rgba(0, 245, 212, .45);
            --border: rgba(255, 255, 255, .08);
            --glow: 0 0 4px rgba(0, 245, 212, .25), 0 0 4px rgba(0, 209, 255, .15);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font: 15px/1.45 Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            letter-spacing: 0.2px;
        }

        /* --- Background layers: grid + vignette + noise --- */
        .bg {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
            background: radial-gradient(60vw 60vh at 70% -10%, rgba(0, 209, 255, 0.16), transparent 60%), radial-gradient(50vw 50vh at -10% 90%, rgba(0, 245, 212, 0.1), transparent 60%), linear-gradient(180deg, rgba(255, 255, 255, 0.03), transparent 35%), var(--bg);
        }

        .bg::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(transparent 31px, rgba(255, 255, 255, 0.06) 32px, transparent 33px), linear-gradient(90deg, transparent 31px, rgba(255, 255, 255, 0.04) 32px, transparent 33px);
            background-size: 32px 32px;
            -webkit-mask: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.65), rgb(0, 0, 0) 70%);
            mask: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.65), rgb(0, 0, 0) 70%);
            opacity: 0.35;
        }

        .bg::after {
            content: "";
            position: absolute;
            inset: -200%;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160"><filter id="n"><feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch"/></filter><rect width="100%" height="100%" filter="url(%23n)" opacity="0.035"/></svg>');
            animation: drift 40s linear infinite;
        }

        @keyframes drift {
            to {
                transform: translate3d(25%, 20%, 0);
            }
        }

        /* --- Layout --- */
        .wrap {
            min-height: 100%;
            display: grid;
            place-items: center;
            padding: 6vmin 2rem;
        }

        .card {
            width: min(860px, 100%);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.02));
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--glow);
            overflow: hidden;
        }

        /* --- Header / Terminal strip --- */
        .card__head {
            padding: 14px 16px;
            background: linear-gradient(90deg, rgba(0, 209, 255, 0.08), rgba(0, 245, 212, 0.08));
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .dot--r {
            background: #ff6b6b;
        }

        .dot--y {
            background: #ffd166;
        }

        .dot--g {
            background: #6bffb1;
        }

        .title {
            margin-left: 4px;
            font-weight: 600;
            letter-spacing: 0.4px;
            opacity: 0.9;
            text-shadow: 0 0 10px rgba(0, 209, 255, 0.35);
        }

        .title small {
            color: var(--muted);
            font-weight: 500;
            margin-left: 6px;
        }

        /* --- Body --- */
        .card__body {
            padding: 28px;
            display: grid;
            gap: 24px;
        }

        .intro {
            color: var(--muted);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--border);
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.03);
            font-size: 0.85rem;
            color: #cbeff2;
        }

        /* --- Form --- */
        form {
            display: grid;
            gap: 18px;
        }

        .grid {
            display: grid;
            gap: 16px;
            grid-template-columns: 1fr;
        }

        @media (min-width: 760px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        .field {
            position: relative;
            display: grid;
            gap: 8px;
        }

        .field__label {
            position: absolute;
            top: 12px;
            left: 44px;
            font-size: 0.85rem;
            color: #9ad3d9;
            pointer-events: none;
            transition: all 0.18s ease;
            background: linear-gradient(180deg, rgb(10, 12, 16) 0%, rgba(10, 12, 16, 0.6) 100%);
            padding: 0 6px;
            border-radius: 6px;
            opacity: 0.9;
        }

        .field__icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            opacity: 0.88;
            color: #8be9fa;
            filter: drop-shadow(0 0 8px rgba(0, 209, 255, 0.3));
            pointer-events: none;
        }

        .control {
            width: 100%;
            padding: 14px 14px 14px 14px;
            border-radius: 12px;
            color: var(--text);
            background: rgba(12, 16, 21, 0.85);
            border: 1px solid var(--border);
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.05s ease;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.02);
        }

        .control::-moz-placeholder {
            color: #667077;
        }

        .control::placeholder {
            color: #667077;
        }

        .control:focus {
            border-color: transparent;
            box-shadow: 0 0 0 2px var(--ring), 0 0 2px rgba(0, 209, 255, 0.2);
        }

        .control:not(:-moz-placeholder)+.field__label {
            transform: translateY(-20px) scale(0.92);
            color: #c8fff4;
            opacity: 1;
        }

        .control:not(:placeholder-shown)+.field__label,
        .control:focus+.field__label {
            transform: translateY(-20px) scale(0.92);
            color: #c8fff4;
            opacity: 1;
        }

        textarea.control {
            min-height: 160px;
            resize: vertical;
            line-height: 1.5;
            overflow: hidden;
        }

        .counts {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #97a6ad;
            margin-top: 4px;
        }

        .counts .limit.warn {
            color: var(--danger);
        }

        /* --- Actions --- */
        .actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .btn {
            position: relative;
            flex: 1;
            padding: 12px 16px;
            border-radius: 12px;
            border: 0;
            color: #071015;
            font-weight: 700;
            letter-spacing: 0.3px;
            cursor: pointer;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 2px 2px rgba(0, 245, 212, 0.2), inset 0 0 0 1px rgba(255, 255, 255, 0.15);
            transition: transform 0.06s ease, box-shadow 0.15s ease, filter 0.15s ease;
        }

        .btn:hover {
            box-shadow: 0 4px 4px rgba(0, 245, 212, 0.35);
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn--ghost {
            flex: 0 0 auto;
            min-width: 120px;
            background: transparent;
            width: 100%;
            color: #bfeff1;
            border: 1px solid var(--border);
            box-shadow: none;
        }

        .btn--ghost:hover {
            border-color: rgba(0, 245, 212, 0.35);
            color: #e6fffb;
        }

        /* --- Footer / Tips --- */
        .tips {
            margin-top: 4px;
            color: #8fa4ad;
            font-size: 0.86rem;
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .kbd {
            border: 1px solid var(--border);
            padding: 2px 6px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.03);
            font-size: 0.84rem;
            color: #d6f7ff;
        }

        /* Toast */
        .toast {
            position: fixed;
            right: 18px;
            bottom: 18px;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: rgba(14, 18, 23, 0.9);
            box-shadow: var(--glow);
            display: none;
            font-size: 0.9rem;
        }

        .toast.show {
            display: block;
            animation: toast 2.6s ease both;
        }

        @keyframes toast {
            0% {
                transform: translateY(8px);
                opacity: 0;
            }

            10%,
            90% {
                transform: translateY(0);
                opacity: 1;
            }

            100% {
                transform: translateY(8px);
                opacity: 0;
            }
        }
    </style>
</head>

<body>

    <div class="wrap">
        <div class="card">
            <div class="card__head">
                <div class="title">Nytheria AI<small></small></div>
            </div>

            <div class="card__body">
                <div class="intro">
                    <span class="badge" title="Endpoint ready">/terminal-of-ideas</span>
                    <span>Craft, mutate, and dispatch ideas to your endpoint.</span>
                </div>

                <form action="/terminal" method="POST" id="ideaForm" novalidate>
                    <div class="grid">
                        <!-- Base Topic -->
                        <div class="field">
                            <input class="control" type="text" id="base_topic" name="base_topic"
                                placeholder="e.g. Quantum creativity engines" maxlength="100" required />
                            <div class="counts">
                                <span>High-level theme</span>
                                <span class="limit" id="topicCount">0 / 100</span>
                            </div>
                        </div>

                        <!-- User Idea -->
                        <div class="field">
                            <textarea class="control" id="user_idea" name="user_idea"
                                placeholder="Describe a seed idea, constraints, or a vibe. The engine will elaborate and mutate it…"
                                maxlength="1000" required></textarea>
                            <div class="counts">
                                <span>Describe the seed</span>
                                <span class="limit" id="ideaCount">0 / 1000</span>
                            </div>
                        </div>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn--primary" id="submitBtn">
                            <span style="display:inline-flex;align-items:center;gap:8px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    aria-hidden="true">
                                    <path d="M3 11l18-8-8 18-2-8-8-2z" stroke="#071015" stroke-width="1.6"
                                        stroke-linejoin="round" />
                                </svg>
                                Generate Idea
                            </span>
                        </button>
                        {{-- <button type="button" class="btn" id="resetBtn">Reset</button> --}}
                    </div>

                    <div class="tips">
                        <span>Tip: keep topics tight; details go in the idea.</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast" role="status" aria-live="polite">Submitting…</div>

    <!-- Result Modal -->
    <div class="modal" id="resultModal" aria-hidden="true">
        <div class="modal__overlay" id="modalOverlay"></div>
        <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="modalTitle" style="margin: 0;">
            <button class="modal__close" id="closeModalBtn" aria-label="Close">
                <!-- X icon -->
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
            </button>
            <h3 style="margin: 0;" id="modalTitle">Idea posted</h3>
            <p class="modal__desc">Open the live post or view the saved text file.</p>
            <div class="modal__actions">
                <a id="tweetLink" class="btn btn--primary" href="#" target="_blank" rel="noopener noreferrer">View
                    on X</a>
                <a id="txtLink" class="btn btn--secondary" href="#" target="_blank"
                    rel="noopener noreferrer">Open .txt</a>
            </div>
            <div class="modal__hint">Press <strong>Esc</strong> to close</div>
        </div>
    </div>

    <script>
        const topic = document.getElementById('base_topic');
        const idea = document.getElementById('user_idea');
        const topicCount = document.getElementById('topicCount');
        const ideaCount = document.getElementById('ideaCount');
        const form = document.getElementById('ideaForm');
        const submitBtn = document.getElementById('submitBtn');
        const resetBtn = document.getElementById('resetBtn');
        const toast = document.getElementById('toast');

        // Modal elements
        const modal = document.getElementById('resultModal');
        const modalOverlay = document.getElementById('modalOverlay');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const tweetLink = document.getElementById('tweetLink');
        const txtLink = document.getElementById('txtLink');

        function showToast(msg, ms = 2100) {
            toast.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), ms);
        }

        function openModal(tweetUrl, fileUrl) {
            if (tweetUrl) tweetLink.href = tweetUrl;
            else tweetLink.removeAttribute('href');
            if (fileUrl) txtLink.href = fileUrl;
            else txtLink.removeAttribute('href');
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }
        modalOverlay.addEventListener('click', closeModal);
        closeModalBtn.addEventListener('click', closeModal);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });

        // Count + autogrow
        function updateCounts() {
            topicCount.textContent = (topic.value.length) + " / " + topic.maxLength;
            ideaCount.textContent = (idea.value.length) + " / " + idea.maxLength;
            ideaCount.classList.toggle('warn', idea.maxLength - idea.value.length < 50);
            topicCount.classList.toggle('warn', topic.maxLength - topic.value.length < 10);
        }

        function autoGrow(el) {
            el.style.height = 'auto';
            el.style.height = (el.scrollHeight) + 'px';
        }
        [topic, idea].forEach(el => {
            el.addEventListener('input', () => {
                updateCounts();
                if (el === idea) autoGrow(el);
            });
            el.addEventListener('focus', () => {
                if (el === idea) autoGrow(el);
            });
        });
        updateCounts();
        autoGrow(idea);
        topic.focus();

        // Cmd/Ctrl + Enter submits
        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
                form.requestSubmit(submitBtn);
            }
        });

        // Submit via fetch -> modal with X + txt link
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!topic.value.trim() || !idea.value.trim()) return;

            submitBtn.disabled = true;
            showToast('Submitting…', 1400);

            const body = new URLSearchParams({
                base_topic: topic.value,
                user_idea: idea.value
            });

            // Optional CSRF from <meta name="csrf-token" content="...">
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.content;

            try {
                const res = await fetch('/terminal', {
                    method: 'POST',
                    headers,
                    body: body.toString()
                });
                if (!res.ok) {
                    const txt = await res.text().catch(() => '');
                    throw new Error(`HTTP ${res.status} ${res.statusText} ${txt}`);
                }
                const json = await res.json(); // expecting { idea, url, tweet_url }
                openModal(json.tweet_url || '#', json.url || '#');
                showToast('Done', 1200);
            } catch (err) {
                console.error(err);
                showToast('Error');
                alert('Failed to submit: ' + err.message);
            } finally {
                submitBtn.disabled = false;
            }
        });

        // Reset
        resetBtn.addEventListener('click', () => {
            form.reset();
            updateCounts();
            autoGrow(idea);
            topic.focus();
        });
    </script>
</body>

</html>
