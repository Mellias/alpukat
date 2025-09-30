<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use App\Models\Verifikasi;
use App\Models\Notifikasi;
use App\Models\Dokumen;
use App\Models\SkUkk;

// class User extends Authenticatable implements MustVerifyEmail
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'avatar_path',
        'alamat',
    ];

    /**
     * Kolom yang disembunyikan
     */
    protected $hidden = [
        'password',
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
        ];
    }

    // Relasi
    public function dokumens()
    {
        return $this->hasMany(Dokumen::class, 'user_id');
    }

    public function verifikasi()
    {
        return $this->hasOne(Verifikasi::class);
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class);
    }

    public function SkUkk()
    {
        return $this->hasMany(SkUkk::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar_path
            ? asset('storage/' . $this->avatar_path)
            : asset('resources/front-end/images/logo-koperasi.png');
    }
}
