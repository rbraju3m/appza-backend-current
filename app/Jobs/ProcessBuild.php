<?php

namespace App\Jobs;

use App\Models\BuildOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessBuild implements ShouldQueue
{
    use Queueable;
    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $buildOrderId
    )
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = BuildOrder::find($this->buildOrderId);
//        dump($this->buildOrder->status->value);
        if ( $order->status->value !== 'pending') {
            Log::info('Ignoring Build order #' . $this->buildOrderId . 'Found In ' . $order->status->value . ' status');
            return;
        }
        Log::info('Processing Build order #' . $this->buildOrderId);
    }
}
