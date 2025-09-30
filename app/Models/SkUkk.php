<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkUkk extends Model
{
    use HasFactory;

    // kasih tahu Laravel nama tabel yang benar
    protected $table = 'sk_ukk';

    protected $fillable = [
        'verifikasi_id',
        'user_id',
        'file_path',
    ];

    public function verifikasi()
    {
        return $this->belongsTo(Verifikasi::class, 'verifikasi_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
