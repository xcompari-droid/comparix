@extends('layouts.main')

@section('title', 'Categorii produse - compariX.ro')

@section('breadcrumbs')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Acasă",
      "item": "{{ url('/') }}"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Categorii"
    }
  ]
}
</script>
@endsection

@section('content')
<div class="bg-gradient-to-b from-cyan-50 to-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Categorii de produse</h1>
        <p class="text-lg text-gray-600">Descoperă și compară produse din categoriile tale preferate</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if($categories->isEmpty())
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nu există categorii</h3>
            <p class="mt-1 text-sm text-gray-500">Adaugă categorii din panoul de admin</p>
            <div class="mt-6">
                <a href="/admin" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-cyan-600 hover:bg-cyan-700">
                    Mergi la Admin
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($categories as $category)
                <a href="/categorii/{{ $category->slug }}" class="group">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-200 h-full">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900 group-hover:text-cyan-600 transition-colors">
                                {{ $category->name }}
                            </h3>
                            <svg class="h-6 w-6 text-gray-400 group-hover:text-cyan-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        @if($category->description)
                            <p class="text-gray-600 text-sm">{{ Str::limit($category->description, 100) }}</p>
                        @endif
                        <div class="mt-4 text-sm text-gray-500">
                            {{ $category->products_count ?? 0 }} produse
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
