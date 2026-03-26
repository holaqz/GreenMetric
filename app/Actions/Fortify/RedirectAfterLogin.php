<?php

namespace App\Actions\Fortify;

use App\Models\Cycle;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Http\Responses\LoginResponse;

class RedirectAfterLogin extends LoginResponse
{
    /**
     * Определить, куда перенаправить пользователя после входа
     */
    public function to($request = null): string
    {
        // Если есть активные циклы — редирект на последний открытый
        $activeCycle = Cycle::whereIn('status', ['open', 'draft'])
            ->orderBy('year', 'desc')
            ->first();

        if ($activeCycle) {
            // Редирект на редактирование цикла (категория WR по умолчанию)
            return route('cycles.show', ['cycle' => $activeCycle, 'category' => 'WR']);
        }

        // Если циклов нет — редирект на список циклов (чтобы создать новый)
        return route('cycles.index');
    }
}
