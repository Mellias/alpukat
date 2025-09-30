@extends('admin.theme.default')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4 fw-bold">Daftar SK UKK</h1>
    <p>Berikut adalah daftar Surat Keputusan (SK UKK) yang sudah diunggah.</p>

    <a href="{{ route('admin.skukk.create') }}" class="btn btn-primary mb-4">Tambah SK UKK</a>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>Nama Koperasi</th>
                    <th>Tanggal Unggah</th>
                    <th>File</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($skUkk as $sk)
                <tr>
                    <td>{{ $sk->verifikasi->user->name ?? '-' }}</td>
                    <td>{{ $sk->created_at->format('d-m-Y H:i') }}</td>
                    <td class="text-center">
                        <a href="{{ asset('storage/sk_ukk/' . $sk->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            Lihat
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.skukk.download', $sk->id) }}" class="btn btn-success btn-sm me-1">Download</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada SK UKK yang diunggah.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $skUkk->links() }}
    </div>
</div>
@endsection
