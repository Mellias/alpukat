@extends('admin.theme.default')

@section('content')

<div class="container mt-5">
    <h2 class="mb-4 fw-bold">Lihat Persyaratan Berkas</h2>
    <p>Lihat Persyaratan Berkas yang Telah Anda Masukkan</p>

    <a href="{{ route('admin.syarat.tambah_syarat') }}" class="btn btn-primary mb-4">Tambah Syarat</a>

    {{-- TAB NAV --}}
    <ul class="nav nav-tabs" id="syaratTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="koperasi-tab" data-bs-toggle="tab" href="#koperasi" role="tab" aria-controls="koperasi" aria-selected="true">
                📁 Koperasi
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="pengurus-tab" data-bs-toggle="tab" href="#pengurus" role="tab" aria-controls="pengurus" aria-selected="false">
                🗂️ Pengurus Koperasi
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="pengawas-tab" data-bs-toggle="tab" href="#pengawas" role="tab" aria-controls="pengawas" aria-selected="false">
                📂 Pengawas Koperasi
            </a>
        </li>
    </ul>


    {{-- TAB CONTENT --}}
    <div class="tab-content mt-3" id="syaratTabContent">
        {{-- Tab Koperasi --}}
        <div class="tab-pane fade show active" id="koperasi" role="tabpanel" aria-labelledby="koperasi-tab">
            @if($syarat_koperasi->isEmpty())
            <div colspan="6" class="text-center">Belum ada persyaratan untuk kategori koperasi.</div>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-2">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Syarat</th>
                            <th>Waktu Ditambahkan</th>
                            <th style="width: 150px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syarat_koperasi as $index => $syarat)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $syarat->nama_syarat }}</td>
                            <td>{{ $syarat->created_at->format('d-m-Y H:i') }}</td>
                            <td class="text-center" style="white-space: nowrap;">
                                <a href="{{ route('admin.syarat.edit_syarat', $syarat->id) }}"
                                    class="btn btn-sm btn-outline-success rounded-pill me-2"
                                    title="Edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('admin.syarat.hapus_syarat', $syarat->id) }}"
                                    class="btn btn-sm btn-outline-danger rounded-pill"
                                    onclick="return confirm('Apakah Anda yakin?')"
                                    title="Hapus">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Tab Pengurus --}}
        <div class="tab-pane fade" id="pengurus" role="tabpanel" aria-labelledby="pengurus-tab">
            @if($syarat_pengurus->isEmpty())
            <div colspan="6" class="text-center">Belum ada persyaratan untuk kategori pengurus koperasi.</div>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-2">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Syarat</th>
                            <th>Waktu Ditambahkan</th>
                            <th style="width: 150px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syarat_pengurus as $index => $syarat)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $syarat->nama_syarat }}</td>
                            <td>{{ $syarat->created_at->format('d-m-Y H:i') }}</td>
                            <td class="text-center" style="white-space: nowrap;">
                                <a href="{{ route('admin.syarat.edit_syarat', $syarat->id) }}"
                                    class="btn btn-sm btn-outline-success rounded-pill me-2"
                                    title="Edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('admin.syarat.hapus_syarat', $syarat->id) }}"
                                    class="btn btn-sm btn-outline-danger rounded-pill"
                                    onclick="return confirm('Apakah Anda yakin?')"
                                    title="Hapus">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Tab Pengawas --}}
        <div class="tab-pane fade" id="pengawas" role="tabpanel" aria-labelledby="pengawas-tab">
            @if($syarat_pengawas->isEmpty())
            <div colspan="6" class="text-center">Belum ada persyaratan untuk kategori pengawas koperasi.</div>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-2">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Syarat</th>
                            <th>Waktu Ditambahkan</th>
                            <th style="width: 150px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syarat_pengawas as $index => $syarat)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $syarat->nama_syarat }}</td>
                            <td>{{ $syarat->created_at->format('d-m-Y H:i') }}</td>
                            <td class="text-center" style="white-space: nowrap;">
                                <a href="{{ route('admin.syarat.edit_syarat', $syarat->id) }}"
                                    class="btn btn-sm btn-outline-success rounded-pill me-2"
                                    title="Edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('admin.syarat.hapus_syarat', $syarat->id) }}"
                                    class="btn btn-sm btn-outline-danger rounded-pill"
                                    onclick="return confirm('Apakah Anda yakin?')"
                                    title="Hapus">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection