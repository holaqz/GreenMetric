<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Проверка, является ли пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Получить категории, к которым у пользователя есть доступ
     */
    public function accessibleCategories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserCategoryAccess::class)->with('category');
    }

    /**
     * Проверка, есть ли доступ к категории
     */
    public function hasCategoryAccess(string $categoryCode): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->accessibleCategories()
            ->whereHas('category', fn($q) => $q->where('code', $categoryCode))
            ->exists();
    }

    /**
     * Получить коды категорий, к которым есть доступ
     */
    public function getAccessibleCategoryCodes(): array
    {
        if ($this->isAdmin()) {
            return Category::pluck('code')->toArray();
        }

        return $this->accessibleCategories()
            ->get()
            ->pluck('category.code')
            ->toArray();
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(UniversityProfile::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(IndicatorResponse::class, 'created_by');
    }

    public function updatedResponses(): HasMany
    {
        return $this->hasMany(IndicatorResponse::class, 'updated_by');
    }

    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(EvidenceFile::class, 'uploaded_by');
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(ChangeLog::class);
    }
}
