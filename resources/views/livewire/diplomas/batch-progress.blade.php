<div>
    @once
        <style>
            .diploma-progress-backdrop {
                position: fixed;
                inset: 0;
                z-index: 200;
                background: rgba(0, 0, 0, 0.45);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .diploma-progress-card {
                width: 90%;
                max-width: 420px;
                border-radius: 14px;
                padding: 24px;
                background: #ffffff;
                color: #1f2937;
                box-shadow: 0 6px 25px rgba(0,0,0,0.25);
            }

            .dark .diploma-progress-card {
                background: #111827;
                color: #e5e7eb;
                box-shadow: 0 6px 25px rgba(0,0,0,0.5);
            }

            .diploma-progress-title {
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 8px;
                text-align: left;
            }

            .diploma-progress-text {
                font-size: 14px;
                margin-bottom: 12px;
                opacity: 0.9;
            }

            .diploma-progress-bar {
                width: 100%;
                height: 10px;
                background: #e5e7eb;
                border-radius: 999px;
                overflow: hidden;
                margin-bottom: 12px;
            }

            .dark .diploma-progress-bar {
                background: #374151;
            }

            .diploma-progress-inner {
                height: 10px;
                background: #10b981;
                transition: width 0.4s ease;
            }

            .diploma-success-text {
                font-size: 14px;
                font-weight: 600;
                color: #059669;
                margin-top: 6px;
            }

            .dark .diploma-success-text {
                color: #6ee7b7;
            }

            .diploma-close-btn {
                margin-top: 14px;
                background: #10b981;
                color: white;
                border: none;
                padding: 8px 14px;
                border-radius: 8px;
                font-size: 14px;
                cursor: pointer;
                font-weight: 500;
            }

            .diploma-close-btn:hover {
                background: #059669;
            }
        </style>
    @endonce

    @if ($batchId)
        <div class="diploma-progress-backdrop" wire:poll.1000ms="refreshBatch">
            <div class="diploma-progress-card">
                @if ($batch)
                    <div class="diploma-progress-title">
                        Generando diplomas…
                    </div>

                    <div class="diploma-progress-text">
                        Procesados {{ $batch->processed }} de {{ $batch->total }}.
                    </div>

                    <div class="diploma-progress-bar">
                        <div class="diploma-progress-inner"
                             style="width: {{ $this->progress }}%;"></div>
                    </div>

                    @if ($batch->status === 'done')
                        <div class="diploma-success-text">
                            ¡Diplomas listos!
                        </div>

                        <button
                            type="button"
                            class="diploma-close-btn"
                            wire:click="close"
                        >
                            Cerrar
                        </button>
                    @endif
                @else
                    <div class="diploma-progress-title">
                        Preparando lote…
                    </div>
                    <div class="diploma-progress-text">
                        Consultando estado de los diplomas.
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
