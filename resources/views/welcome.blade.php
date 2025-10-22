{{-- resources/views/welcome.blade.php --}}
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Aplicatie') }}</title>
    <style>
        html,body{height:100%} body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#0a0a0a;color:#ededec}
        .wrap{min-height:100%;display:flex;align-items:center;justify-content:center;padding:32px}
        .card{max-width:720px;width:100%;background:#161615;border:1px solid #2a2a27;border-radius:14px;padding:28px}
        h1{margin:0 0 8px;font-size:28px}
        p{margin:8px 0 0;color:#a1a09a}
        .ok{display:inline-block;margin-top:14px;padding:8px 12px;border-radius:10px;background:#0f1f0f;border:1px solid #244b24;color:#b7ffb7;font-weight:600}
        .links{margin-top:18px;display:flex;gap:12px;flex-wrap:wrap}
        .links a{padding:8px 12px;border-radius:10px;border:1px solid #2a2a27;color:#ededec;text-decoration:none}
        .links a:hover{background:#1b1b18}
        .muted{margin-top:16px;font-size:12px;color:#8d8c86}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>{{ config('app.name', 'Site') }}</h1>
        <div class="ok">Site-ul este online ✅</div>
        <p>Bun venit! Aceasta este pagina temporară de start. Dacă vezi mesajul, deploy-ul funcționează.</p>

        <div class="links">
            <a href="{{ url('/') }}">Acasă</a>
            <a href="{{ url('/health.txt') }}">Healthcheck</a>
            <a href="https://laravel.com/docs" target="_blank" rel="noopener">Documentație Laravel</a>
        </div>

        <p class="muted">
            Poți edita acest fișier la <code>resources/views/welcome.blade.php</code> și/sau muta routing-ul către o altă pagină.
        </p>
    </div>
</div>
</body>
</html>
