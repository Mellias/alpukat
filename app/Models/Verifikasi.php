<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verifikasi extends Model
{
    use HasFactory;

    protected $table = 'verifikasis';

    protected $fillable = [
        'user_id',
        'status',
        'feedback',
        'tanggal_wawancara',
        'lokasi_wawancara',
    ];

    protected $casts = [
        'tanggal_wawancara' => 'datetime',
    ];

    // Relasi ke user (koperasi)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function SkUkk()
    {
        return $this->hasMany(SkUkk::class);
    }
}
