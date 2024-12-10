<?php

namespace App\Jobs;

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
use Modules\Appfiy\Entities\BuildOrder;

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
        /*$this->buildOrder->update(['status' => 'processing']);

        $templateDir = config('app.build_template');

        // Copy template directory
        File::isReadable($templateDir) OR throw new \Exception("Template directory ($templateDir) not found");

        $buildDir = $this->getTargetDir();
        File::copy($templateDir, $buildDir);

        // Download icon_url to assets/icons/launcher_icon.png
        $iconUrl = $this->buildOrder->icon_url;
        $iconPath = implode(DIRECTORY_SEPARATOR, [$buildDir, 'assets', 'icons', 'launcher_icon.png']);
        Storage::put($iconPath, file_get_contents($iconUrl));*/
    }

    /**
     * @return string
     */
    public function getTargetDir(): string
    {
        return Storage::path(implode(DIRECTORY_SEPARATOR, [
            'app',
            'build',
            $this->buildOrder->id . '_' . $this->buildOrder->build_target,
            Str::slug($this->buildOrder->build_number, '_')
        ]));
    }
}
