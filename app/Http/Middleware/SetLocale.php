<?php

namespace App\Http\Middleware;

use App\Enums\AppLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale
            ?? AppLocale::tryFrom((string)$request->session()->get("locale"))
            ?? AppLocale::tryFrom($request->getPreferredLanguage(AppLocale::values()));

        if ($locale)
        {
            App::setLocale($locale->value);
        }

        return $next($request);
    }
}
