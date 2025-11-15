<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Atributos asignables en masa.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',       // por compatibilidad antigua (si existiera)
        'avatar_path',  // nueva ruta de imagen en /storage
    ];

    /**
     * Atributos ocultos para serialización.
     */
    protected $hidden = ['password','remember_token'];

    /**
     * Casts.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // permite asignar texto plano y lo hashea
        ];
    }

    public function settings()
    {
        return $this->hasOne(\App\Models\UserSetting::class);
    }

    /**
     * URL pública del avatar (o imagen por defecto).
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path && Storage::disk('public')->exists($this->avatar_path)) {
            return Storage::url($this->avatar_path);
        }
        // fallback genérico
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=2fca6c&color=fff&size=128';
    }
}