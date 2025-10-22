<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comparix</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="antialiased" style="font-family: system-ui, -apple-system, Segoe UI, Roboto;">
    <div style="max-width: 960px; margin: 40px auto;">
        <h1 style="margin-bottom: 12px;">Comparix</h1>
        <p>Site-ul este online ✅ — pagina Laravel de demo a fost înlocuită.</p>
    </div>
</body>
</html>
