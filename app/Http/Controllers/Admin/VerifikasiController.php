<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Verifikasi;
use App\Models\Notifikasi;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class VerifikasiController extends Controller
{
    // Tampilkan form verifikasi
    public function verifBerkas($id)
    {
        $users = User::findOrFail($id);
        $dokumen = $users->dokumens ?? collect();

        // Ambil verifikasi (bisa null)
        $verifikasi = Verifikasi::where('user_id', $id)->first();

        // Pastikan user punya dokumen
        if ($dokumen->isEmpty()) {
            return redirect()->back()->with('error', 'Dokumen belum diunggah.');
        }

        // Ambil tanggal upload pertama
        $tanggalUpload = $dokumen->first()->created_at;
        if (!$tanggalUpload) {
            return back()->with('error', 'Tanggal upload dokumen tidak ditemukan.');
        }

        // Ambil config (dari config/app.php)
        $batasSeconds = config('app.batas_verifikasi_seconds');
        $batasDays = config('app.batas_verifikasi_days', 14);

        // Hitung batas verifikasi
        $batasVerifikasi = $tanggalUpload->copy()->addDays((int) $batasDays);
        if (!empty($batasSeconds) && is_numeric($batasSeconds)) {
            $batasVerifikasi = $tanggalUpload->copy()->addSeconds((int) $batasSeconds);
        }

        // Cek apakah batas waktu sudah lewat
        if (now()->gt($batasVerifikasi)) {
            return redirect()->route('admin.verif.hasil_verifikasi')
                ->with('error', 'Batas waktu verifikasi telah habis.');
        }

        // Hitung 30 hari kerja dari hari ini
        $batasMax = $this->hitungBatasWawancara(30);

        return view('admin.verif.verif_berkas', compact('dokumen', 'users', 'verifikasi', 'batasMax', 'batasVerifikasi'));
    }

    public function hitungBatasWawancara($jumlahHariKerja)
    {
        $tanggal = Carbon::now();
        $hariKerja = 0;

        while ($hariKerja < $jumlahHariKerja) {
            if (!$tanggal->isWeekend()) {
                $hariKerja++;
            }
            if ($hariKerja < $jumlahHariKerja) {
                $tanggal->addDay();
            }
        }

        return $tanggal;
    }

    // Simpan hasil verifikasi
    public function postVerifBerkas(Request $request, $id)
    {
        // Validasi input atau data yang masuk
        $request->validate([
            'status' => 'required|in:diterima,ditolak',
            'feedback' => 'nullable|string|max:1000',
            'tanggal_wawancara' => 'required_if:status,diterima|nullable|date_format:Y-m-d\TH:i',
            'lokasi_wawancara' => 'required_if:status,diterima|nullable|string|max:255',
        ]);

        // Cek apakah verifikasi sudah ada
        if (Verifikasi::where('user_id', $id)->exists()) {
            return back()->with('error', 'Verifikasi hanya bisa diberikan sekali.');
        }

        // parsing datetime dari input (lokal Asia/Jakarta)
        $tanggalWawancara = null;
        if ($request->status === 'diterima' && $request->filled('tanggal_wawancara')) {
            // parsing dari format datetime-local (Y-m-d\TH:i)
            $tanggalWawancara = Carbon::createFromFormat(
                'Y-m-d\TH:i',
                $request->input('tanggal_wawancara'),
                config('app.timezone') // pastikan timezone Asia/Jakarta
            );
        }

        // Simpan hasil verifikasi ke dalam variabel
        $batasWawancara = null;

        // Validasi agar tanggal_wawancara tidak melebihi batas
        if ($request->status === 'diterima') {
            $batasWawancara = $this->hitungBatasWawancara(30);

            if ($request->tanggal_wawancara && Carbon::parse($request->tanggal_wawancara)->gt($batasWawancara)) {
                return back()->with('error', 'Tanggal wawancara tidak boleh melebihi batas maksimal (' . $batasWawancara->translatedFormat('d F Y \p\u{k}ul H:i') . 'WIB)');
            }
        }

        $verifikasi = Verifikasi::create([
            'user_id' => $id,
            'status' => $request->status,
            'feedback' => $request->feedback,
            'tanggal_wawancara' => $request->tanggal_wawancara,
            'lokasi_wawancara' => $request->lokasi_wawancara,
            'batas_wawancara' => $batasWawancara,
        ]);

        // Ambil data user berdasarkan id
        $user = User::find($id);

        // Siapkan pesan notifikasi berdasarkan status
        $pesan = $request->status === 'diterima'
            ? "Selamat! Berkas Anda sudah lengkap. Silahkan ikut wawancara pada tanggal " . date('d M Y, H:i', strtotime($request->tanggal_wawancara)) . " di " . $request->lokasi_wawancara . ". " . $request->feedback
            : "Maaf, pengajuan Anda ditolak. " . $request->feedback;

        // Simpan notifikasi
        Notifikasi::create([
            'user_id' => $user->id, //id koperasi
            'verifikasi_id' => $verifikasi->id,
            'pesan' => $pesan,
            'dibaca' => false,
        ]);

        return redirect()->route('admin.verif.hasil_verifikasi', ['id' => $id])->with('success', 'Verifikasi berhasil disimpan.');
    }

    public function hasilVerifikasi()
    {
        $verifikasi = Verifikasi::with('user')->orderBy('updated_at', 'desc')->paginate(3);

        return view('admin.verif.hasil_verifikasi', compact('verifikasi'));
    }
}
