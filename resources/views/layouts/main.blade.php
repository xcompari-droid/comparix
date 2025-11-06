<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'compariX.ro - Comparator modern de produse')</title>
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-50 antialiased">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold">
                        compari<span class="bg-gradient-to-r from-cyan-500 to-emerald-400 bg-clip-text text-transparent">X</span>
                    </a>
                </div>
                
                <div class="hidden md:flex space-x-8">
                    <a href="/" class="text-gray-700 hover:text-cyan-600 px-3 py-2 text-sm font-medium">Acasă</a>
                    <a href="/categorii" class="text-gray-700 hover:text-cyan-600 px-3 py-2 text-sm font-medium">Categorii</a>
                    <a href="/despre" class="text-gray-700 hover:text-cyan-600 px-3 py-2 text-sm font-medium">Despre</a>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="/admin" class="text-sm text-gray-500 hover:text-cyan-600">Admin</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">compariX.ro</h3>
                    <p class="text-gray-600 text-sm">Comparator modern de produse. Găsește cele mai bune oferte și compară specificațiile.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Link-uri</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/" class="text-gray-600 hover:text-cyan-600">Acasă</a></li>
                        <li><a href="/categorii" class="text-gray-600 hover:text-cyan-600">Categorii</a></li>
                        <li><a href="/despre" class="text-gray-600 hover:text-cyan-600">Despre</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-600 hover:text-cyan-600">Termeni și condiții</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-cyan-600">Confidențialitate</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-200 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} compariX.ro. Toate drepturile rezervate.
            </div>
        </div>
    </footer>
</body>
</html>
