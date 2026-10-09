<?php

namespace Novay\MiniOS\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetDesktopLocale
{
    /**
     * Handle an incoming request and synchronize active desktop locale.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (function_exists('os_setting')) {
            $locale = os_setting('locale_time.locale');
            if (is_string($locale) && in_array(strtolower($locale), ['id', 'en'], true)) {
                app()->setLocale(strtolower($locale));
            }
        }

        return $next($request);
    }
}
