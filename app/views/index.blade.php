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
