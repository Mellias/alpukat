<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | ALPUKAT</title>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    integrity="sha384-..." crossorigin="anonymous">
  <link rel="shortcut icon" href="{{ asset('images/logo_kepri.png') }}" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    :root {
      --hero-a: #1f2a7a;
      --hero-b: #4456d1;
      --hero-c: #7e8af0;
      --text: #fff;

      --input-bg: #f9fbff;
      --input-bd: #e3e7ff;
      --input-bd-focus: #c9d2ff;
      --input-text: #111;
      --placeholder: #a9b6cc;
      --placeholder-focus: #c3ccda;

      --btn: #23349E;
      --btn-hover: #1a2877;
      --danger: #b42318;
    }

    html,
    body {
      height: 100%;
      margin: 0;
    }

    body {
      background: #10205e;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    /* ===== FULL HERO LOGIN ===== */
    .login-hero {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 56px 16px;
      min-height: 100svh;
      min-height: 100vh;
      background: linear-gradient(135deg, var(--hero-a) 0%, var(--hero-b) 60%, var(--hero-c) 100%);
      color: var(--text);
      overflow: hidden;
    }

    .decor {
      position: absolute;
      border-radius: 999px;
      background: rgba(255, 255, 255, .08);
      pointer-events: none
    }

    .decor-1 {
      right: -60px;
      top: -60px;
      width: 260px;
      height: 260px
    }

    .decor-2 {
      left: -80px;
      bottom: -80px;
      width: 320px;
      height: 320px;
      background: rgba(255, 255, 255, .06)
    }

    .login-container {
      width: 100%;
      max-width: 520px;
      z-index: 1
    }

    .login-title {
      margin: 0 0 .25rem 0;
      font-weight: 800;
      font-size: 2.1rem;
      text-align: center
    }

    .login-subtitle {
      margin: 0 0 1.25rem 0;
      text-align: center;
      opacity: .95
    }

    .alert-status {
      margin-bottom: .75rem
    }

    .login-form {
      display: flex;
      flex-direction: column;
      gap: 16px
    }

    .label-row {
      display: flex;
      justify-content: space-between;
      align-items: center
    }

    .form-label {
      font-weight: 600;
      color: #eef1ff
    }

    /* input + placeholder */
    .form-input {
      width: 100%;
      margin-top: 6px;
      padding: 12px 44px 12px 14px;
      /* ruang kanan buat ikon */
      border-radius: 12px;
      font-size: 1rem;
      background: var(--input-bg) !important;
      border: 1px solid var(--input-bd);
      color: var(--input-text) !important;
      caret-color: var(--input-text);
    }

    .form-input::placeholder {
      color: var(--placeholder);
      opacity: 1
    }

    .form-input:focus {
      outline: none;
      background: #fff !important;
      border-color: var(--input-bd-focus);
      color: var(--input-text) !important;
    }

    .form-input:focus::placeholder {
      color: var(--placeholder-focus)
    }

    input.form-input:-webkit-autofill {
      -webkit-text-fill-color: var(--input-text) !important;
      transition: background-color 9999s ease-in-out 0s;
      box-shadow: 0 0 0 1000px #fff inset;
    }

    /* wrapper untuk field dengan ikon (password) */
    .input-wrap {
      position: relative
    }

    .toggle-pass {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: transparent;
      border: none;
      padding: 6px;
      border-radius: 8px;
      color: #5060b3;
      cursor: pointer;
    }

    .toggle-pass:hover {
      background: #eef2ff
    }

    .form-error {
      color: var(--danger);
      background: #fff;
      border-radius: 8px;
      padding: 6px 8px;
      margin-top: 6px
    }

    .remember-row {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #eef1ff;
      cursor: pointer
    }

    .remember-check {
      width: 16px;
      height: 16px
    }

    .btn-primary {
      background: var(--btn);
      color: #fff;
      font-weight: 700;
      padding: 12px;
      border: none;
      border-radius: 14px;
      cursor: pointer;
      width: 100%;
    }

    .btn-primary:hover {
      background: var(--btn-hover)
    }

    .link-small {
      color: #fff;
      text-decoration: underline;
      font-weight: 600
    }

    .signup-hint {
      color: #eef1ff;
      text-align: left
    }
  </style>
</head>

<body>

  <section class="login-hero">
    <span class="decor decor-1"></span>
    <span class="decor decor-2"></span>

    <div class="login-container">
      <header class="text-center">
        <h1 class="login-title">Login</h1>
        <p class="login-subtitle">Silakan login terlebih dahulu untuk masuk ke <b>ALPUKAT</b></p>
      </header>

      {{-- Status sesi (Breeze) --}}
      <x-auth-session-status class="alert-status" :status="session('status')" />

      <form method="POST" action="{{ route('login') }}" class="login-form" novalidate>
        @csrf

        {{-- Email --}}
        <div>
          <x-input-label for="email" :value="__('Email Koperasi')" class="form-label" />
          <x-text-input id="email" class="form-input"
            type="email" name="email" :value="old('email')" required autofocus
            autocomplete="username" placeholder=" Masukkan Alamat Email Koperasi" />
          <x-input-error :messages="$errors->get('email')" class="form-error" />
        </div>

        {{-- Password + toggle eye --}}
        <div>
          <div class="label-row">
            <x-input-label for="password" :value="__('Password')" class="form-label" />
            <!-- @if (Route::has('password.request'))
            <a class="link-small" href="{{ route('password.request') }}">Lupa password?</a>
            @endif -->
          </div>

          <div class="input-wrap">
            <x-text-input id="password" class="form-input"
              type="password" name="password" required autocomplete="current-password"
              placeholder="Masukkan Password" />
            <button type="button" class="toggle-pass" id="togglePass" aria-label="Tampilkan password">
              <i class="fa fa-eye" id="toggleIcon" aria-hidden="true"></i>
            </button>
          </div>

          <x-input-error :messages="$errors->get('password')" class="form-error" />
        </div>

        {{-- Remember --}}
        <label for="remember_me" class="remember-row">
          <input id="remember_me" type="checkbox" name="remember" class="remember-check">
          <span>Ingat saya</span>
        </label>

        {{-- Button --}}
        <button type="submit" class="btn-primary">Login</button>

        @if (Route::has('register'))
        <p class="signup-hint mt-3">
          Belum punya akun? <a href="{{ route('register') }}" class="link-small">Register</a>
        </p>
        @endif
      </form>
    </div>
  </section>

  <script>
    // Toggle show/hide password
    (function() {
      const input = document.getElementById('password');
      const btn = document.getElementById('togglePass');
      const icon = document.getElementById('toggleIcon');
      if (!input || !btn || !icon) return;

      btn.addEventListener('click', () => {
        const isHidden = input.getAttribute('type') === 'password';
        input.setAttribute('type', isHidden ? 'text' : 'password');
        icon.classList.toggle('fa-eye', !isHidden);
        icon.classList.toggle('fa-eye-slash', isHidden);
        btn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
        // kembalikan fokus ke input agar UX mulus
        input.focus({
          preventScroll: true
        });
      });
    })();
  </script>
</body>

</html>