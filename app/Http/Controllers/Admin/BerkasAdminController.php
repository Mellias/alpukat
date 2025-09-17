<?php

namespace App\Http\Controllers\Admin;

use App\Models\BerkasAdmin;
use App\Models\User;
use App\Models\Verifikasi;
use App\Models\Notifikasi;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BerkasAdminController extends Controller
{
    // Menampilkan semua data berkas admin
    public function index()
    {
        $data = BerkasAdmin::with(['verifikasi.user'])->latest()->paginate(3);

        return view('admin.berkas.index', compact('data'));
    }

    // Menampilkan form tambah berkas
    public function create()
    {
        // Ambil user yang telah diverifikasi
        $users = User::whereHas('verifikasi', function ($query) {
            $query->whereNotNull('tanggal_wawancara');
        })->get();

        // Ambil data verifikasi dengan tanggal wawancara supaya dropdown verifikasi valid
        $verifikasis = Verifikasi::whereNotNull('tanggal_wawancara')
            ->where('status', 'diterima'    )
            ->with('user')->get();

        return view('admin.berkas.create', compact('users', 'verifikasis'));
    }

    // Menyimpan berkas baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'verifikasi_id' => 'required|exists:verifikasis,id',
            'jenis_surat'   => 'required|in:berita_acara,sk_ukk',
            'file'          => 'required|mimes:pdf|max:5120',
        ]);

        $verifikasi = Verifikasi::findOrFail($request->verifikasi_id);

        // Pastikan ada tanggal wawancara
        if (!$verifikasi->tanggal_wawancara) {
            return back()->withErrors([
                'verifikasi_id' => 'Tanggal wawancara tidak ditemukan untuk verifikasi ini.'
            ])->withInput();
        }

        // Cek status berkas yang sudah ada
        $existsBA = BerkasAdmin::where('verifikasi_id', $verifikasi->id)
            ->where('jenis_surat', 'berita_acara')
            ->exists();

        $existsSK = BerkasAdmin::where('verifikasi_id', $verifikasi->id)
            ->where('jenis_surat', 'sk_ukk')
            ->exists();

        // Aturan urutan & duplikasi:
        if ($request->jenis_surat === 'berita_acara') {
            if ($existsBA) {
                return back()->withErrors([
                    'jenis_surat' => 'Berita Acara sudah pernah diunggah untuk verifikasi ini.'
                ])->withInput();
            }
            // Deadline hanya untuk Berita Acara
            $tanggalWawancara = Carbon::parse($verifikasi->tanggal_wawancara);
            $batas = $this->uploadDeadline($tanggalWawancara);
            $now   = now();
            if ($now->greaterThan($batas)) {
                $labelDurasi = config('app.batas_unggah_wawancara_seconds')
                    ? config('app.batas_unggah_wawancara_seconds') . ' detik (demo)'
                    : config('app.batas_unggah_wawancara_days', 30) . ' hari kerja';

                $batasStr = $batas->locale('id')->timezone(config('app.timezone'))
                    ->translatedFormat('d F Y H:i');

                return back()->withErrors([
                    'file' => "Batas waktu unggah Berita Acara sudah lewat ($labelDurasi). "
                        . "Batas unggah: $batasStr WIB."
                ])->withInput();
            }
        } else { // sk_ukk
            if (!$existsBA) {
                return back()->withErrors([
                    'jenis_surat' => 'SK UKK hanya dapat diunggah setelah Berita Acara diunggah.'
                ])->withInput();
            }

            if ($existsSK) {
                return back()->withErrors([
                    'jenis_surat' => 'SK UKK sudah pernah diunggah untuk verifikasi ini.'
                ])->withInput();
            }

            // Ambil BA (untuk ambil created_at sebagai basis deadline SK)
            $berkasBA = BerkasAdmin::where('verifikasi_id', $verifikasi->id)
                ->where('jenis_surat', 'berita_acara')
                ->latest('created_at')
                ->first();

            if (!$berkasBA) {
                return back()->withErrors([
                    'jenis_surat' => 'Unggah Berita Acara terlebih dahulu.'
                ])->withInput();
            }

            // Hitung deadline SK dari waktu upload BA
            $baUploadedAt = $berkasBA->created_at instanceof \Carbon\Carbon
                ? $berkasBA->created_at
                : \Carbon\Carbon::parse($berkasBA->created_at);

            $deadlineSk = $this->skUkkDeadlineFromBA($baUploadedAt);

            if (now()->greaterThan($deadlineSk)) {
                $labelSk = config('app.batas_unggah_sk_seconds')
                    ? config('app.batas_unggah_sk_seconds') . ' detik (demo)'
                    : (config('app.batas_unggah_sk_days', 30) . ' hari kerja');

                $deadlineStr = $deadlineSk->locale('id')->timezone(config('app.timezone'))
                    ->translatedFormat('d F Y H:i');

                return back()->withErrors([
                    'jenis_surat' => "Batas waktu untuk mengunggah SK UKK sudah lewat ($labelSk). Batas: $deadlineStr WIB."
                ])->withInput();
            }
        }

        // Simpan file & data atomik
        return DB::transaction(function () use ($request, $verifikasi) {
            $file = $request->file('file');

            // Nama file rapi: timestamp_nama-asli.pdf
            $namaBersih    = preg_replace('/\s+/', '_', strtolower($file->getClientOriginalName()));
            $namaFileFinal = time() . '_' . $namaBersih;

            // Simpan file
            $path = $file->storeAs('berkas_admin', $namaFileFinal, 'public');

            BerkasAdmin::create([
                'verifikasi_id' => $request->verifikasi_id,
                'user_id'       => $verifikasi->user_id,
                'jenis_surat'   => $request->jenis_surat,
                'file_path'     => $path, 
            ]);

            // Notifikasi
            $pesanNotif = $request->jenis_surat === 'sk_ukk'
                ? "Admin telah mengunggah SK UKK Anda."
                : "Admin telah mengunggah Berita Acara sebagai hasil wawancara Anda.";

            Notifikasi::create([
                'user_id'       => $verifikasi->user_id,
                'verifikasi_id' => $request->verifikasi_id,
                'pesan'         => $pesanNotif,
                'file_path'     => $path,
                'dibaca'        => 0,
            ]);

            return redirect()
                ->route('admin.berkas-admin.index')
                ->with('success', 'Berkas berhasil ditambahkan');
        });
    }

    // Hitung batas unggah dari tanggal wawancara.
    // - Jika config 'seconds' diisi (mode demo), pakai detik kalender.
    // - Jika tidak, pakai 'hari kerja' (skip Sabtu/Minggu) sebanyak 'days'.
    private function uploadDeadline(Carbon $tanggalWawancara): Carbon
    {
        $seconds = config('app.batas_unggah_wawancara_seconds');
        if (!empty($seconds)) {
            return $tanggalWawancara->copy()->addSeconds((int) $seconds);
        }

        $days = (int) config('app.batas_unggah_wawancara_days', 30);

        return $this->addBusinessDays($tanggalWawancara, $days);
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

    private function skUkkDeadlineFromBA(Carbon $baUploadedAt): Carbon
    {
        // Mode demo: jika di config ada seconds → pakai detik
        $skSeconds = config('app.batas_unggah_sk_seconds'); // contoh: 180 (3 menit)
        if (!empty($skSeconds)) {
            return $baUploadedAt->copy()->addSeconds((int) $skSeconds);
        }

        // Produksi: hari kerja
        $skDays = (int) config('app.batas_unggah_sk_days', 30); // default 30 hari kerja
        return $this->addBusinessDays($baUploadedAt, $skDays);
    }

    public function download($id)
    {
        $berkas = BerkasAdmin::findOrFail($id);

        $path = ltrim($berkas->file_path ?? '', '/');
        $path = preg_replace('#^storage/#', '', $path);

        if ($path !== '' && strpos($path, '/') === false) {
            $path = 'berkas_admin/'.$path;
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Ambil path absolut di filesystem kontainer
        $absolutePath = $disk->path($path);

        // Paksa unduh (download). Untuk preview pakai response()->file($absolutePath)
        return response()->download($absolutePath, basename($path));
    }
}
