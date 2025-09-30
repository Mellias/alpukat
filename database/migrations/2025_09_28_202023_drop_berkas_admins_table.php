<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('berkas_admins');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::create('berkas_admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verifikasi_id')->constrained('verifikasis')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('jenis_surat', ['berita_acara', 'sk_ukk']);
            $table->string('file_path');
            $table->timestamps();
        });
    }
};
