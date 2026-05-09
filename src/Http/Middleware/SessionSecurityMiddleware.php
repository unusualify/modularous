<?php

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionSecurityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! modularousConfig('security.enabled', false)) {
            return $next($request);
        }

        if (! Auth::check()) {
            return $next($request);
        }

        $idleTimeoutMinutes = (int) modularousConfig('security.session.idle_timeout_minutes', 60);
        $lastSeenAt = (int) $request->session()->get('security_last_seen_at', time());

        if ((time() - $lastSeenAt) > ($idleTimeoutMinutes * 60)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session timed out. Please login again.'], 401);
            }

            return redirect()->route('admin.login.form')->withErrors([
                'session' => 'Session timed out. Please login again.',
            ]);
        }

        $request->session()->put('security_last_seen_at', time());

        return $next($request);
    }
}
