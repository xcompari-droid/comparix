<!DOCTYPE html>
<html lang="ro">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Comparix.ro — Comparator modern de produse')</title>
        <meta name="description" content="@yield('meta_description', 'Compară specificații și oferte din România. Găsește rapid cele mai bune produse, prețuri și comparații actualizate.')">
        <meta property="og:type" content="website">
        <meta property="og:title" content="@yield('og_title','Comparix.ro')">
        <meta property="og:description" content="@yield('og_desc','Compară specificații și oferte din România.')">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ asset('img/og-default.jpg') }}">
        <meta name="twitter:card" content="summary_large_image">
        <link rel="icon" type="image/svg+xml" href="/comparix-x.svg">
        <link rel="canonical" href="{{ url()->current() }}">
        @vite('resources/js/app.js')
        <link rel="preload" href="/fonts/Inter-roman.var.woff2" as="font" type="font/woff2" crossorigin>
        <script type="application/ld+json">
        {
            "@context":"https://schema.org",
            "@type":"WebSite",
            "name":"Comparix",
            "url":"https://comparix.ro",
            "potentialAction":{
                "@type":"SearchAction",
                "target":"https://comparix.ro/cautare?q={search_term_string}",
                "query-input":"required name=search_term_string"
            }
        }
        </script>
        <meta name="theme-color" content="#06b6d4">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="HandheldFriendly" content="true">
        <meta name="MobileOptimized" content="320">
        <meta http-equiv="x-ua-compatible" content="IE=edge">
        <meta name="color-scheme" content="light dark">
        <meta name="msapplication-TileColor" content="#06b6d4">
        <meta name="msapplication-config" content="/browserconfig.xml">
        <link rel="manifest" href="/site.webmanifest">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#06b6d4">
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-KGR2R7N1EK"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', 'G-KGR2R7N1EK');
        </script>
</head>
<body class="bg-gray-50 antialiased">
    <!-- Header -->
    <header class="bg-white shadow-sm" role="banner">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" aria-label="Navigație principală">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold">
                        compari<span class="bg-gradient-to-r from-cyan-500 to-emerald-400 bg-clip-text text-transparent">X</span>
                    </a>
                </div>
                
                <div class="hidden md:flex space-x-8">
                    <a href="/" class="text-gray-700 hover:text-cyan-600 px-3 py-2 text-sm font-medium">Acasă</a>
                    <a href="/categorii" class="text-gray-700 hover:text-cyan-600 px-3 py-2 text-sm font-medium">Categorii</a>
                    <a href="/compare" class="text-gray-700 hover:text-emerald-600 px-3 py-2 text-sm font-medium flex items-center gap-1">
                        Compară <span id="cmp-header-count" class="ml-1 inline-block rounded bg-emerald-500 text-white px-2 py-0.5 text-xs font-bold">0</span>
                    </a>
                    <a href="/despre" class="text-gray-700 hover:text-cyan-600 px-3 py-2 text-sm font-medium">Despre</a>
                </div>

                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('favorites.index') }}" class="text-sm text-gray-700 hover:text-cyan-600 flex items-center gap-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            Favorite
                        </a>
                        <a href="/dashboard" class="text-sm text-gray-700 hover:text-cyan-600">Dashboard</a>
                    @else
                        <a href="/login" class="text-sm text-gray-500 hover:text-cyan-600">Login</a>
                    @endauth
                    <a href="/admin" class="text-sm text-gray-500 hover:text-cyan-600">Admin</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen" id="main-content" tabindex="-1" role="main">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">compariX.ro</h3>
                    <p class="text-gray-600 text-sm">Comparator modern de produse. Găsește cele mai bune oferte și compară specificațiile.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Link-uri</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/" class="text-gray-600 hover:text-cyan-600">Acasă</a></li>
                        <li><a href="/categorii" class="text-gray-600 hover:text-cyan-600">Categorii</a></li>
                        <li><a href="/compare" class="text-gray-600 hover:text-emerald-600">Compară produse</a></li>
                        <li><a href="/despre" class="text-gray-600 hover:text-cyan-600">Despre</a></li>
                        <li><a href="/cum-functioneaza" class="text-gray-
                        <li><a href="/confidentialitate" class="text-gray-600 hover:text-cyan-600">Confidențialitate</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Parteneri</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-600 hover:text-cyan-600">Afiliere</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-cyan-600">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-200 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} compariX.ro. Toate drepturile rezervate.<br>
                Product images from <a href="https://unsplash.com/?utm_source=comparix&utm_medium=referral" class="underline hover:text-cyan-600" rel="noopener" target="_blank">Unsplash</a>.<br>
                Folosim cookie-uri pentru funcționalitate și analytics. <a href="/confidentialitate" class="underline hover:text-cyan-600">Află mai multe</a>.
            </div>
        </div>
    </footer>

        <!-- Sticky compare bar mobil -->
        <div class="fixed bottom-0 inset-x-0 z-40 border-t bg-white/95 backdrop-blur px-3 py-2 flex items-center gap-2 md:hidden">
            <div class="text-sm"><span id="cmp-count" class="font-semibold">0</span>/4 în comparație</div>
            <a href="/compare" class="ml-auto rounded-lg bg-black text-white px-3 py-2 text-sm">Deschide comparația</a>
        </div>
        <script>
            // Header compare count
            document.addEventListener('DOMContentLoaded', () => {
                const ids = JSON.parse(localStorage.getItem('cmp.products')||'[]');
                if(document.getElementById('cmp-count')) document.getElementById('cmp-count').textContent = ids.length;
                if(document.getElementById('cmp-header-count')) document.getElementById('cmp-header-count').textContent = ids.length;
            });
        </script>
        <!-- Lazy-load imagini -->
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const obs = new IntersectionObserver((entries, io) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        const img = e.target; img.src = img.dataset.src || img.src; io.unobserve(img);
                    }
                });
            }, { rootMargin: '200px' });
            document.querySelectorAll('img.lazy').forEach(img => obs.observe(img));
        });
        </script>

    <!-- Cookie Consent Banner -->
    <div id="cookie-consent" class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-lg p-4 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-700" style="display:none;">
        <div>
            Folosim cookie-uri pentru funcționalitate și analytics. Continuând navigarea, accepți <a href="/confidentialitate" class="underline hover:text-cyan-600">politica de confidențialitate</a>.
        </div>
        <button id="cookie-accept" class="mt-2 md:mt-0 px-4 py-2 bg-emerald-500 text-white rounded-lg font-semibold hover:bg-emerald-600 transition">Accept</button>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!localStorage.getItem('cookieConsent')) {
            document.getElementById('cookie-consent').style.display = 'flex';
        }
        document.getElementById('cookie-accept').addEventListener('click', function() {
            localStorage.setItem('cookieConsent', '1');
            document.getElementById('cookie-consent').style.display = 'none';
        });
    });
    </script>
</body>
</html>
