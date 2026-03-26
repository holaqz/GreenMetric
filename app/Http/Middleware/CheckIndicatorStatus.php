<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIndicatorStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Админ может редактировать всё
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Получаем response из маршрута
        $response = $request->route('response');
        
        if ($response) {
            // Запрещаем редактирование утверждённых и находящихся на проверке
            if (in_array($response->status, ['approved', 'ready_for_review'])) {
                return back()->with('error', 'Нельзя редактировать индикатор со статусом "' . $response->status . '"');
            }
        }

        return $next($request);
    }
}
