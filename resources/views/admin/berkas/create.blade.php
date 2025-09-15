@extends('admin.theme.default')

@section('content')
<div class="container mt-5 mb-5">
    <h1 class="mb-4 fw-bold">Tambah Berkas</h1>
    <p>Masukkan Berita Acara terlebih dahulu, lalu SK UKK.</p>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="formTambahBerkas" action="{{ route('admin.berkas-admin.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf

        {{-- Pilih Koperasi --}}
        <div class="mb-3">
            <label for="verifikasi_id" class="form-label">Pilih Koperasi yang Telah Diwawancarai</label>
            <select name="verifikasi_id" id="verifikasi_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Koperasi --</option>
                @foreach($verifikasis as $verifikasi)
                @php
                // Cari BA terbaru (jika ada)
                $baLatest = $verifikasi->berkasAdmin
                ->where('jenis_surat','berita_acara')
                ->sortByDesc('created_at')
                ->first();
                @endphp
                <option
                    value="{{ $verifikasi->id }}"
                    {{-- BA dari tanggal wawancara --}}
                    data-datetime="{{ $verifikasi->tanggal_wawancara ? \Carbon\Carbon::parse($verifikasi->tanggal_wawancara)->timezone(config('app.timezone'))->format('c') : '' }}"
                    {{-- Flag sudah punya BA? --}}
                    data-has-ba="{{ $baLatest ? 'true' : 'false' }}"
                    {{-- SK dari created_at BA --}}
                    data-ba-at="{{ $baLatest?->created_at?->timezone(config('app.timezone'))->format('c') ?? '' }}">
                    {{ $verifikasi->user->name ?? 'User' }}
                </option>
                @endforeach
            </select>
            <div class="invalid-feedback">Harap pilih koperasi yang sudah diwawancara.</div>
        </div>

        {{-- Tanggal Wawancara + Info Deadline --}}
        <div class="mb-3">
            <label for="tanggal_wawancara" class="form-label">Waktu Wawancara</label>
            <input type="text" id="tanggal_wawancara" class="form-control" readonly>
            <div class="form-text" id="batasInfo"></div>
        </div>

        {{-- Jenis Surat --}}
        <div class="mb-3">
            <label for="jenis_surat" class="form-label">Jenis Surat</label>
            <select name="jenis_surat" id="jenis_surat" class="form-select" required>
                <option value="" disabled selected>-- Pilih Jenis Surat --</option>
                <option value="berita_acara" {{ old('jenis_surat') == 'berita_acara' ? 'selected' : '' }}>Berita Acara</option>
                <option value="sk_ukk" {{ old('jenis_surat') == 'sk_ukk' ? 'selected' : '' }}>SK UKK</option>
            </select>
            <div class="invalid-feedback">Jenis surat wajib dipilih.</div>
            <div class="form-text" id="jenisHelp"></div>
        </div>

        {{-- Upload File --}}
        <div class="mb-3">
            <label for="file" class="form-label">File PDF</label>
            <input type="file" name="file" id="file" class="form-control" accept="application/pdf" required>
            <small class="text-muted">Hanya file PDF, ukuran maksimal 5 MB</small>
            <div class="invalid-feedback" id="fileError">Mohon pilih file PDF yang ukurannya maksimal 5 MB.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.berkas-admin.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>

    {{-- Konfigurasi untuk hitung deadline (BA & SK) --}}
    <div id="cfg"
        data-days="{{ (int) config('app.batas_unggah_wawancara_days', 30) }}"
        data-seconds="{{ config('app.batas_unggah_wawancara_seconds') }}"
        data-sk-days="{{ (int) config('app.batas_unggah_sk_days', 30) }}"
        data-sk-seconds="{{ config('app.batas_unggah_sk_seconds') }}">
    </div>

    {{-- Script --}}
    <script>
        (function() {
            'use strict';

            const form = document.getElementById('formTambahBerkas');
            const selectVerifikasi = document.getElementById('verifikasi_id');
            const selectJenis = document.getElementById('jenis_surat');
            const fileInput = document.getElementById('file');
            const batasInfo = document.getElementById('batasInfo');
            const jenisHelp = document.getElementById('jenisHelp');
            const maxSize = 5 * 1024 * 1024; // 5 MB

            const cfgEl = document.getElementById('cfg');
            // BA: dari tanggal wawancara
            const BA_DAYS = Number(cfgEl.dataset.days) || 30;
            const BA_SECONDS_RAW = cfgEl.getAttribute('data-seconds');
            const BA_DEMO_SECONDS = (BA_SECONDS_RAW !== null && BA_SECONDS_RAW !== '') ? Number(BA_SECONDS_RAW) : null;
            const BA_IS_DEMO = Number.isFinite(BA_DEMO_SECONDS);
            // SK: dari created_at Berita Acara
            const SK_DAYS = Number(cfgEl.dataset.skDays) || 30;
            const SK_SECONDS_RAW = cfgEl.getAttribute('data-sk-seconds');
            const SK_DEMO_SECONDS = (SK_SECONDS_RAW !== null && SK_SECONDS_RAW !== '') ? Number(SK_SECONDS_RAW) : null;
            const SK_IS_DEMO = Number.isFinite(SK_DEMO_SECONDS);

            function addBusinessDays(start, days) {
                const d = new Date(start.getTime()); // <- fix: jangan pakai 'date'
                let added = 0;
                while (added < days) {
                    d.setDate(d.getDate() + 1);
                    const day = d.getDay(); // 0=Min,6=Sab
                    if (day !== 0 && day !== 6) added++;
                }
                return d;
            }

            function formatID(d) {
                return d.toLocaleString('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            function humanizeDemo(seconds) {
                if (!Number.isFinite(seconds)) return '';
                if (seconds % 60 === 0) return (seconds / 60) + ' menit';
                return seconds + ' detik';
            }

            let deadline = null;

            function updateJenisOptions() {
                const opt = selectVerifikasi.options[selectVerifikasi.selectedIndex];
                const hasBA = opt?.getAttribute('data-has-ba') === 'true';
                const optBA = selectJenis.querySelector('option[value="berita_acara"]');
                const optSK = selectJenis.querySelector('option[value="sk_ukk"]');

                if (hasBA) {
                    // BA sudah ada → hanya SK yang aktif
                    optBA.disabled = true;
                    optSK.disabled = false;
                    if (selectJenis.value !== 'sk_ukk') selectJenis.value = 'sk_ukk';
                    jenisHelp.textContent = 'Berita Acara sudah ada. Silakan unggah SK UKK dalam tenggat yang ditentukan.';
                } else {
                    // BA belum ada → hanya BA yang aktif
                    optBA.disabled = false;
                    optSK.disabled = true;
                    if (selectJenis.value !== 'berita_acara') selectJenis.value = 'berita_acara';
                    jenisHelp.textContent = 'Unggah Berita Acara terlebih dahulu. SK UKK akan aktif setelah Berita Acara ada.';
                }
            }

            function updateTanggalWawancaraField() {
                const iso = selectVerifikasi.options[selectVerifikasi.selectedIndex]?.getAttribute('data-datetime') || '';
                document.getElementById('tanggal_wawancara').value = iso ? (formatID(new Date(iso)) + ' WIB') : '';
            }

            function updateDeadlineText() {
                const opt = selectVerifikasi.options[selectVerifikasi.selectedIndex];
                if (!opt) {
                    batasInfo.textContent = '';
                    deadline = null;
                    return;
                }

                const wawancaraIso = opt.getAttribute('data-datetime') || '';
                const baIso = opt.getAttribute('data-ba-at') || '';
                const jenis = selectJenis.value;

                let base = null,
                    label = '';

                if (jenis === 'berita_acara') {
                    if (!wawancaraIso) {
                        batasInfo.textContent = '';
                        deadline = null;
                        return;
                    }
                    base = new Date(wawancaraIso);
                    if (BA_IS_DEMO) {
                        label = `demo ${humanizeDemo(BA_DEMO_SECONDS)}`;
                        deadline = new Date(base.getTime() + BA_DEMO_SECONDS * 1000);
                    } else {
                        label = `${BA_DAYS} hari kerja`;
                        deadline = addBusinessDays(base, BA_DAYS);
                    }
                } else if (jenis === 'sk_ukk') {
                    if (!baIso) {
                        batasInfo.textContent = 'Unggah Berita Acara terlebih dahulu.';
                        deadline = null;
                        return;
                    }
                    base = new Date(baIso);
                    if (SK_IS_DEMO) {
                        label = `demo ${humanizeDemo(SK_DEMO_SECONDS)}`;
                        deadline = new Date(base.getTime() + SK_DEMO_SECONDS * 1000);
                    } else {
                        label = `${SK_DAYS} hari kerja`;
                        deadline = addBusinessDays(base, SK_DAYS);
                    }
                }

                batasInfo.textContent = deadline ?
                    `Batas unggah (${label}): ${formatID(deadline)} WIB` :
                    '';
            }

            selectVerifikasi.addEventListener('change', () => {
                updateJenisOptions();
                updateTanggalWawancaraField();
                updateDeadlineText();
            });

            selectJenis.addEventListener('change', updateDeadlineText);

            form.addEventListener('submit', function(event) {
                fileInput.classList.remove('is-invalid');

                // Validasi file
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                    if (!isPdf || file.size > maxSize) {
                        fileInput.classList.add('is-invalid');
                        event.preventDefault();
                        event.stopPropagation();
                        return;
                    }
                }

                // Helper UI: blokir jika lewat deadline
                if (deadline && new Date() > deadline) {
                    alert('Batas waktu unggah sudah lewat.');
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);

            // init jika ada nilai lama
            if (selectVerifikasi.value) {
                selectVerifikasi.dispatchEvent(new Event('change'));
            }
        })();
    </script>
</div>
@endsection