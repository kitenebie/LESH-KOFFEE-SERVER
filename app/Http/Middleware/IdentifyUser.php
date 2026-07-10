<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IdentifyUser
{
    /**
     * Reads X-User-Id header and authenticates the user
     * so Auth::id() and Auth::user() work in controllers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->header('X-User-Id');

        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                Auth::setUser($user);
            }
        }

        return $next($request);
    }
}
