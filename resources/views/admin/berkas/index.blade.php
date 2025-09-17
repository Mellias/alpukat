@extends('admin.theme.default')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4 fw-bold">Lihat Berkas</h1>
    <p>Berikut adalah daftar berkas yang telah Anda kirim</p>

    <a href="{{ route('admin.berkas-admin.create') }}" class="btn btn-primary mb-4">Tambah Berkas</a>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>Nama Berkas</th>
                    <th>Nama Koperasi</th>
                    <th>Waktu Ditambahkan</th>
                    <th>File</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $berkas)
                <tr>
                    <td>
                        {{ $berkas->nama_berkas ?? ucfirst(str_replace('_', ' ', $berkas->jenis_surat)) }}
                    </td>
                    <td>
                        {{ $berkas->verifikasi->user->name ?? '-' }}
                    </td>
                    <td>
                        {{ $berkas->created_at->format('d-m-Y H:i') }}
                    </td>
                    <td class="text-center">
                        <a href="{{ asset('storage/berkas_admin/' . $berkas->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            Lihat
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.berkas-admin.download', $berkas->id) }}" class="btn btn-success btn-sm me-1">Download</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada berkas yang dikirim.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->
    <div class="mt-3">
        {{ $data->links() }}
    </div>
</div>
@endsection