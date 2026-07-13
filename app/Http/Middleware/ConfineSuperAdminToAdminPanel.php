<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfineSuperAdminToAdminPanel
{
    protected array $allowedRoutes = ['admin.*', 'filament.*', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->hasRole('super_admin')) {
            // Skip Livewire internal requests (they use POST to /livewire/*)
            if ($request->is('livewire/*') || $request->header('X-Livewire')) {
                return $next($request);
            }

            $allowed = collect($this->allowedRoutes)
                ->contains(fn ($pattern) => $request->routeIs($pattern));

            // Also allow Filament panel path prefix
            $isFilamentPath = str_starts_with($request->path(), 'shop');

            if (! $allowed && ! $isFilamentPath) {
                return redirect('/shop');
            }
        }

        return $next($request);
    }
}
