<?php

namespace App\Jobs;

use App\Models\BuildOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class DeleteBuildDir implements ShouldQueue
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
        Log::info("Delete build directory job started.".$order);
    }
}
