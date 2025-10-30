<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Error del servidor</title>
  <style>
    html,body{height:100%;margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:#0b0b0c;color:#e5e7eb}
    .wrap{min-height:100%;display:flex;align-items:center;justify-content:center;padding:32px}
    .card{width:100%;max-width:560px;background:#111214;border:1px solid #26272b;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
    .body{padding:24px 24px 16px}
    .title{font-size:20px;line-height:1.2;font-weight:700;margin:0 0 8px}
    .muted{color:#a1a1aa;font-size:14px;margin:0}
    .footer{display:flex;gap:12px;align-items:center;justify-content:flex-end;padding:12px 16px 16px}
    .btn{appearance:none;border:1px solid #2f3035;background:#1b1c20;color:#e5e7eb;border-radius:10px;padding:10px 14px;font-weight:600;cursor:pointer}
    .btn:hover{background:#232429}
    .btn.primary{border-color:#4f46e5;background:#4f46e5}
    .btn.primary:hover{background:#4338ca}
  </style>
  <meta name="robots" content="noindex">
  <meta name="turbo-visit-control" content="reload">
  <meta name="livewire" content="off">
  <meta name="csrf-token" content="">
  <script>try{document.documentElement.classList.toggle('dark',true)}catch(e){}</script>
  <!-- No usamos funciones de traducción para evitar dependencias -->
  <!-- Página ligera para evitar fallos recursivos al renderizar errores -->
</head>
<body>
  <div class="wrap">
    <div class="card" role="alert" aria-live="assertive">
      <div class="body">
        <h1 class="title">Error del servidor (500)</h1>
        <p class="muted">Ocurrió un problema inesperado. Ya estamos trabajando para solucionarlo.</p>
      </div>
      <div class="footer">
        <button class="btn" onclick="location.reload()">Reintentar</button>
        <a class="btn primary" href="/">Ir al inicio</a>
      </div>
    </div>
  </div>
</body>
</html>

