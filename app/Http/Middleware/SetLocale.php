<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    private $locales = ['ar', 'en', 'bn'];
    public function handle($request, Closure $next)
    {
        if (array_search($request->segment(1), $this->locales) === false) {
            return route('home');
        }
        URL::defaults(['locale' => $request->segment(1)]);

        App::setLocale($request->segment(1));

        return $next($request);
    }
}
