<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SkUkk;
use App\Models\Verifikasi;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Menghitung jumlah berkas yang telah diverifikasi
        $hitungVerifikasi = Verifikasi::count();

        // Menghitung jumlah SK UKK yang telah diunggah
        $hitungSkUkk = SkUkk::count();

        // Menghitung status verifikasi yang diterima dan ditolak
        $statusCounts = Verifikasi::selectRaw("
            SUM(CASE WHEN status = 'diterima' THEN 1 ELSE 0 END) AS diterima,
            SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) AS ditolak
        ")->first();

        // Mengambil hasil
        $hitungDiterima = (int) ($statusCounts->diterima ?? 0);
        $hitungDitolak = (int) ($statusCounts->ditolak ?? 0);

        // Mengirimkan hasil ke view
        return view('admin.dashboard', [
            'countPengajuan' => $hitungVerifikasi,
            'countApproved' => $hitungDiterima,
            'countRejected' => $hitungDitolak,
            'countSkUkk' => $hitungSkUkk,
        ]);
    }

}
