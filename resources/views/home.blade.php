<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>compariX.ro — Comparator modern</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial,Helvetica,sans-serif;background:#fff;color:#111}
    main{max-width:960px;margin:48px auto;padding:0 16px}
    h1{font-size:clamp(28px,4vw,40px);margin-bottom:8px}
    .accent{background:linear-gradient(90deg,#00a3ff,#00e08a);-webkit-background-clip:text;background-clip:text;color:transparent}
    p.lead{color:#666;margin-bottom:24px}
    .links{display:flex;gap:12px;flex-wrap:wrap;margin:24px 0}
    footer{margin-top:40px;color:#888;font-size:14px}
  </style>
</head>
<body class="antialiased">
  <main>
    <h1>compari<span class="accent">X</span>.ro</h1>
    <p class="lead">Comparator modern — MVP online ✅</p>

    <div class="links">
      <a href="https://laravel.com/docs" target="_blank" rel="noopener">Documentație Laravel</a>
      <a href="/health">Healthcheck</a>
    </div>

    <section>
      <h2>Ce urmează</h2>
      <ol>
        <li>Conectăm pagina de categorie „Telefoane”.</li>
        <li>Adăugăm feed-urile 2Performant + Open Icecat.</li>
        <li>Construim pagina de comparație (grafic, tabel, câștigător).</li>
      </ol>
    </section>

    <footer>compariX.ro — versiune MVP</footer>
  </main>
</body>
</html>
