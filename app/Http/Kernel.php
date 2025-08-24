<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Support\Facades\Schedule;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'setlocale' => \App\Http\Middleware\SetLocale::class,

    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            \Log::info('🟢 Http/kernel Laravel scheduler test triggered at ' . now()->toDateTimeString());
        })->withoutOverlapping()->everyMinute(); // Add withoutOverlapping to be safe

//        $schedule->command('logs:clean-requests --days=30')->daily();
    }
}
