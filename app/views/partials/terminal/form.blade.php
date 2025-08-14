<div class="bg"></div>

<div class="wrap" id="terminalform">
    <div class="card">
        <div class="card__head">
            <div class="title">Nytheria AI</div>
        </div>

        <div class="card__body">
            <div class="intro">
                <span class="badge" title="Endpoint ready">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 2l3 6 6 .9-4.5 4.2L17.8 20 12 16.9 6.2 20l1.3-6.9L3 8.9 9 8l3-6z"
                            stroke="currentColor" stroke-width="1.4" />
                    </svg>
                    /terminal
                </span>
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
                        <textarea class="control" id="user_idea" name="user_idea" placeholder="Describe a seed idea, constraints, or a vibe…"
                            maxlength="1000" required></textarea>
                        <div class="counts">
                            <span>Describe the seed</span>
                            <span class="limit" id="ideaCount">0 / 1000</span>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button type="button" class="btn btn--ghost" id="resetBtn">Reset</button>
                    <button type="submit" class="btn" id="submitBtn">
                        <span style="display:inline-flex;align-items:center;gap:8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M3 11l18-8-8 18-2-8-8-2z" stroke="#071015" stroke-width="1.6"
                                    stroke-linejoin="round" />
                            </svg>
                            Generate Idea
                        </span>
                    </button>
                </div>

                <div class="tips">
                    <span>Tip: keep topics tight; details go in the idea.</span>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="toast" id="toast" role="status" aria-live="polite">Submitting…</div>
