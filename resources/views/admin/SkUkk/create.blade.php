@extends('admin.theme.default')

@section('title', 'Unggah SK UKK')

@section('content')
<div class="container mt-5 mb-5">
    <h1 class="mb-4 fw-bold">Tambah SK UKK</h1>
    <p>Kirimkan Surat Keputusan lulus Uji Kelayakan dan Kepatutan (SK UKK) di sini.</p>

    {{-- Error message --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="formTambahSkUkk" action="{{ route('admin.skukk.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf

        {{-- Pilih koperasi --}}
        <div class="mb-3">
            <label for="verifikasi_id" class="form-label">Pilih Koperasi yang Telah Diwawancarai & Diterima</label>
            <select name="verifikasi_id" id="verifikasi_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Koperasi --</option>
                @foreach($verifikasis as $verifikasi)
                    <option 
                        value="{{ $verifikasi->id }}"
                        data-wawancara="{{ $verifikasi->tanggal_wawancara ? \Carbon\Carbon::parse($verifikasi->tanggal_wawancara)->timezone(config('app.timezone'))->format('c') : '' }}">
                        {{ $verifikasi->user->name ?? 'User' }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback">Harap pilih koperasi.</div>
        </div>

        {{-- Info tanggal wawancara + deadline --}}
        <div class="mb-3">
            <label for="tanggal_wawancara" class="form-label">Tanggal Wawancara</label>
            <input type="text" id="tanggal_wawancara" class="form-control" readonly>
            <div class="form-text" id="deadlineInfo"></div>
        </div>

        {{-- Upload file --}}
        <div class="mb-3">
            <label for="file" class="form-label">File SK UKK (PDF)</label>
            <input type="file" name="file" id="file" class="form-control" accept="application/pdf" required>
            <small class="text-muted">Hanya file PDF, ukuran maksimal 5 MB</small>
            <div class="invalid-feedback">Mohon pilih file PDF ukuran maksimal 5 MB.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.skukk.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>

    {{-- Config batas unggah --}}
    <div id="cfg"
        data-sk-days="{{ (int) config('app.batas_unggah_sk_days', 44) }}"
        data-sk-seconds="{{ config('app.batas_unggah_sk_seconds') }}">
    </div>

    <script>
        (function() {
            'use strict';

            const form = document.getElementById('formTambahSkUkk');
            const selectVerifikasi = document.getElementById('verifikasi_id');
            const wawancaraField = document.getElementById('tanggal_wawancara');
            const deadlineInfo = document.getElementById('deadlineInfo');
            const fileInput = document.getElementById('file');
            const maxSize = 5 * 1024 * 1024;

            const cfgEl = document.getElementById('cfg');
            const SK_DAYS = Number(cfgEl.dataset.skDays) || 44;
            const SK_SECONDS_RAW = cfgEl.getAttribute('data-sk-seconds');
            const SK_DEMO_SECONDS = (SK_SECONDS_RAW !== null && SK_SECONDS_RAW !== '') ? Number(SK_SECONDS_RAW) : null;
            const SK_IS_DEMO = Number.isFinite(SK_DEMO_SECONDS);

            let deadline = null;

            function addBusinessDays(start, days) {
                const d = new Date(start.getTime());
                let added = 0;
                while (added < days) {
                    d.setDate(d.getDate() + 1);
                    const day = d.getDay();
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

            function updateInfo() {
                const opt = selectVerifikasi.options[selectVerifikasi.selectedIndex];
                if (!opt) {
                    wawancaraField.value = '';
                    deadlineInfo.textContent = '';
                    deadline = null;
                    return;
                }

                const wawancaraIso = opt.getAttribute('data-wawancara');
                if (!wawancaraIso) {
                    wawancaraField.value = '';
                    deadlineInfo.textContent = '';
                    deadline = null;
                    return;
                }

                const wawancaraDate = new Date(wawancaraIso);
                wawancaraField.value = formatID(wawancaraDate) + ' WIB';

                if (SK_IS_DEMO) {
                    deadline = new Date(wawancaraDate.getTime() + SK_DEMO_SECONDS * 1000);
                    deadlineInfo.textContent = `Batas unggah (demo ${humanizeDemo(SK_DEMO_SECONDS)}): ${formatID(deadline)} WIB`;
                } else {
                    deadline = addBusinessDays(wawancaraDate, SK_DAYS);
                    deadlineInfo.textContent = `Batas unggah (${SK_DAYS} hari kerja): ${formatID(deadline)} WIB`;
                }
            }

            selectVerifikasi.addEventListener('change', updateInfo);

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

                // Blokir jika lewat deadline
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

        })();
    </script>
</div>
@endsection
