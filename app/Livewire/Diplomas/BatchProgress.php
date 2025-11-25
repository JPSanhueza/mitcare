<?php

namespace App\Livewire\Diplomas;

use App\Models\DiplomaBatch;
use Livewire\Component;

class BatchProgress extends Component
{
    public ?int $batchId = null;
    public ?DiplomaBatch $batch = null;

    protected $listeners = [
        'diplomas-batch-started' => 'start',
    ];

    public function start(int $batchId): void
    {
        $this->batchId = $batchId;
        $this->refreshBatch();
    }

    public function refreshBatch(): void
    {
        if ($this->batchId) {
            $this->batch = DiplomaBatch::find($this->batchId);
        }
    }

    public function close(): void
    {
        $this->batchId = null;
        $this->batch   = null;
    }

    public function getProgressProperty(): int
    {
        if (! $this->batch || $this->batch->total === 0) {
            return 0;
        }

        return (int) round(($this->batch->processed / $this->batch->total) * 100);
    }

    public function render()
    {
        if ($this->batchId && ! $this->batch) {
            $this->refreshBatch();
        }

        return view('livewire.diplomas.batch-progress');
    }
}
