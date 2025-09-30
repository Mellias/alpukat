<?php

namespace App\Http\Controllers\Admin;

use App\Models\SkUkk;
use App\Models\User;
use App\Models\Verifikasi;
use App\Models\Notifikasi;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkUkkController extends Controller
{
    // Menampilkan semua data SK UKK
    public function index()
    {
        $skUkk = \App\Models\SkUkk::with('verifikasi.user')->latest()->paginate(10);

        return view('admin.skukk.index', compact('skUkk'));
    }

    // Menampilkan form unggah SK UKK
    public function create()
    {
        // Ambil user yang telah diverifikasi
        $users = User::whereHas('verifikasi', function ($query) {
            $query->whereNotNull('tanggal_wawancara');
        })->get();

        // Ambil data verifikasi dengan tanggal wawancara supaya dropdown verifikasi valid
        $verifikasis = Verifikasi::whereNotNull('tanggal_wawancara')
            ->where('status', 'diterima')
            ->with('user')
            ->get();

        return view('admin.skukk.create', compact('users', 'verifikasis'));
    }

    // Menyimpan berkas baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'verifikasi_id' => 'required|exists:verifikasis,id',
            'file'          => 'required|mimes:pdf|max:5120',
        ]);

        $verifikasi = Verifikasi::findOrFail($request->verifikasi_id);

        // Pastikan ada tanggal wawancara
        if (!$verifikasi->tanggal_wawancara) {
            return back()->withErrors([
                'verifikasi_id' => 'Tanggal wawancara tidak ditemukan untuk verifikasi ini.'
            ])->withInput();
        }

        // Hitung deadline (default 44 hari kerja (14 hari untuk berita acara + 30 hari untuk SK UKK), demo bisa pakai detik)
        $tanggalWawancara = Carbon::parse($verifikasi->tanggal_wawancara);
        $deadline = $this->deadlineFromInterview($tanggalWawancara);

        if (now()->greaterThan($deadline)) {
            $label = config('app.batas_unggah_sk_seconds')
                ? config('app.batas_unggah_sk_seconds') . ' detik (demo)'
                : (config('app.batas_unggah_sk_days', 44) . ' hari kerja');

            $deadlineStr = $deadline->locale('id')
                ->timezone(config('app.timezone'))
                ->translatedFormat('d F Y H:i');

            return back()->withErrors([
                'file' => "Batas waktu unggah SK UKK sudah lewat ($label). Batas: $deadlineStr WIB."
            ])->withInput();
        }

        // Simpan file & data atomik
        return DB::transaction(function () use ($request, $verifikasi) {
            $file = $request->file('file');
            // Nama file rapi: timestamp_nama-asli.pdf
            $namaBersih    = preg_replace('/\s+/', '_', strtolower($file->getClientOriginalName()));
            $namaFileFinal = time() . '_' . $namaBersih;
            // Simpan file
            $path = $file->storeAs('sk_ukk', $namaFileFinal, 'public');

            SkUkk::create([
                'verifikasi_id' => $request->verifikasi_id,
                'user_id'       => $verifikasi->user_id,
                'file_path'     => $path,
            ]);

            Notifikasi::create([
                'user_id'       => $verifikasi->user_id,
                'verifikasi_id' => $request->verifikasi_id,
                'pesan'         => "Admin telah mengunggah SK UKK Anda.",
                'file_path'     => $path,
                'dibaca'        => 0,
            ]);

            return redirect()
                ->route('admin.skukk.index')
                ->with('success', 'SK UKK berhasil ditambahkan');
        });
    }

    // Hitung deadline (44 hari kerja atau pakai detik kalau demo)
    private function deadlineFromInterview(Carbon $date): Carbon
    {
        $seconds = config('app.batas_unggah_sk_seconds'); // contoh demo: 180
        if (!empty($seconds)) {
            return $date->copy()->addSeconds((int) $seconds);
        }

        $days = (int) config('app.batas_unggah_sk_days', 44);
        return $this->addBusinessDays($date, $days);
    }

    // Tambah n hari kerja dari tanggal tertentu (hari 1 = hari kerja berikutnya)
    private function addBusinessDays(Carbon $date, int $days): Carbon
    {
        $added = 0;
        $d = $date->copy();

        while ($added < $days) {
            $d->addDay();
            if (!$d->isWeekend()) {
                $added++;
            }
        }

        return $d;
    }

    // Download SK
    public function download($id)
    {
        $berkas = SkUkk::findOrFail($id);
        $path = storage_path('app/public/' . $berkas->file_path);

        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($path, basename($berkas->file_path));
    }
}
