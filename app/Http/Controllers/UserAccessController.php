<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\UserCategoryAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserAccessController extends Controller
{
    /**
     * Показать страницу управления доступом пользователей
     */
    public function index()
    {
        // Только админ может управлять доступом
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Только администратор может управлять доступом');
        }

        $users = User::with(['accessibleCategories.category'])
            ->orderBy('is_admin', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'accessible_categories' => $user->accessibleCategories->pluck('category_id')->toArray(),
            ]);

        $categories = Category::orderBy('order')->get([
            'id',
            'code',
            'name',
        ]);

        return Inertia::render('settings/UserAccess', [
            'users' => $users,
            'categories' => $categories,
        ]);
    }

    /**
     * Обновить доступ пользователя
     */
    public function update(Request $request, User $user)
    {
        // Только админ может управлять доступом
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Только администратор может управлять доступом');
        }

        $validated = $request->validate([
            'is_admin' => 'boolean',
            'accessible_categories' => 'array',
            'accessible_categories.*' => 'exists:categories,id',
        ]);

        // Обновляем роль админа
        if (isset($validated['is_admin'])) {
            $user->update(['is_admin' => $validated['is_admin']]);
        }

        // Обновляем доступ к категориям
        if (isset($validated['accessible_categories'])) {
            // Если админ - очищаем доступ (админ имеет доступ ко всем)
            if ($validated['is_admin']) {
                UserCategoryAccess::where('user_id', $user->id)->delete();
            } else {
                // Синхронизируем доступ
                UserCategoryAccess::where('user_id', $user->id)
                    ->whereNotIn('category_id', $validated['accessible_categories'])
                    ->delete();

                // Добавляем новые
                foreach ($validated['accessible_categories'] as $categoryId) {
                    UserCategoryAccess::firstOrCreate([
                        'user_id' => $user->id,
                        'category_id' => $categoryId,
                    ]);
                }
            }
        }

        return back()->with('success', 'Права доступа обновлены');
    }
}
