@extends('admin.theme.default')

@section('content')
<div class="container mt-5 mb-5">
    <h2 class="mb-4 fw-bold">Verifikasi Pengajuan: {{ $users->name }}</h2>
    <p>Periksa dokumen yang telah diunggah oleh {{ $users->name }}</p>

    @if(isset($batasVerifikasi))
    @if(now()->gt($batasVerifikasi))
    <div class="alert alert-danger">
        ⚠️ Batas verifikasi sudah lewat sejak {{ $batasVerifikasi->translatedFormat('d F Y H:i') }}
    </div>
    @else
    <div class="alert alert-warning">
        ⏳ Batas verifikasi sampai {{ $batasVerifikasi->translatedFormat('d F Y H:i') }}
    </div>
    @endif
    @endif

    <form method="POST" action="{{ route('admin.verif.verif_berkas', ['id' => $users->id]) }}">
        @csrf

        <div class="mb-3">
            <label for="status" class="form-label">Status:</label>
            <select name="status" id="status" class="form-select" required onchange="toggleWawancara(this.value)">
                <option value="">-- Pilih Status --</option>
                <option value="diterima" {{ $verifikasi?->status === 'diterima' ? 'selected' : '' }}>Diterima</option>
                <option value="ditolak" {{ $verifikasi?->status === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>

        @if ($verifikasi && $verifikasi->status === 'diterima' && $verifikasi->batas_wawancara)
        <div class="alert alert-info">
            <strong>Batas maksimal wawancara:</strong>
            {{ \Carbon\Carbon::parse($verifikasi->batas_wawancara)->translatedFormat('d F Y') }}
        </div>
        @endif

        <div id="wawancara-fields" style="display: none;">
            <div class="mb-3">
                <label for="tanggal_wawancara" class="form-label">Tanggal dan Jam Wawancara:</label>
                <input
                    type="datetime-local"
                    name="tanggal_wawancara"
                    class="form-control"
                    value="{{ old('tanggal_wawancara', $verifikasi && $verifikasi->tanggal_wawancara 
                        ? $verifikasi->tanggal_wawancara->timezone(config('app.timezone'))->format('Y-m-d\TH:i') 
                        : '') }}"
                    max="{{ optional($batasMax)->format('Y-m-d\TH:i') }}">

                <small class="text-muted">
                    Batas maksimal wawancara: <strong>{{ optional($batasMax)->timezone(config('app.timezone'))->translatedFormat('d F Y H:i') }}</strong>
                </small>
            </div>

            <div class="mb-3">
                <label for="lokasi_wawancara" class="form-label">Lokasi Wawancara:</label>
                <input
                    type="text"
                    name="lokasi_wawancara"
                    class="form-control"
                    value="{{ old('lokasi_wawancara', $verifikasi?->lokasi_wawancara) }}">
            </div>
        </div>

        <div class="mb-3">
            <label for="feedback" class="form-label">Pesan (Opsional):</label>
            <textarea name="feedback" class="form-control" rows="3">{{ old('feedback', $verifikasi?->feedback) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">
            Simpan Verifikasi
        </button>
    </form>
</div>

<div class="countdown"></div>
<script>
    function toggleWawancara(status) {
        const wawancaraFields = document.getElementById('wawancara-fields');
        wawancaraFields.style.display = (status === 'diterima') ? 'block' : 'none';
    }

    window.addEventListener('DOMContentLoaded', () => {
        toggleWawancara(document.getElementById('status').value);
    });

    const endTime = new Date("{{ $batasVerifikasi->format('Y-m-d H:i:s') }}").getTime();
    const timer = setInterval(() => {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
            clearInterval(timer);
            document.getElementById("countdown").innerHTML = "⛔ Waktu habis";
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("countdown").innerHTML =
            `⏳ ${hours}j ${minutes}m ${seconds}d tersisa`;
    }, 1000);
</script>
@endsection