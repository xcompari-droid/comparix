@extends('layouts.main')
@section('title', 'Politica de confidențialitate — Comparix.ro')
@section('meta_description', 'Află cum protejăm datele tale personale pe Comparix.ro și ce drepturi ai conform GDPR.')
@section('content')
<div class="max-w-3xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold mb-6">Politica de confidențialitate</h1>
    <div class="prose max-w-none">
        <p>Ultima actualizare: {{ date('d.m.Y') }}</p>
        <h2>1. Ce date colectăm</h2>
        <p>Colectăm date strict necesare pentru funcționarea site-ului și pentru analytics (Google Analytics, cookies funcționale).</p>
        <h2>2. Cum folosim datele</h2>
        <p>Datele sunt folosite doar pentru îmbunătățirea experienței și nu sunt vândute terților.</p>
        <h2>3. Drepturile tale</h2>
        <p>Ai dreptul să soliciți acces, rectificare sau ștergere a datelor personale. Scrie-ne la <a href="mailto:contact@comparix.ro">contact@comparix.ro</a>.</p>
        <h2>4. Cookie-uri</h2>
        <p>Folosim cookie-uri pentru funcționalitate și analytics. Poți seta browserul să le blocheze, dar anumite funcții nu vor merge.</p>
        <h2>5. Securitate</h2>
        <p>Luăm măsuri tehnice pentru a proteja datele tale. Totuși, nicio metodă nu este 100% sigură.</p>
    </div>
</div>
@endsection
