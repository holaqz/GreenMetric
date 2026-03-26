<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCategoryAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $categoryCode): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Админ имеет доступ ко всем категориям
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Проверяем доступ к категории
        if (!$user->hasCategoryAccess($categoryCode)) {
            abort(403, 'Нет доступа к этой категории');
        }

        return $next($request);
    }
}
