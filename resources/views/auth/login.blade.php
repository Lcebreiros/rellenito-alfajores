<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ __('auth.login_title') }} · Helipso</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet" />

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    /* ============================================================
       TOKENS
       ============================================================ */
    :root {
      --space:        #0B0820;
      --space-2:      #15102E;
      --violet:       #8A5CF5;
      --violet-deep:  #5B2FCC;
      --violet-soft:  #B79CFA;
      --violet-pale:  #E5DBFD;
      --fuchsia:      #D670F0;

      --bg:           #FAFAF7;
      --ink:          #1A1330;
      --ink-2:        #4A4360;
      --slate:        #8B8499;
      --slate-2:      #B5B0C2;
      --line:         #E8E5EE;
      --line-2:       #D8D4E0;
      --error:        #C2272D;

      --ease:         cubic-bezier(0.32, 0.72, 0, 1);
    }

    *, *::before, *::after { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; height: 100%; }
    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: var(--bg);
      color: var(--ink);
      -webkit-font-smoothing: antialiased;
      text-rendering: geometricPrecision;
      overflow: hidden;
    }
    [x-cloak] { display: none !important; }

    /* ============================================================
       SHELL
       ============================================================ */
    .shell {
      height: 100vh;
      display: grid;
      grid-template-columns: 1fr 1fr;
      padding: 0;
      gap: 0;
      background: var(--bg);
    }

    /* ============================================================
       COSMOS PANE (left) — full height, sin bordes redondeados
       ============================================================ */
    .cosmos {
      position: relative;
      overflow: hidden;
      border-radius: 0;
      background:
        radial-gradient(ellipse 90% 60% at 30% 35%, rgba(138,92,245,0.22), transparent 60%),
        radial-gradient(ellipse 60% 50% at 80% 85%, rgba(214,112,240,0.14), transparent 55%),
        radial-gradient(ellipse 100% 100% at 50% 50%, #1a1240 0%, #0B0820 70%, #050313 100%);
      color: #fff;
      isolation: isolate;
    }

    .stars { position: absolute; inset: 0; pointer-events: none; }
    .stars span {
      position: absolute;
      border-radius: 50%;
      background: #fff;
      opacity: 0;
      animation: twinkle var(--dur, 8s) ease-in-out infinite;
      animation-delay: var(--delay, 0s);
    }
    @keyframes twinkle {
      0%, 100% { opacity: 0.05; transform: scale(0.8); }
      50%       { opacity: var(--peak, 0.5); transform: scale(1); }
    }

    .nebula {
      position: absolute;
      left: 50%; top: 50%;
      width: 70%; aspect-ratio: 1/1;
      transform: translate(-50%, -50%);
      background: radial-gradient(circle at center,
        rgba(183,156,250,0.16) 0%,
        rgba(138,92,245,0.08) 30%,
        transparent 65%);
      filter: blur(14px);
      pointer-events: none;
    }

    .cosmos-content {
      position: relative;
      z-index: 2;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 36px 44px;
    }

    .cosmos-top {
      display: flex;
      align-items: center;
      gap: 8px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 11px;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: rgba(255,255,255,0.45);
    }
    .cosmos-top .dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: #50e3a4;
      box-shadow: 0 0 10px rgba(80,227,164,0.6);
    }

    /* Center: animated logo — centrado verticalmente entre header y footer */
    .center {
      display: flex;
      flex-direction: column;
      gap: 22px;
      max-width: 480px;
      align-self: center;
    }

    .glyph {
      --fs: clamp(72px, 9vw, 128px);
      width:  calc(var(--fs) * 1.05 * 52 / 80);
      height: calc(var(--fs) * 1.05);
      overflow: visible;
      display: block;
    }

    .glyph .node, .glyph .node-halo {
      --np: 0;
      transform-box: fill-box;
      transform-origin: center;
    }
    .glyph .node {
      transform: scale(calc(var(--np) * 1));
      opacity: calc(var(--np));
    }
    .glyph .node-halo {
      transform: scale(calc(0.4 + var(--np) * 1.6));
      opacity: calc(var(--np) * (1 - var(--np)) * 1.6);
      filter: blur(2px);
    }
    .glyph .seg {
      --lp: 0;
      stroke-dasharray: var(--len, 100);
      stroke-dashoffset: calc(var(--len, 100) * (1 - var(--lp)));
      fill: none;
      stroke: var(--violet-soft);
      stroke-width: 1.4;
      stroke-linecap: round;
      opacity: calc(0.55 + var(--lp) * 0.35);
    }

    /* Lockup row: glyph + wordmark */
    .lockup {
      display: flex;
      align-items: flex-end;
      gap: calc(var(--fs, 96px) * 0.04);
    }
    /* elipso: estático, sin animación */
    .lockup .word {
      font-family: 'Space Grotesk', sans-serif;
      font-weight: 350;
      font-size: var(--fs);
      letter-spacing: -0.04em;
      line-height: 1;
      color: #fff;
    }

    .glyph  { --fs: clamp(72px, 9vw, 128px); }
    .lockup { --fs: clamp(72px, 9vw, 128px); }

    /* tagline: estático */
    .tagline {
      font-size: 14px;
      line-height: 1.55;
      color: rgba(255,255,255,0.58);
      margin: 0;
      max-width: 420px;
    }

    .cosmos-foot {
      padding-top: 0;
      display: flex;
      justify-content: space-between;
      font-family: 'JetBrains Mono', monospace;
      font-size: 10px;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      color: rgba(255,255,255,0.32);
    }

    /* ============================================================
       FORM PANE (right) — minimal
       ============================================================ */
    .form-pane {
      position: relative;
      display: grid;
      grid-template-rows: auto 1fr auto;
      padding: 24px 16px 16px;
    }

    .pane-top {
      display: flex;
      justify-content: flex-end;
      padding: 8px 16px;
    }
    .pane-top .top-link {
      font-size: 13px;
      color: var(--slate);
    }
    .pane-top a {
      color: var(--ink);
      text-decoration: none;
      font-weight: 500;
      border-bottom: 1px solid var(--line-2);
      padding-bottom: 1px;
      margin-left: 4px;
      transition: border-color 0.2s var(--ease);
    }
    .pane-top a:hover { border-bottom-color: var(--ink); }

    .pane-mid {
      display: grid;
      place-items: center;
    }

    .form-card {
      width: 100%;
      max-width: 380px;
    }

    .form-header { margin-bottom: 30px; }
    .form-header h1 {
      font-family: 'Space Grotesk', sans-serif;
      font-weight: 400;
      font-size: 30px;
      letter-spacing: -0.025em;
      color: var(--ink);
      margin: 0 0 6px;
      line-height: 1.2;
    }
    .form-header p {
      font-size: 14px;
      color: var(--slate);
      margin: 0;
      line-height: 1.5;
    }

    /* Alerts */
    .alert {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 10px 0 10px 14px;
      border-left: 2px solid var(--error);
      margin-bottom: 22px;
      font-size: 13px;
      line-height: 1.5;
      color: var(--ink-2);
    }
    .alert strong { color: var(--ink); font-weight: 600; }
    .alert-suspended { border-left-color: #C28A27; }
    .alert-throttle  { border-left-color: #C26927; }

    /* Form */
    .login-form { display: flex; flex-direction: column; gap: 22px; }

    .field {
      position: relative;
      border-bottom: 1px solid var(--line-2);
      padding-top: 18px;
      transition: border-color 0.2s var(--ease);
    }
    .field:focus-within { border-bottom-color: var(--ink); }
    .field.has-error { border-bottom-color: var(--error); }

    .field label {
      position: absolute;
      left: 0;
      top: 22px;
      font-size: 14px;
      color: var(--slate);
      pointer-events: none;
      transition: all 0.2s var(--ease);
      transform-origin: left center;
    }
    .field input:focus ~ label,
    .field input:not(:placeholder-shown) ~ label {
      top: 0;
      font-size: 11px;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: var(--slate);
      font-weight: 500;
    }
    .field:focus-within label { color: var(--ink); }
    .field.has-error label,
    .field.has-error:focus-within label { color: var(--error); }

    .field input {
      width: 100%;
      border: 0;
      outline: 0;
      background: transparent;
      padding: 0 60px 10px 0;
      font: inherit;
      font-size: 15px;
      color: var(--ink);
    }
    .field input:-webkit-autofill {
      -webkit-text-fill-color: var(--ink);
      box-shadow: 0 0 0 1000px var(--bg) inset;
    }

    .field-action {
      position: absolute;
      right: 0;
      top: 18px;
      display: flex;
      gap: 14px;
      align-items: center;
    }
    .field-action a {
      font-size: 12px;
      color: var(--slate);
      text-decoration: none;
      transition: color 0.2s var(--ease);
    }
    .field-action a:hover { color: var(--ink); }
    .toggle-pw {
      border: 0;
      background: transparent;
      color: var(--slate);
      cursor: pointer;
      padding: 4px;
      display: grid; place-items: center;
      transition: color 0.2s var(--ease);
    }
    .toggle-pw:hover { color: var(--ink); }

    /* Remember */
    .remember {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      cursor: pointer;
      user-select: none;
      font-size: 13px;
      color: var(--ink-2);
    }
    .remember input { position: absolute; opacity: 0; pointer-events: none; }
    .remember .box {
      width: 16px; height: 16px;
      border: 1.5px solid var(--line-2);
      border-radius: 4px;
      display: grid; place-items: center;
      transition: all 0.18s var(--ease);
    }
    .remember .box svg { opacity: 0; transition: opacity 0.15s var(--ease); }
    .remember input:checked ~ .box {
      background: var(--ink);
      border-color: var(--ink);
    }
    .remember input:checked ~ .box svg { opacity: 1; }

    /* Submit */
    .btn-submit {
      margin-top: 8px;
      width: 100%;
      border: 0;
      padding: 14px 20px;
      border-radius: 999px;
      font: inherit;
      font-size: 14px;
      font-weight: 500;
      color: #fff;
      background: var(--ink);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: background 0.2s var(--ease), transform 0.08s var(--ease), box-shadow 0.2s var(--ease);
    }
    .btn-submit:hover { background: var(--violet-deep); box-shadow: 0 6px 20px rgba(91,47,204,0.25); }
    .btn-submit:active { transform: scale(0.99); }
    .btn-submit svg { transition: transform 0.2s var(--ease); }
    .btn-submit:hover svg { transform: translateX(2px); }

    .pane-foot {
      display: flex;
      justify-content: space-between;
      padding: 8px 16px;
      font-size: 11px;
      color: var(--slate-2);
      font-family: 'JetBrains Mono', monospace;
      letter-spacing: 0.12em;
      text-transform: uppercase;
    }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 960px) {
      body { overflow-y: auto; }
      .shell {
        grid-template-columns: 1fr;
        height: auto;
        min-height: 100vh;
      }
      .cosmos { min-height: 320px; }
      .cosmos-content { padding: 28px; }
      .glyph, .lockup { --fs: clamp(56px, 12vw, 96px); }
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
      .glyph .node, .glyph .node-halo { transform: none !important; opacity: 1 !important; }
      .glyph .seg { stroke-dashoffset: 0 !important; opacity: 0.9 !important; }
      .stars span { animation: none !important; opacity: 0.3 !important; }
    }
  </style>

  @livewireStyles
</head>
<body>
<div class="shell">

  <!-- ============================================================
       LEFT · COSMOS PASTILLA
       ============================================================ -->
  <aside class="cosmos">
    <div class="stars" id="stars" aria-hidden="true"></div>
    <div class="nebula" aria-hidden="true"></div>

    <div class="cosmos-content">
      <div class="cosmos-top">
        <span class="dot"></span>
        <span>v2.0</span>
      </div>

      <div class="center">
        <!-- LOCKUP: h animada + elipso estático -->
        <div class="lockup">
          <svg class="glyph" viewBox="26 16 52 80" aria-hidden="true">
            <!-- halos -->
            <circle class="node-halo" data-node="0" cx="32" cy="22" r="5"   fill="#D670F0" />
            <circle class="node-halo" data-node="1" cx="38" cy="54" r="5.5" fill="#B79CFA" />
            <circle class="node-halo" data-node="2" cx="34" cy="90" r="3.5" fill="#B79CFA" />
            <circle class="node-halo" data-node="3" cx="50" cy="46" r="5"   fill="#B79CFA" />
            <circle class="node-halo" data-node="4" cx="66" cy="56" r="3.5" fill="#B79CFA" />
            <circle class="node-halo" data-node="5" cx="72" cy="64" r="5.5" fill="#B79CFA" />
            <circle class="node-halo" data-node="6" cx="68" cy="90" r="3.5" fill="#B79CFA" />
            <!-- lines -->
            <line class="seg" data-line="0" data-len="32.6" x1="32" y1="22" x2="38" y2="54" />
            <line class="seg" data-line="1" data-len="36.4" x1="38" y1="54" x2="34" y2="90" />
            <line class="seg" data-line="2" data-len="14.4" x1="38" y1="54" x2="50" y2="46" />
            <line class="seg" data-line="3" data-len="18.9" x1="50" y1="46" x2="66" y2="56" />
            <line class="seg" data-line="4" data-len="10.0" x1="66" y1="56" x2="72" y2="64" />
            <line class="seg" data-line="5" data-len="26.3" x1="72" y1="64" x2="68" y2="90" />
            <!-- nodes -->
            <circle class="node" data-node="0" cx="32" cy="22" r="2.8" fill="#D670F0" />
            <circle class="node" data-node="1" cx="38" cy="54" r="4"   fill="#fff" />
            <circle class="node" data-node="2" cx="34" cy="90" r="2.6" fill="#fff" />
            <circle class="node" data-node="3" cx="50" cy="46" r="3.2" fill="#fff" />
            <circle class="node" data-node="4" cx="66" cy="56" r="2.6" fill="#fff" />
            <circle class="node" data-node="5" cx="72" cy="64" r="4"   fill="#fff" />
            <circle class="node" data-node="6" cx="68" cy="90" r="2.6" fill="#fff" />
          </svg>
          <span class="word">elipso</span>
        </div>

        <p class="tagline">Potenciá tu negocio.<br>simplificá tu vida.</p>
      </div>

      <div class="cosmos-foot">
        <span></span>
        <span></span>
      </div>
    </div>
  </aside>

  <!-- ============================================================
       RIGHT · FORM PANE
       ============================================================ -->
  <main class="form-pane">
    <div class="pane-top">
      <div class="top-link">
        ¿Sin cuenta?
        @if (Route::has('plans'))
          <a href="{{ route('plans') }}">Ver planes</a>
        @else
          <a href="#">Ver planes</a>
        @endif
      </div>
    </div>

    <div class="pane-mid">
      <div class="form-card">
        <div class="form-header">
          <h1>Iniciar sesión</h1>
          <p>Bienvenido de vuelta. Ingresá tus credenciales para continuar.</p>
        </div>

        @php
          $emailError   = $errors->has('email') ? $errors->first('email') : null;
          $isSuspended  = $emailError && str_contains($emailError, 'Cuenta suspendida');
          $isThrottle   = $emailError && (str_contains($emailError, 'Too many') || str_contains($emailError, 'throttle') || str_contains($emailError, 'seconds'));
          $isCredFailed = $emailError && !$isSuspended && !$isThrottle;
        @endphp

        @if($isSuspended)
          <div class="alert alert-suspended">
            <span><strong>Cuenta suspendida.</strong> Contactá al administrador para reactivar el acceso.</span>
          </div>
        @endif

        @if($isThrottle)
          <div class="alert alert-throttle">
            <span><strong>Demasiados intentos.</strong> Esperá unos segundos antes de volver a intentarlo.</span>
          </div>
        @endif

        @if($isCredFailed)
          <div class="alert">
            <span><strong>Credenciales incorrectas.</strong> Verificá email y contraseña.</span>
          </div>
        @endif

        @if(session('error'))
          <div class="alert alert-suspended">
            <span>{{ session('error') }}</span>
          </div>
        @endif

        @if($errors->any() && !$emailError)
          <div class="alert">
            <span>
              @foreach($errors->all() as $e)
                {{ $e }}<br>
              @endforeach
            </span>
          </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="login-form" autocomplete="on">
          @csrf

          <div class="field {{ $isCredFailed ? 'has-error' : '' }}">
            <input id="email" name="email" type="email" required autofocus
                   autocomplete="email" placeholder=" "
                   value="{{ old('email') }}" />
            <label for="email">Email</label>
          </div>

          <div class="field {{ $isCredFailed ? 'has-error' : '' }}" x-data="{ show: false }">
            <input id="password" name="password" :type="show ? 'text' : 'password'" required
                   autocomplete="current-password" placeholder=" " />
            <label for="password">Contraseña</label>
            <div class="field-action">
              @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Olvidé</a>
              @endif
              <button type="button" class="toggle-pw" @click="show = !show"
                      :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                <svg x-show="!show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  <circle cx="12" cy="12" r="3" />
                </svg>
                <svg x-show="show" x-cloak width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.477 10.477A3 3 0 0012 15c1.657 0 3-1.343 3-3a3 3 0 00-3-3c-.525 0-1.02.135-1.45.373M9.88 9.88L6.343 6.343M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m3.32-2.91A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.186 2.592"/>
                </svg>
              </button>
            </div>
          </div>

          <label class="remember">
            <input type="checkbox" name="remember" />
            <span class="box">
              <svg width="10" height="10" viewBox="0 0 20 20" fill="none">
                <path d="M4 10l4 4 8-8" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            <span>Recordarme en este equipo</span>
          </label>

          <button type="submit" class="btn-submit">
            <span>Ingresar</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 5l7 7-7 7"/>
            </svg>
          </button>
        </form>
      </div>
    </div>

    <div class="pane-foot">
      <span>© {{ date('Y') }} helipso</span>
      <span>ar-1</span>
    </div>
  </main>

</div>

<script>
  // ================================================================
  // Stars
  // ================================================================
  (() => {
    const stars = document.getElementById('stars');
    let seed = 13;
    const rnd = () => { seed = (seed * 9301 + 49297) % 233280; return seed / 233280; };
    const N = 60;
    const frag = document.createDocumentFragment();
    for (let i = 0; i < N; i++) {
      const s = document.createElement('span');
      const size = 1 + rnd() * 2.2;
      s.style.left   = (rnd() * 100).toFixed(2) + '%';
      s.style.top    = (rnd() * 100).toFixed(2) + '%';
      s.style.width  = size.toFixed(2) + 'px';
      s.style.height = size.toFixed(2) + 'px';
      s.style.setProperty('--peak',  (0.18 + rnd() * 0.55).toFixed(2));
      const dur = 5 + rnd() * 9;
      s.style.setProperty('--dur',   dur.toFixed(1) + 's');
      s.style.setProperty('--delay', (-rnd() * dur).toFixed(1) + 's');
      frag.appendChild(s);
    }
    stars.appendChild(frag);
  })();

  // ================================================================
  // h-constellation entrance — solo la h se anima, elipso es estático
  // ================================================================
  (() => {
    const ease     = (t) => 1 - Math.pow(1 - t, 4);  // easeOutQuart
    const easeStar = (t) => 1 - Math.pow(1 - t, 3);  // easeOutCubic
    const clamp01  = (v) => Math.max(0, Math.min(1, v));
    const remap    = (v, a, b) => clamp01((v - a) / (b - a));

    const DURATION_MS = 3200;

    const STAR_ORDER = [0, 2, 6, 1, 3, 4, 5];
    const STAR_PHASE = { start: 0.04, end: 0.55, perDur: 0.22 };
    const LINE_PHASE = { start: 0.32, end: 0.72, perDur: 0.16 };

    const nodes = [...document.querySelectorAll('.glyph .node[data-node]')];
    const halos = [...document.querySelectorAll('.glyph .node-halo[data-node]')];
    const lines = [...document.querySelectorAll('.glyph .seg[data-line]')];

    lines.forEach(l => l.style.setProperty('--len', parseFloat(l.dataset.len)));

    function update(p) {
      STAR_ORDER.forEach((idx, i) => {
        const t0 = STAR_PHASE.start + i * ((STAR_PHASE.end - STAR_PHASE.start - STAR_PHASE.perDur) / (STAR_ORDER.length - 1));
        const t1 = t0 + STAR_PHASE.perDur;
        const np = easeStar(remap(p, t0, t1));
        const node = nodes.find(n => +n.dataset.node === idx);
        const halo = halos.find(n => +n.dataset.node === idx);
        if (node) node.style.setProperty('--np', np.toFixed(3));
        if (halo) halo.style.setProperty('--np', np.toFixed(3));
      });

      lines.forEach((line, i) => {
        const t0 = LINE_PHASE.start + i * ((LINE_PHASE.end - LINE_PHASE.start - LINE_PHASE.perDur) / (lines.length - 1));
        const t1 = t0 + LINE_PHASE.perDur;
        const lp = ease(remap(p, t0, t1));
        line.style.setProperty('--lp', lp.toFixed(3));
      });
    }

    update(0);

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      update(1);
      return;
    }

    let startTime = null;
    function tick(now) {
      if (startTime === null) startTime = now;
      const p = clamp01((now - startTime) / DURATION_MS);
      update(p);
      if (p < 1) requestAnimationFrame(tick);
    }
    setTimeout(() => requestAnimationFrame(tick), 250);
  })();
</script>

@livewireScripts
</body>
</html>
