@extends('layouts.main')

@section('title', 'compariX.ro - Comparator modern de produse')

@section('content')
<div class="bg-gradient-to-b from-cyan-50 via-white to-emerald-50 min-h-[80vh] flex items-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6">
            compari<span class="bg-gradient-to-r from-cyan-500 to-emerald-400 bg-clip-text text-transparent">X</span>.ro
        </h1>
        <p class="text-xl md:text-2xl text-gray-600 mb-8 max-w-2xl mx-auto">
            Comparator modern de produse. Găsește cele mai bune oferte și compară specificațiile.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
            <a href="/categorii" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-cyan-500 to-emerald-400 text-white font-semibold rounded-lg hover:from-cyan-600 hover:to-emerald-500 transition-all shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Explorează Produsele
            </a>
            <a href="/admin" class="inline-flex items-center px-8 py-4 bg-white text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all shadow-md hover:shadow-lg border-2 border-gray-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Panou Admin
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto mt-16">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                    <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mb-2">Compară Specificații</h3>
                <p class="text-gray-600 text-sm">Vezi diferențele între produse side-by-side</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mb-2">Cele Mai Bune Prețuri</h3>
                <p class="text-gray-600 text-sm">Găsește automat cele mai avantajoase oferte</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mb-2">Rapid și Simplu</h3>
                <p class="text-gray-600 text-sm">Interface modernă și intuitivă</p>
            </div>
        </div>

        <div class="mt-16 pt-16 border-t border-gray-200 max-w-3xl mx-auto">
            <h2 class="text-2xl font-bold mb-6">Următorii pași</h2>
            <div class="bg-white rounded-lg shadow-md p-6 text-left">
                <ol class="space-y-3">
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-cyan-100 text-cyan-600 rounded-full flex items-center justify-center text-sm font-semibold mr-3">1</span>
                        <span class="text-gray-700">Adaugă categorii și produse din panoul de admin</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-cyan-100 text-cyan-600 rounded-full flex items-center justify-center text-sm font-semibold mr-3">2</span>
                        <span class="text-gray-700">Conectează feed-urile 2Performant pentru oferte automate</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-cyan-100 text-cyan-600 rounded-full flex items-center justify-center text-sm font-semibold mr-3">3</span>
                        <span class="text-gray-700">Testează funcționalitatea de comparare produse</span>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
