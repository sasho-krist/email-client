<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, MustVerifyEmailTrait, Notifiable;

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
        ];
    }

    /**
     * Първо име за показване в навигацията.
     */
    public function firstName(): string
    {
        $name = trim((string) $this->name);

        if ($name === '') {
            return explode('@', (string) $this->email)[0] ?: '?';
        }

        return explode(' ', $name, 2)[0];
    }

    /**
     * Публичен URL към каченото изображение или null.
     */
    public function avatarPublicUrl(): ?string
    {
        if ($this->avatar_path === null || $this->avatar_path === '') {
            return null;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }

    public function emailAccounts(): HasMany
    {
        return $this->hasMany(EmailAccount::class);
    }

    public function mailPreference(): HasOne
    {
        return $this->hasOne(UserMailPreference::class);
    }

    public function mailPreferenceOrCreate(): UserMailPreference
    {
        return UserMailPreference::firstOrCreate(
            ['user_id' => $this->id],
            ['inbox_group_by' => 'none', 'reply_include_quote' => true, 'reply_top_posting' => false],
        );
    }
}
