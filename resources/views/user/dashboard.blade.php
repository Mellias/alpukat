@extends('user.theme.default')

@section('title', 'Dashboard | ALPUKAT')

@section('content')
{{-- Hero --}}
<section class="hero-section py-5">
  <div class="container">
    <div class="row align-items-center">

      {{-- Kiri: Teks --}}
      <div class="col-md-7 animate-up">
        <h3 class="hero-title"><strong>Selamat Datang di</strong></h3>
        <h1 class="hero-brand"><strong>ALPUKAT</strong></h1>
        <p class="hero-desc">Aplikasi Uji Kelayakan dan Kepatutan</p>

        <a href="{{ asset('files/juklak_ukk.pdf') }}" class="btn btn-primary mb-2" download>
          Download Juklak UKK
        </a>

        @guest
        <br>
        <a href="{{ route('login') }}" class="btn btn-light">Login untuk Pengajuan</a>
        @endguest
      </div>

      {{-- Kanan: Logo Koperasi + Orbit --}}
      <div class="col-md-5 animate-right">
        <div class="hero-anim-pro" aria-hidden="true">
          {{-- orbs latar --}}
          <span class="orb2 orb-a"></span>
          <span class="orb2 orb-b"></span>

          {{-- Logo Koperasi --}}
          <div class="coop-logo">
            <span class="glow-ring"></span>
            <img
              src="{{ asset('images/logo-koperasi1.png') }}"
              alt="Logo Koperasi Indonesia"
              loading="lazy">
          </div>

          {{-- Orbit ikon --}}
          <div class="orbit-pro">
            <span class="avatar chip"><i class="fa fa-user-tie"></i></span>
            <span class="avatar chip"><i class="fa fa-user-check"></i></span>
            <span class="avatar chip"><i class="fa fa-user-shield"></i></span>
          </div>

          {{-- satelit kecil --}}
          <span class="sat dot-1"></span>
          <span class="sat dot-2"></span>
          <span class="sat dot-3"></span>
        </div>
      </div>

    </div>
  </div>
</section>

{{-- Steps --}}
<section class="steps-section">
  <div class="container">
    <h2 class="mb-4" style="font-weight:800">Tata Cara Pengajuan</h2>
    @php
    $steps = [
    ['title'=>'Pendaftaran','desc'=>'Login/Register. Unduh Juklak UKK untuk menyiapkan berkas.','icon'=>'fa-user-plus'],
    ['title'=>'Unggah','desc'=>'Isi formulir dan unggah seluruh berkas persyaratan sesuai juklak.','icon'=>'fa-upload'],
    ['title'=>'Verifikasi','desc'=>'Tim memeriksa kelengkapan dan keabsahan berkas. Lengkapi jika ada catatan.','icon'=>'fa-check-square'],
    ['title'=>'Wawancara','desc'=>'Ikuti wawancara sesuai jadwal. Siapkan dokumen asli bila diminta.','icon'=>'fa-comments'],
    ['title'=>'Hasil','desc'=>'Lihat keputusan UKK (Lulus/Tidak Lulus) di notifikasi. Unduh berita acara.','icon'=>'fa-flag-checkered'],
    ];
    @endphp

    <div class="steps-grid">
      @foreach ($steps as $s)
      @php $delay = sprintf('%.1f', $loop->index * 0.2); @endphp
      <div class="step-box" style="--delay: {{ $delay }}s">
        <div class="step-main">
          <div class="step-main-number">{{ $loop->iteration }}.</div>
          <div class="step-main-title">{{ data_get($s,'title') }}</div>
        </div>

        <div class="step-panel">
          <span class="step-icon"><i class="fa {{ data_get($s,'icon','fa-circle') }}" aria-hidden="true"></i></span>
          <div>
            <div class="step-title">{{ data_get($s,'title') }}</div>
            <p class="step-desc mb-0">{{ data_get($s,'desc') }}</p>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Tentang Kami --}}
<section class="about-section">
  {{-- gelembung latar tepi (dipakai CSS ::before / ::after) --}}
  <div class="container">
    <h2 class="mb-4" style="font-weight:800">Tentang Kami</h2>

    <div class="about-card">
      <div class="row align-items-start g-4">
        <div class="col-lg-7">
          <iframe
            class="about-map"
            title="Lokasi Dinas Koperasi, Usaha Kecil dan Menengah Kepulauan Riau"
            loading="lazy"
            src="https://www.google.com/maps/embed/v1/place?key=AIzaSyA0s1a7phLN0iaD6-UE7m4qP-z21pH0eSc&q=Dinas+Koperasi+Usaha+Kecil+dan+Menengah+Kepulauan+Riau"
            allowfullscreen>
          </iframe>
        </div>

        <div class="col-lg-5 about-text">
          <p>
            Dinas Koperasi, Usaha Kecil dan Menengah Provinsi Kepulauan Riau adalah instansi pemerintah daerah yang bertanggung jawab
            dalam pembinaan, pengembangan, dan pemberdayaan Koperasi dan Usaha Kecil dan Menengah (UKM)
            di wilayah Kepulauan Riau.
          </p>

          <div class="about-list">
            {{-- pakai CSS variable --accent untuk border/icon color --}}
            <div class="about-item email">
              <span class="about-icon"><i class="fa fa-envelope"></i></span>
              <a href="mailto:diskop.kelembagaan@gmail.com">diskop.kelembagaan@gmail.com</a>
            </div>
            <div class="about-item phone">
              <span class="about-icon"><i class="fa fa-phone"></i></span>
              <a href="https://wa.me/628121645651">08121645651 (Rizky)</a>
            </div>
            <div class="about-item phone">
              <span class="about-icon"><i class="fa fa-phone"></i></span>
              <a href="https://wa.me/6281266009800">081266009800 (Awang)</a>
            </div>
            <div class="about-item phone">
              <span class="about-icon"><i class="fa fa-phone"></i></span>
              <a href="https://wa.me/6281266686289">081266686289 (Firmansyah)</a>
            </div>
            <div class="about-item ig">
              <span class="about-icon"><i class="fa fa-instagram"></i></span>
              <a href="https://instagram.com/dkukmkepri" target="_blank" rel="noopener">@dkukmkepri</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection