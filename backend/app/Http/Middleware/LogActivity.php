<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function handle(Request $request, Closure $next, string $action = null): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        if ($request->user() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $action = $request->route()?->getActionMethod() ?? $request->method();
            $description = $request->method() . ' ' . $request->path();

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => $action,
                'description' => $description,
                'subject_type' => null,
                'subject_id' => null,
                'properties' => $request->except(['password', 'password_confirmation', 'pin', 'token']),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    }
}
