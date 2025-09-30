@extends('admin.theme.default')

@section('title', 'Dashboard Admin | ALPUKAT')

@section('content')
<div class="container py-4">
  {{-- Header & Quick Actions --}}
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
    <h1 class="h4 fw-bold mb-0">Halaman Admin</h1>
  </div>
  <p class="text-muted mb-4">Selamat datang di dashboard admin! Ringkasan kondisi terbaru sistem ALPUKAT.</p>

  {{-- KPI Cards --}}
  @php
  $countPengajuan = $countPengajuan ?? 0;
  $countApproved = $countApproved ?? 0;
  $countRejected = $countRejected ?? 0;
  $countSkUkk = $countSkUkk ?? 0;
  @endphp

  <div class="row g-3 mb-4">
    {{-- Baris pertama --}}
    <div class="col-12 col-md-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="small text-muted">Total Verifikasi</div>
          <div class="display-6 fw-bold">{{ $countPengajuan }}</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="small text-muted">Disetujui</div>
          <div class="display-6 fw-bold text-success">{{ $countApproved }}</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="small text-muted">Ditolak</div>
          <div class="display-6 fw-bold text-danger">{{ $countRejected }}</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="small text-muted">SK UKK yang Telah Diunggah</div>
          <div class="display-6 fw-bold text-info">{{ $countSkUkk }}</div>
        </div>
      </div>
    </div>
  </div>
  @endsection