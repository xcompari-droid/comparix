<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Comparix</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="antialiased" style="font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif;">
  <div style="max-width: 920px; margin: 60px auto; padding: 0 16px;">
    <h1 style="font-size: 44px; margin: 0 0 12px;">Comparix</h1>
    <p style="font-size: 18px; color:#555; margin:0 0 24px;">
      Site-ul tău Laravel este online. Aici va fi homepage-ul aplicației.
    </p>

    <div style="display:flex; gap:16px; flex-wrap:wrap;">
      <a href="/" style="padding:12px 16px; border:1px solid #ddd; border-radius:10px; text-decoration:none;">Acasă</a>
      <a href="/login" style="padding:12px 16px; border:1px solid #ddd; border-radius:10px; text-decoration:none;">Autentificare</a>
      <a href="/register" style="padding:12px 16px; border:1px solid #ddd; border-radius:10px; text-decoration:none;">Înregistrare</a>
    </div>

    <hr style="margin:32px 0; border:0; border-top:1px solid #eee;">
    <p style="color:#777;">Poți modifica acest fișier: <code>resources/views/welcome.blade.php</code></p>
  </div>
</body>
</html>
