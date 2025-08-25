<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DebugKernel extends Command
{
    protected $signature = 'debug:kernel';

    protected $description = 'Debug which Kernel class is bound';

    /**
     * @throws \ReflectionException
     */
    public function handle()
    {
        $kernel = app(\Illuminate\Contracts\Console\Kernel::class);

        $this->info('Bound Kernel class: ' . get_class($kernel));

        $kernelFile = (new \ReflectionClass($kernel))->getFileName();

        $this->info('Kernel loaded from: ' . $kernelFile);
    }
}
