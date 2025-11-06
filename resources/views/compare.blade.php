@extends('layouts.main')

@section('title', 'Comparare produse - compariX.ro')

@section('content')
<div class="bg-gradient-to-b from-cyan-50 to-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Comparare produse</h1>
        <p class="text-lg text-gray-600">Compară specificațiile și găsește cea mai bună ofertă</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if(count($products) < 2)
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Selectează cel puțin 2 produse</h3>
            <p class="mt-1 text-sm text-gray-500">Trebuie să selectezi cel puțin 2 produse pentru a le compara</p>
            <div class="mt-6">
                <a href="/categorii" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-cyan-600 hover:bg-cyan-700">
                    Înapoi la categorii
                </a>
            </div>
        </div>
    @else
        <!-- Winner Banner -->
        @if(isset($winner))
            <div class="bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-lg p-6 mb-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">🏆 Câștigător: {{ $winner->name }}</h2>
                        <p class="text-emerald-50">Cea mai bună ofertă: {{ number_format($winner->best_price, 2) }} RON</p>
                    </div>
                    <a href="/oferta/{{ $winner->best_offer_id }}" 
                       target="_blank"
                       class="px-6 py-3 bg-white text-cyan-600 rounded-lg hover:bg-emerald-50 transition-colors font-bold">
                        Vezi oferta
                    </a>
                </div>
            </div>
        @endif

        <!-- Price Comparison Chart -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold mb-4">Comparație prețuri</h3>
            <div class="space-y-4">
                @foreach($products as $product)
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">{{ $product->name }}</span>
                            <span class="text-lg font-bold text-cyan-600">
                                @if($product->offers->isNotEmpty())
                                    {{ number_format($product->offers->min('price'), 2) }} RON
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            @php
                                $maxPrice = $products->flatMap->offers->max('price');
                                $minPrice = $product->offers->min('price') ?? 0;
                                $percentage = $maxPrice > 0 ? ($minPrice / $maxPrice) * 100 : 0;
                            @endphp
                            <div class="bg-gradient-to-r from-cyan-500 to-emerald-400 h-3 rounded-full" 
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Specifications Comparison Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50">
                                Specificație
                            </th>
                            @foreach($products as $product)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="font-semibold text-gray-900 text-sm">{{ $product->name }}</div>
                                    @if($product->brand)
                                        <div class="text-gray-500 font-normal">{{ $product->brand }}</div>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Basic Info -->
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 bg-gray-50">
                                Preț minim
                            </td>
                            @foreach($products as $product)
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($product->offers->isNotEmpty())
                                        <span class="text-lg font-bold text-cyan-600">
                                            {{ number_format($product->offers->min('price'), 2) }} RON
                                        </span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>

                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 bg-white">
                                Oferte disponibile
                            </td>
                            @foreach($products as $product)
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800">
                                        {{ $product->offers->count() }} oferte
                                    </span>
                                </td>
                            @endforeach
                        </tr>

                        <!-- Specifications from SpecValues -->
                        @php
                            $allSpecKeys = collect();
                            foreach($products as $product) {
                                $allSpecKeys = $allSpecKeys->merge($product->specValues->pluck('specKey'));
                            }
                            $allSpecKeys = $allSpecKeys->unique('id');
                        @endphp

                        @foreach($allSpecKeys as $specKey)
                            <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 sticky left-0 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                    {{ $specKey->name }}
                                </td>
                                @foreach($products as $product)
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        @php
                                            $specValue = $product->specValues->firstWhere('spec_key_id', $specKey->id);
                                        @endphp
                                        {{ $specValue->value ?? '-' }}
                                        @if($specValue && $specValue->unit)
                                            <span class="text-gray-500">{{ $specValue->unit }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Offers Section -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-{{ min(count($products), 3) }} gap-6">
            @foreach($products as $product)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Oferte pentru {{ $product->name }}</h3>
                    @if($product->offers->isEmpty())
                        <p class="text-sm text-gray-500">Nu există oferte disponibile</p>
                    @else
                        <div class="space-y-3">
                            @foreach($product->offers->sortBy('price') as $offer)
                                <a href="/oferta/{{ $offer->id }}" 
                                   target="_blank"
                                   class="block p-4 bg-gradient-to-r from-cyan-50 to-emerald-50 rounded-lg hover:from-cyan-100 hover:to-emerald-100 transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-medium text-gray-900">{{ $offer->store_name }}</span>
                                        <span class="text-lg font-bold text-cyan-600">{{ number_format($offer->price, 2) }} RON</span>
                                    </div>
                                    @if($offer->stock_status)
                                        <span class="text-xs text-green-600">✓ În stoc</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
