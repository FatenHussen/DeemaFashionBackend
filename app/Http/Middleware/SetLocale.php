<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use App\Models\Language;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $availableLocales = Cache::rememberForever('active_locales', fn () =>
            Language::where('is_active', true)->pluck('code')->toArray()
        );

        $locale = $request->header('Accept-Language');

        if (!in_array($locale, $availableLocales)) {
            $locale = Language::getDefault()?->code ?? 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
