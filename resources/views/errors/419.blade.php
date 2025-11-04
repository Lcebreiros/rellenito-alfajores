<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sesión expirada</title>
  <style>
    html,body{height:100%;margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:#0b0b0c;color:#e5e7eb}
    .wrap{min-height:100%;display:flex;align-items:center;justify-content:center;padding:32px}
    .card{width:100%;max-width:560px;background:#111214;border:1px solid #26272b;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
    .body{padding:24px 24px 16px}
    .icon-container{display:flex;justify-content:center;margin-bottom:16px}
    .icon{width:64px;height:64px;border-radius:50%;background:#fbbf24;display:flex;align-items:center;justify-content:center}
    .icon svg{width:36px;height:36px;color:#78350f}
    .title{font-size:20px;line-height:1.2;font-weight:700;margin:0 0 8px;text-align:center}
    .muted{color:#a1a1aa;font-size:14px;margin:0;text-align:center;line-height:1.5}
    .footer{display:flex;gap:12px;align-items:center;justify-content:center;padding:12px 16px 16px}
    .btn{appearance:none;border:1px solid #2f3035;background:#1b1c20;color:#e5e7eb;border-radius:10px;padding:10px 18px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block}
    .btn:hover{background:#232429}
    .btn.primary{border-color:#4f46e5;background:#4f46e5}
    .btn.primary:hover{background:#4338ca}
  </style>
  <meta name="robots" content="noindex">
  <meta name="turbo-visit-control" content="reload">
  <meta name="livewire" content="off">
  <meta name="csrf-token" content="">
  <script>try{document.documentElement.classList.toggle('dark',true)}catch(e){}</script>
</head>
<body>
  <div class="wrap">
    <div class="card" role="alert" aria-live="assertive">
      <div class="body">
        <div class="icon-container">
          <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
          </div>
        </div>
        <h1 class="title">Su sesión ha expirado</h1>
        <p class="muted">Por seguridad, su sesión ha caducado debido a inactividad. Por favor, inicie sesión nuevamente para continuar.</p>
      </div>
      <div class="footer">
        <a class="btn primary" href="{{ route('login') }}">Iniciar sesión</a>
      </div>
    </div>
  </div>
</body>
</html>
