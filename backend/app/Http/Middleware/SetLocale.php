<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    const ALLOWED = ['en', 'fr', 'es', 'de', 'pt', 'it', 'nl', 'pl', 'ru', 'zh', 'ja', 'ko', 'ar', 'tr'];

    public function handle(Request $request, Closure $next): Response
    {
        $setting = Setting::where('key', 'locale')->first();
        $locale = $setting ? $setting->value : 'en';

        if (in_array($locale, self::ALLOWED)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
