{{-- resources/views/cape/partials/cape-panel.blade.php
     Blade partial included in the IoT dashboard — populated entirely via JS polling --}}

<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="glass-card p-4" id="cape-panel">
            {{-- Header Row --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-white fw-bold mb-0">
                    <i class="material-icons align-middle me-2">psychology</i>
                    CAPE RISK ASSESSMENT
                </h5>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-xs text-slate-500" id="cape-last-updated">Awaiting first assessment…</span>
                    <a href="/admin/cape-logs" class="btn btn-sm btn-glass px-3">
                        <i class="material-icons align-middle me-1" style="font-size: 16px;">history</i> Logs
                    </a>
                </div>
            </div>

            {{-- Risk Badge --}}
            <div class="text-center mb-4">
                <span id="cape-risk-badge"
                      class="badge bg-secondary px-5 py-3"
                      style="font-size: 1.75rem; font-weight: 900; letter-spacing: 2px; border-radius: 1rem;">
                    ANALYZING…
                </span>
                <div class="text-slate-500 text-xs mt-2" id="cape-response-time">—</div>
            </div>

            {{-- Context Grid --}}
            <div class="row g-2 mb-4" id="cape-context-grid">
                <div class="col-md-4 col-lg-2">
                    <div class="p-3 rounded-3 text-center" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="text-slate-500 text-xs text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing: 0.1rem;">Speed</div>
                        <div class="text-white small fw-bold" id="cape-ctx-speed">—</div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="p-3 rounded-3 text-center" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="text-slate-500 text-xs text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing: 0.1rem;">Light</div>
                        <div class="text-white small fw-bold" id="cape-ctx-light">—</div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="p-3 rounded-3 text-center" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="text-slate-500 text-xs text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing: 0.1rem;">Obstacle</div>
                        <div class="text-white small fw-bold" id="cape-ctx-obstacle">—</div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="p-3 rounded-3 text-center" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="text-slate-500 text-xs text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing: 0.1rem;">Flood Risk</div>
                        <div class="text-white small fw-bold" id="cape-ctx-flood">—</div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="p-3 rounded-3 text-center" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="text-slate-500 text-xs text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing: 0.1rem;">Weather</div>
                        <div class="text-white small fw-bold" id="cape-ctx-weather">—</div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="p-3 rounded-3 text-center" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="text-slate-500 text-xs text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing: 0.1rem;">Proximity</div>
                        <div class="text-white small fw-bold" id="cape-ctx-proximity">—</div>
                    </div>
                </div>
            </div>

            {{-- Reasons & Actions Row --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="p-3 rounded-3" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.06);">
                        <h6 class="text-white fw-bold mb-2 small text-uppercase" style="letter-spacing: 0.1rem;">
                            <i class="material-icons align-middle me-1" style="font-size: 16px;">warning</i> Risk Reasons
                        </h6>
                        <ul class="list-unstyled text-slate-300 small mb-0" id="cape-reasons-list">
                            <li class="text-slate-500">Awaiting assessment…</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.06);">
                        <h6 class="text-white fw-bold mb-2 small text-uppercase" style="letter-spacing: 0.1rem;">
                            <i class="material-icons align-middle me-1" style="font-size: 16px;">task_alt</i> Recommended Actions
                        </h6>
                        <ol class="text-slate-300 small mb-0 ps-3" id="cape-actions-list">
                            <li class="text-slate-500">Awaiting assessment…</li>
                        </ol>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.06);">
                        <h6 class="text-white fw-bold mb-2 small text-uppercase" style="letter-spacing: 0.1rem;">
                            <i class="material-icons align-middle me-1" style="font-size: 16px;">trending_up</i> Prediction
                        </h6>
                        <p class="text-slate-300 small fst-italic mb-0" id="cape-prediction">Awaiting assessment…</p>
                    </div>
                </div>
            </div>

            {{-- Collapsible Prompt Viewer --}}
            <div class="mb-4">
                <button class="btn btn-sm btn-glass w-100 text-start" type="button"
                        data-bs-toggle="collapse" data-bs-target="#cape-prompt-collapse"
                        aria-expanded="false" aria-controls="cape-prompt-collapse">
                    <i class="material-icons align-middle me-1" style="font-size: 16px;">code</i>
                    View Generated Prompt (Research Transparency)
                    <i class="material-icons align-middle float-end" style="font-size: 16px;">expand_more</i>
                </button>
                <div class="collapse mt-2" id="cape-prompt-collapse">
                    <pre id="cape-prompt-text"
                         style="background: #0f172a; color: #94a3b8; padding: 1rem; border-radius: 0.75rem;
                                font-size: 0.75rem; max-height: 300px; overflow-y: auto;
                                white-space: pre-wrap; word-break: break-word;
                                border: 1px solid rgba(255,255,255,0.06);">
Awaiting first assessment…</pre>
                </div>
            </div>

            {{-- Ask CAPE Mini Chat --}}
            <div class="p-3 rounded-3" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.06);">
                <h6 class="text-white fw-bold mb-2 small text-uppercase" style="letter-spacing: 0.1rem;">
                    <i class="material-icons align-middle me-1" style="font-size: 16px;">chat</i> Ask CAPE
                </h6>
                <div class="d-flex gap-2">
                    <input type="text" id="cape-chat-input"
                           class="form-control form-control-sm"
                           style="background: #1e293b; border-color: rgba(255,255,255,0.1); color: #e2e8f0;"
                           placeholder="Ask about the current risk assessment…"
                           maxlength="500">
                    <button id="cape-chat-send" class="btn btn-sm btn-primary px-3"
                            style="white-space: nowrap;">
                        <i class="material-icons align-middle" style="font-size: 16px;">send</i>
                    </button>
                </div>
                <div id="cape-chat-response" class="mt-2 text-slate-300 small" style="display: none;">
                    <div class="p-2 rounded" style="background: rgba(96,165,250,0.08); border: 1px solid rgba(96,165,250,0.15);">
                        <i class="material-icons align-middle me-1" style="font-size: 14px; color: #60a5fa;">smart_toy</i>
                        <span id="cape-chat-answer"></span>
                    </div>
                </div>
                <div id="cape-chat-loading" class="mt-2 text-slate-500 small" style="display: none;">
                    <i class="material-icons align-middle me-1 spin" style="font-size: 14px;">hourglass_empty</i>
                    CAPE is thinking…
                </div>
            </div>
        </div>
    </div>
</div>
