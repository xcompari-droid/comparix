@extends('layouts.main')

@section('title', 'Comparare orașe - compariX.ro')

@section('content')
<div class="bg-gradient-to-b from-blue-50 to-white py-4 md:py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-center gap-3 md:gap-8 mb-4 md:mb-6">
            @if(count($products) >= 2)
                @foreach($products->take(2) as $index => $product)
                    <div class="text-center">
                        <div class="relative inline-block">
                            <img src="{{ $product->image_url }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-20 h-20 md:w-28 md:h-28 rounded-full object-cover border-2 md:border-3 border-white shadow-lg">
                        </div>
                        <h2 class="text-lg md:text-2xl font-bold mt-2 md:mt-4" style="color: {{ $cityColors[$product->name] ?? '#6B7280' }}">
                            {{ $product->name }}
                        </h2>
                        <p class="text-xs md:text-sm text-gray-600 mt-1 md:mt-2 px-2">{{ $product->short_desc }}</p>
                    </div>
                    @if($index === 0)
                        <div class="text-2xl md:text-4xl font-black text-gray-300">VS</div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 md:py-8">
    @if(count($products) < 2)
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Selectează cel puțin 2 orașe</h3>
            <p class="mt-1 text-sm text-gray-500">Trebuie să selectezi cel puțin 2 orașe pentru a le compara</p>
            <div class="mt-6">
                <a href="/categorii/orase" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Înapoi la orașe
                </a>
            </div>
        </div>
    @else
        @php
            // Prepare data for charts
            $cityNames = $products->pluck('name')->toArray();
            
            // City color mapping
            $cityColors = [
                'București' => '#3B82F6',
                'Cluj-Napoca' => '#EA580C',
                'Timișoara' => '#F43F5E',
                'Iași' => '#A855F7',
                'Constanța' => '#0EA5E9',
                'Craiova' => '#22C55E',
                'Brașov' => '#78716C',
                'Galați' => '#60A5FA',
                'Ploiești' => '#F59E0B',
                'Brăila' => '#14B8A6'
            ];
            
            $populations = [];
            $areas = [];
            $densities = [];
            $gdpPerCapita = [];
            $unemployment = [];
            $lifeExpectancy = [];
            $universities = [];
            $museums = [];
            $greenSpaces = [];
            $airQuality = [];
            $crimeIndex = [];
            $healthcareIndex = [];
            $trafficIndex = [];
            $allSpecs = [];
            
            foreach ($products as $product) {
                $specs = $product->specValues->keyBy(function($item) {
                    return $item->specKey->slug;
                });

                $allSpecs[] = $specs;
                $populations[] = isset($specs['population']) && $specs['population'] ? ($specs['population']->value_number ?? 0) : 0;
                $areas[] = isset($specs['surface_km2']) && $specs['surface_km2'] ? ($specs['surface_km2']->value_number ?? 0) : 0;
                $densities[] = isset($specs['population_density']) && $specs['population_density'] ? ($specs['population_density']->value_number ?? 0) : 0;
                $gdpPerCapita[] = isset($specs['gdp_per_capita_usd']) && $specs['gdp_per_capita_usd'] ? ($specs['gdp_per_capita_usd']->value_number ?? 0) : 0;
                $unemployment[] = isset($specs['unemployment_rate']) && $specs['unemployment_rate'] ? ($specs['unemployment_rate']->value_number ?? 0) : 0;
                $lifeExpectancy[] = isset($specs['life_expectancy']) && $specs['life_expectancy'] ? ($specs['life_expectancy']->value_number ?? 0) : 0;
                $universities[] = isset($specs['universities_count']) && $specs['universities_count'] ? ($specs['universities_count']->value_number ?? 0) : 0;
                $museums[] = isset($specs['museums_count']) && $specs['museums_count'] ? ($specs['museums_count']->value_number ?? 0) : 0;
                $greenSpaces[] = isset($specs['green_spaces_km2']) && $specs['green_spaces_km2'] ? ($specs['green_spaces_km2']->value_number ?? 0) : 0;
                $airQuality[] = isset($specs['air_quality_index']) && $specs['air_quality_index'] ? ($specs['air_quality_index']->value_number ?? 0) : 0;
                $crimeIndex[] = isset($specs['crime_index']) && $specs['crime_index'] ? ($specs['crime_index']->value_number ?? 0) : 0;
                $healthcareIndex[] = isset($specs['healthcare_index']) && $specs['healthcare_index'] ? ($specs['healthcare_index']->value_number ?? 0) : 0;
                $trafficIndex[] = isset($specs['traffic_index']) && $specs['traffic_index'] ? ($specs['traffic_index']->value_number ?? 0) : 0;
            }
            
            // Calculate winners for each metric (only for 2 cities)
            $comparisons = [];
            if (count($products) == 2) {
                $city1 = $products[0];
                $city2 = $products[1];
                $specs1 = $allSpecs[0];
                $specs2 = $allSpecs[1];
                
                // Population
                if ($populations[0] > $populations[1]) {
                    $diff = $populations[0] - $populations[1];
                    $comparisons[] = [
                        'winner' => 0,
                        'text' => number_format($diff, 0, ',', '.') . ' mai mulți locuitori',
                        'detail' => number_format($populations[0], 0, ',', '.') . ' vs ' . number_format($populations[1], 0, ',', '.')
                    ];
                } elseif ($populations[1] > $populations[0]) {
                    $diff = $populations[1] - $populations[0];
                    $comparisons[] = [
                        'winner' => 1,
                        'text' => number_format($diff, 0, ',', '.') . ' mai mulți locuitori',
                        'detail' => number_format($populations[1], 0, ',', '.') . ' vs ' . number_format($populations[0], 0, ',', '.')
                    ];
                }
                
                // GDP
                if ($gdpPerCapita[0] > $gdpPerCapita[1]) {
                    $diff = $gdpPerCapita[0] - $gdpPerCapita[1];
                    $comparisons[] = [
                        'winner' => 0,
                        'text' => '$' . number_format($diff, 0, ',', '.') . ' PIB per capita mai mare',
                        'detail' => '$' . number_format($gdpPerCapita[0], 0, ',', '.') . ' vs $' . number_format($gdpPerCapita[1], 0, ',', '.')
                    ];
                } elseif ($gdpPerCapita[1] > $gdpPerCapita[0]) {
                    $diff = $gdpPerCapita[1] - $gdpPerCapita[0];
                    $comparisons[] = [
                        'winner' => 1,
                        'text' => '$' . number_format($diff, 0, ',', '.') . ' PIB per capita mai mare',
                        'detail' => '$' . number_format($gdpPerCapita[1], 0, ',', '.') . ' vs $' . number_format($gdpPerCapita[0], 0, ',', '.')
                    ];
                }
                
                // Universities
                if ($universities[0] > $universities[1]) {
                    $diff = $universities[0] - $universities[1];
                    $comparisons[] = [
                        'winner' => 0,
                        'text' => $diff . ' mai multe universități',
                        'detail' => $universities[0] . ' vs ' . $universities[1]
                    ];
                } elseif ($universities[1] > $universities[0]) {
                    $diff = $universities[1] - $universities[0];
                    $comparisons[] = [
                        'winner' => 1,
                        'text' => $diff . ' mai multe universități',
                        'detail' => $universities[1] . ' vs ' . $universities[0]
                    ];
                }
                
                // Air Quality (lower is better)
                if ($airQuality[0] < $airQuality[1]) {
                    $diff = $airQuality[1] - $airQuality[0];
                    $comparisons[] = [
                        'winner' => 0,
                        'text' => number_format($diff, 0, ',', '.') . ' puncte aer mai curat',
                        'detail' => number_format($airQuality[0], 0, ',', '.') . ' AQI vs ' . number_format($airQuality[1], 0, ',', '.') . ' AQI'
                    ];
                } elseif ($airQuality[1] < $airQuality[0]) {
                    $diff = $airQuality[0] - $airQuality[1];
                    $comparisons[] = [
                        'winner' => 1,
                        'text' => number_format($diff, 0, ',', '.') . ' puncte aer mai curat',
                        'detail' => number_format($airQuality[1], 0, ',', '.') . ' AQI vs ' . number_format($airQuality[0], 0, ',', '.') . ' AQI'
                    ];
                }
                
                // Crime Index (lower is better)
                if ($crimeIndex[0] < $crimeIndex[1]) {
                    $diff = $crimeIndex[1] - $crimeIndex[0];
                    $comparisons[] = [
                        'winner' => 0,
                        'text' => number_format($diff, 0, ',', '.') . ' puncte mai sigur',
                        'detail' => 'Index ' . number_format($crimeIndex[0], 0, ',', '.') . ' vs ' . number_format($crimeIndex[1], 0, ',', '.')
                    ];
                } elseif ($crimeIndex[1] < $crimeIndex[0]) {
                    $diff = $crimeIndex[0] - $crimeIndex[1];
                    $comparisons[] = [
                        'winner' => 1,
                        'text' => number_format($diff, 0, ',', '.') . ' puncte mai sigur',
                        'detail' => 'Index ' . number_format($crimeIndex[1], 0, ',', '.') . ' vs ' . number_format($crimeIndex[0], 0, ',', '.')
                    ];
                }
                
                // Museums
                if ($museums[0] > $museums[1]) {
                    $diff = $museums[0] - $museums[1];
                    $comparisons[] = [
                        'winner' => 0,
                        'text' => $diff . ' mai multe muzee',
                        'detail' => $museums[0] . ' vs ' . $museums[1]
                    ];
                } elseif ($museums[1] > $museums[0]) {
                    $diff = $museums[1] - $museums[0];
                    $comparisons[] = [
                        'winner' => 1,
                        'text' => $diff . ' mai multe muzee',
                        'detail' => $museums[1] . ' vs ' . $museums[0]
                    ];
                }
                
                // Green Spaces
                if ($greenSpaces[0] > $greenSpaces[1]) {
                    $diff = $greenSpaces[0] - $greenSpaces[1];
                    $comparisons[] = [
                        'winner' => 0,
                        'text' => number_format($diff, 0, ',', '.') . ' km² mai multe spații verzi',
                        'detail' => number_format($greenSpaces[0], 0, ',', '.') . ' km² vs ' . number_format($greenSpaces[1], 0, ',', '.') . ' km²'
                    ];
                } elseif ($greenSpaces[1] > $greenSpaces[0]) {
                    $diff = $greenSpaces[1] - $greenSpaces[0];
                    $comparisons[] = [
                        'winner' => 1,
                        'text' => number_format($diff, 0, ',', '.') . ' km² mai multe spații verzi',
                        'detail' => number_format($greenSpaces[1], 0, ',', '.') . ' km² vs ' . number_format($greenSpaces[0], 0, ',', '.') . ' km²'
                    ];
                }
            }
        @endphp

        @if(count($products) == 2 && !empty($comparisons))
            <!-- Direct Comparison Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 mb-6 md:mb-8">
                <!-- City 1 advantages -->
                <div class="bg-white rounded-xl shadow-sm p-3 md:p-4">
                    <h3 class="text-sm md:text-base font-bold mb-3 md:mb-4" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                        Avantajele {{ $products[0]->name }}
                    </h3>
                    <div class="space-y-2">
                        @foreach($comparisons as $comp)
                            @if($comp['winner'] == 0)
                                <div class="flex items-start gap-2 p-2 bg-emerald-50 rounded-lg border border-emerald-100">
                                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900 text-xs">{{ $comp['text'] }}</p>
                                        <p class="text-[10px] text-gray-600 mt-0.5">{{ $comp['detail'] }}</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- City 2 advantages -->
                <div class="bg-white rounded-xl shadow-sm p-3 md:p-4">
                    <h3 class="text-sm md:text-base font-bold mb-3 md:mb-4" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                        Avantajele {{ $products[1]->name }}
                    </h3>
                    <div class="space-y-2">
                        @foreach($comparisons as $comp)
                            @if($comp['winner'] == 1)
                                <div class="flex items-start gap-2 p-2 bg-emerald-50 rounded-lg border border-emerald-100">
                                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900 text-xs">{{ $comp['text'] }}</p>
                                        <p class="text-[10px] text-gray-600 mt-0.5">{{ $comp['detail'] }}</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Demographics Section -->
        <div class="bg-white rounded-xl shadow-sm p-3 md:p-5 mb-4 md:mb-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 mb-3 md:mb-4 flex items-center pb-2 border-b">
                <span class="text-lg md:text-xl mr-2">👥</span>
                Demografie
            </h2>
            
            @if(count($products) == 2)
                <!-- Side by Side Comparison -->
                <div class="space-y-3 md:space-y-4">
                    <!-- Population -->
                    <div class="border-b pb-3 md:pb-4">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Populație</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    {{ number_format($populations[0], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">locuitori</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    {{ number_format($populations[1], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">locuitori</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalPop = $populations[0] + $populations[1];
                                    $percent1 = ($populations[0] / $totalPop) * 100;
                                @endphp
                                <div class="absolute h-full rounded-full transition-all" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full transition-all" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Population Density -->
                    <div class="border-b pb-4 md:pb-6">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Densitate populație</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    {{ number_format($densities[0], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">loc/km²</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    {{ number_format($densities[1], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">loc/km²</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalDensity = $densities[0] + $densities[1];
                                    $percent1 = ($densities[0] / $totalDensity) * 100;
                                @endphp
                                <div class="absolute h-full rounded-full" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Area -->
                    <div class="border-b pb-3 md:pb-4">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Suprafață</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    {{ number_format($areas[0], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">km²</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    {{ number_format($areas[1], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">km²</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalArea = $areas[0] + $areas[1];
                                    $percent1 = ($areas[0] / $totalArea) * 100;
                                @endphp
                                <div class="absolute h-full rounded-full" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Life Expectancy -->
                    <div class="pb-6">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Speranță de viață</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    {{ number_format($lifeExpectancy[0], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">ani</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    {{ number_format($lifeExpectancy[1], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">ani</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalLife = $lifeExpectancy[0] + $lifeExpectancy[1];
                                    $percent1 = ($lifeExpectancy[0] / $totalLife) * 100;
                                @endphp
                                <div class="absolute h-full rounded-full" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Multi-city charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8 mb-6 md:mb-8">
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Populație</h3>
                        <canvas id="populationChart"></canvas>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Densitate populație (loc/km²)</h3>
                        <canvas id="densityChart"></canvas>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8">
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Speranță de viață (ani)</h3>
                        <canvas id="lifeExpectancyChart"></canvas>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Suprafață (km²)</h3>
                        <canvas id="areaChart"></canvas>
                    </div>
                </div>
            @endif
        </div>

        <!-- Economy Section -->
        <div class="bg-white rounded-xl shadow-sm p-3 md:p-5 mb-4 md:mb-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 mb-3 md:mb-4 flex items-center pb-2 border-b">
                <span class="text-lg md:text-xl mr-2">💰</span>
                Economie
            </h2>
            
            @if(count($products) == 2)
                <div class="space-y-3 md:space-y-4">
                    <!-- GDP per Capita -->
                    <div class="border-b pb-3 md:pb-4">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">PIB per capita</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    ${{ number_format($gdpPerCapita[0], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">USD</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    ${{ number_format($gdpPerCapita[1], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">USD</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalGdp = $gdpPerCapita[0] + $gdpPerCapita[1];
                                    $percent1 = ($gdpPerCapita[0] / $totalGdp) * 100;
                                @endphp
                                <div class="absolute h-full rounded-full" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Unemployment Rate -->
                    <div class="pb-6">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Rata șomajului</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    {{ number_format($unemployment[0], 0, ',', '.') }}%
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">{{ $unemployment[0] < 4 ? '✓ Scăzut' : 'Ridicat' }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    {{ number_format($unemployment[1], 0, ',', '.') }}%
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">{{ $unemployment[1] < 4 ? '✓ Scăzut' : 'Ridicat' }}</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalUnemp = $unemployment[0] + $unemployment[1];
                                    $percent1 = ($unemployment[0] / $totalUnemp) * 100;
                                @endphp
                                <div class="absolute h-full rounded-full" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8">
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">PIB per capita (USD)</h3>
                        <canvas id="gdpChart"></canvas>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Rata șomajului (%)</h3>
                        <canvas id="unemploymentChart"></canvas>
                    </div>
                </div>
            @endif
        </div>

        <!-- Infrastructure Section -->
        <div class="bg-white rounded-xl shadow-sm p-3 md:p-5 mb-4 md:mb-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 mb-3 md:mb-4 flex items-center pb-2 border-b">
                <span class="text-lg md:text-xl mr-2">🏛️</span>
                Infrastructură & Cultură
            </h2>
            
            @if(count($products) == 2)
                <div class="space-y-3 md:space-y-4">
                    <!-- Universities -->
                    <div class="border-b pb-3 md:pb-4">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Universități</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    {{ number_format($universities[0], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">universități</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    {{ number_format($universities[1], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">universități</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalUni = $universities[0] + $universities[1];
                                    $percent1 = $totalUni > 0 ? ($universities[0] / $totalUni) * 100 : 50;
                                @endphp
                                <div class="absolute h-full rounded-full" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Museums -->
                    <div class="border-b pb-3 md:pb-4">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Muzee</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    {{ number_format($museums[0], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">muzee</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    {{ number_format($museums[1], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">muzee</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalMus = $museums[0] + $museums[1];
                                    $percent1 = $totalMus > 0 ? ($museums[0] / $totalMus) * 100 : 50;
                                @endphp
                                <div class="absolute h-full rounded-full" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Green Spaces -->
                    <div class="pb-6">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Spații verzi</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    {{ number_format($greenSpaces[0], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">km²</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    {{ number_format($greenSpaces[1], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">km²</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalGreen = $greenSpaces[0] + $greenSpaces[1];
                                    $percent1 = $totalGreen > 0 ? ($greenSpaces[0] / $totalGreen) * 100 : 50;
                                @endphp
                                <div class="absolute h-full rounded-full" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Universități</h3>
                        <canvas id="universitiesChart"></canvas>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Muzee</h3>
                        <canvas id="museumsChart"></canvas>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Spații verzi (km²)</h3>
                        <canvas id="greenSpacesChart"></canvas>
                    </div>
                </div>
            @endif
        </div>

        <!-- Quality of Life Section -->
        <div class="bg-white rounded-xl shadow-sm p-3 md:p-5 mb-4 md:mb-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 mb-3 md:mb-4 flex items-center pb-2 border-b">
                <span class="text-lg md:text-xl mr-2">🌟</span>
                Calitatea Vieții
            </h2>
            
            @if(count($products) == 2)
                <div class="space-y-3 md:space-y-4">
                    <!-- Air Quality -->
                    <div class="border-b pb-3 md:pb-4">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Calitate aer (mai mic = mai bine)</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    {{ number_format($airQuality[0], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">{{ $airQuality[0] < 50 ? '✓ Bun' : ($airQuality[0] < 100 ? 'Moderat' : 'Rău') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    {{ number_format($airQuality[1], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">{{ $airQuality[1] < 50 ? '✓ Bun' : ($airQuality[1] < 100 ? 'Moderat' : 'Rău') }}</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalAir = $airQuality[0] + $airQuality[1];
                                    $percent1 = $totalAir > 0 ? ($airQuality[0] / $totalAir) * 100 : 50;
                                @endphp
                                <div class="absolute h-full rounded-full" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Crime Index -->
                    <div class="border-b pb-3 md:pb-4">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Index criminalitate (mai mic = mai sigur)</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    {{ number_format($crimeIndex[0], 1) }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">{{ $crimeIndex[0] < 30 ? '✓ Foarte sigur' : ($crimeIndex[0] < 50 ? 'Sigur' : 'Atenție') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    {{ number_format($crimeIndex[1], 1) }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">{{ $crimeIndex[1] < 30 ? '✓ Foarte sigur' : ($crimeIndex[1] < 50 ? 'Sigur' : 'Atenție') }}</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalCrime = $crimeIndex[0] + $crimeIndex[1];
                                    $percent1 = $totalCrime > 0 ? ($crimeIndex[0] / $totalCrime) * 100 : 50;
                                @endphp
                                <div class="absolute h-full rounded-full" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Healthcare Index -->
                    <div class="border-b pb-3 md:pb-4">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Index sănătate (mai mare = mai bine)</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    {{ number_format($healthcareIndex[0], 1) }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">{{ $healthcareIndex[0] > 60 ? '✓ Excelent' : ($healthcareIndex[0] > 50 ? 'Bun' : 'Moderat') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    {{ number_format($healthcareIndex[1], 1) }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">{{ $healthcareIndex[1] > 60 ? '✓ Excelent' : ($healthcareIndex[1] > 50 ? 'Bun' : 'Moderat') }}</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalHealth = $healthcareIndex[0] + $healthcareIndex[1];
                                    $percent1 = $totalHealth > 0 ? ($healthcareIndex[0] / $totalHealth) * 100 : 50;
                                @endphp
                                <div class="absolute h-full rounded-full" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Traffic Index -->
                    <div class="pb-6">
                        <h3 class="text-[10px] md:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Index trafic (mai mic = mai bine)</h3>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}">
                                    {{ number_format($trafficIndex[0], 1) }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">{{ $trafficIndex[0] < 80 ? '✓ Fluid' : ($trafficIndex[0] < 120 ? 'Moderat' : 'Aglomerat') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg md:text-2xl font-bold mb-0.5" style="color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}">
                                    {{ number_format($trafficIndex[1], 1) }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500">{{ $trafficIndex[1] < 80 ? '✓ Fluid' : ($trafficIndex[1] < 120 ? 'Moderat' : 'Aglomerat') }}</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden">
                                @php
                                    $totalTraffic = $trafficIndex[0] + $trafficIndex[1];
                                    $percent1 = $totalTraffic > 0 ? ($trafficIndex[0] / $totalTraffic) * 100 : 50;
                                @endphp
                                <div class="absolute h-full rounded-full" 
                                     style="width: {{ $percent1 }}%; background-color: {{ $cityColors[$products[0]->name] ?? '#3B82F6' }}"></div>
                                <div class="absolute h-full rounded-full" 
                                     style="left: {{ $percent1 }}%; width: {{ 100 - $percent1 }}%; background-color: {{ $cityColors[$products[1]->name] ?? '#EA580C' }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8 mb-6 md:mb-8">
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Calitate aer (index - mai mic = mai bine)</h3>
                        <canvas id="airQualityChart"></canvas>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Index criminalitate (mai mic = mai sigur)</h3>
                        <canvas id="crimeChart"></canvas>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8">
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Index sănătate (mai mare = mai bine)</h3>
                        <canvas id="healthcareChart"></canvas>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Index trafic (mai mic = mai bine)</h3>
                        <canvas id="trafficChart"></canvas>
                    </div>
                </div>
            @endif
        </div>

        @if(count($products) > 2)
        <!-- Overall Comparison Radar Chart (only for 3+ cities) -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <span class="text-3xl mr-3">👥</span>
                Date Demografice
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8 mb-6 md:mb-8">
                <!-- Population Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Populație</h3>
                    <canvas id="populationChart"></canvas>
                </div>
                
                <!-- Population Density Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Densitate populație (loc/km²)</h3>
                    <canvas id="densityChart"></canvas>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8">
                <!-- Life Expectancy Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Speranță de viață (ani)</h3>
                    <canvas id="lifeExpectancyChart"></canvas>
                </div>
                
                <!-- Area Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Suprafață (km²)</h3>
                    <canvas id="areaChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Economy Section -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <span class="text-3xl mr-3">💰</span>
                Date Economice
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8">
                <!-- GDP per Capita Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">PIB per capita (USD)</h3>
                    <canvas id="gdpChart"></canvas>
                </div>
                
                <!-- Unemployment Rate Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Rata șomajului (%)</h3>
                    <canvas id="unemploymentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Infrastructure & Culture Section -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <span class="text-3xl mr-3">🏛️</span>
                Infrastructură & Cultură
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Universities Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Universități</h3>
                    <canvas id="universitiesChart"></canvas>
                </div>
                
                <!-- Museums Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Muzee</h3>
                    <canvas id="museumsChart"></canvas>
                </div>
                
                <!-- Green Spaces Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Spații verzi (km²)</h3>
                    <canvas id="greenSpacesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Quality of Life Section -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <span class="text-3xl mr-3">🌟</span>
                Calitatea Vieții
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8 mb-6 md:mb-8">
                <!-- Air Quality Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Calitate aer (index - mai mic = mai bine)</h3>
                    <canvas id="airQualityChart"></canvas>
                </div>
                
                <!-- Crime Index Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Index criminalitate (mai mic = mai sigur)</h3>
                    <canvas id="crimeChart"></canvas>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8">
                <!-- Healthcare Index Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Index sănătate (mai mare = mai bine)</h3>
                    <canvas id="healthcareChart"></canvas>
                </div>
                
                <!-- Traffic Index Chart -->
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Index trafic (mai mic = mai bine)</h3>
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Overall Comparison Radar Chart -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <span class="text-3xl mr-3">📊</span>
                Comparație Generală (normalizat 0-100)
            </h2>
            <div class="max-w-3xl mx-auto">
                <canvas id="radarChart"></canvas>
            </div>
            <p class="text-sm text-gray-500 text-center mt-2">
                * Valorile sunt normalizate pentru comparație vizuală. Mai mare = mai bine pentru toate indicatorii.
            </p>
        </div>

        @endif
    @endif
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@if(count($products) > 2)
<script>
const cityNames = @json($cityNames ?? []);
const populations = @json($populations ?? []);
const areas = @json($areas ?? []);
const densities = @json($densities ?? []);
const gdpPerCapita = @json($gdpPerCapita ?? []);
const unemployment = @json($unemployment ?? []);
const lifeExpectancy = @json($lifeExpectancy ?? []);
const universities = @json($universities ?? []);
const museums = @json($museums ?? []);
const greenSpaces = @json($greenSpaces ?? []);
const airQuality = @json($airQuality ?? []);
const crimeIndex = @json($crimeIndex ?? []);
const healthcareIndex = @json($healthcareIndex ?? []);
const trafficIndex = @json($trafficIndex ?? []);

// City-specific colors matching SVG mockups
const cityColorMap = {
    'București': { bg: 'rgba(59, 130, 246, 0.8)', border: 'rgba(30, 58, 138, 1)' },      // Blue
    'Cluj-Napoca': { bg: 'rgba(234, 88, 12, 0.8)', border: 'rgba(124, 45, 18, 1)' },     // Orange
    'Timișoara': { bg: 'rgba(244, 63, 94, 0.8)', border: 'rgba(190, 18, 60, 1)' },       // Red/Rose
    'Iași': { bg: 'rgba(168, 85, 247, 0.8)', border: 'rgba(88, 28, 135, 1)' },           // Purple
    'Constanța': { bg: 'rgba(14, 165, 233, 0.8)', border: 'rgba(12, 74, 110, 1)' },      // Sky Blue
    'Craiova': { bg: 'rgba(34, 197, 94, 0.8)', border: 'rgba(21, 83, 45, 1)' },          // Green
    'Brașov': { bg: 'rgba(120, 113, 108, 0.8)', border: 'rgba(68, 64, 60, 1)' },         // Gray/Stone
    'Galați': { bg: 'rgba(96, 165, 250, 0.8)', border: 'rgba(30, 64, 175, 1)' },         // Blue
    'Ploiești': { bg: 'rgba(245, 158, 11, 0.8)', border: 'rgba(113, 63, 18, 1)' },       // Amber/Orange
    'Brăila': { bg: 'rgba(20, 184, 166, 0.8)', border: 'rgba(15, 118, 110, 1)' }         // Teal
};

// Get colors for each city in order
const colors = cityNames.map(name => cityColorMap[name]?.bg || 'rgba(107, 114, 128, 0.8)');
const borderColors = cityNames.map(name => cityColorMap[name]?.border || 'rgba(55, 65, 81, 1)');

// Population Chart
new Chart(document.getElementById('populationChart'), {
    type: 'bar',
    data: {
        labels: cityNames,
        datasets: [{
            label: 'Populație',
            data: populations,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + ' loc';
                    }
                }
            }
        }
    }
});

// Density Chart
new Chart(document.getElementById('densityChart'), {
    type: 'bar',
    data: {
        labels: cityNames,
        datasets: [{
            label: 'Densitate',
            data: densities,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + ' loc/km²';
                    }
                }
            }
        }
    }
});

// Life Expectancy Chart
new Chart(document.getElementById('lifeExpectancyChart'), {
    type: 'line',
    data: {
        labels: cityNames,
        datasets: [{
            label: 'Speranță de viață',
            data: lifeExpectancy,
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: false,
                min: 70,
                max: 80,
                ticks: {
                    callback: function(value) {
                        return value + ' ani';
                    }
                }
            }
        }
    }
});

// Area Chart
new Chart(document.getElementById('areaChart'), {
    type: 'doughnut',
    data: {
        labels: cityNames,
        datasets: [{
            data: areas,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    generateLabels: function(chart) {
                        const data = chart.data;
                        return data.labels.map((label, i) => ({
                            text: label,
                            fillStyle: data.datasets[0].backgroundColor[i],
                            strokeStyle: data.datasets[0].borderColor[i],
                            lineWidth: 2,
                            hidden: false,
                            index: i
                        }));
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.parsed + ' km²';
                    }
                }
            }
        }
    }
});

// GDP Chart
new Chart(document.getElementById('gdpChart'), {
    type: 'bar',
    data: {
        labels: cityNames,
        datasets: [{
            label: 'PIB per capita',
            data: gdpPerCapita,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Unemployment Chart
new Chart(document.getElementById('unemploymentChart'), {
    type: 'bar',
    data: {
        labels: cityNames,
        datasets: [{
            label: 'Rata șomajului',
            data: unemployment,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        }
    }
});

// Universities Chart
new Chart(document.getElementById('universitiesChart'), {
    type: 'polarArea',
    data: {
        labels: cityNames,
        datasets: [{
            data: universities,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    generateLabels: function(chart) {
                        const data = chart.data;
                        return data.labels.map((label, i) => ({
                            text: label,
                            fillStyle: data.datasets[0].backgroundColor[i],
                            strokeStyle: data.datasets[0].borderColor[i],
                            lineWidth: 2,
                            hidden: false,
                            index: i
                        }));
                    }
                }
            }
        }
    }
});

// Museums Chart
new Chart(document.getElementById('museumsChart'), {
    type: 'polarArea',
    data: {
        labels: cityNames,
        datasets: [{
            data: museums,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    generateLabels: function(chart) {
                        const data = chart.data;
                        return data.labels.map((label, i) => ({
                            text: label,
                            fillStyle: data.datasets[0].backgroundColor[i],
                            strokeStyle: data.datasets[0].borderColor[i],
                            lineWidth: 2,
                            hidden: false,
                            index: i
                        }));
                    }
                }
            }
        }
    }
});

// Green Spaces Chart
new Chart(document.getElementById('greenSpacesChart'), {
    type: 'bar',
    data: {
        labels: cityNames,
        datasets: [{
            label: 'Spații verzi',
            data: greenSpaces,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + ' km²';
                    }
                }
            }
        }
    }
});

// Air Quality Chart
new Chart(document.getElementById('airQualityChart'), {
    type: 'bar',
    data: {
        labels: cityNames,
        datasets: [{
            label: 'Calitate aer',
            data: airQuality,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + ' AQI';
                    }
                }
            }
        }
    }
});

// Crime Index Chart
new Chart(document.getElementById('crimeChart'), {
    type: 'bar',
    data: {
        labels: cityNames,
        datasets: [{
            label: 'Index criminalitate',
            data: crimeIndex,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        }
    }
});

// Healthcare Index Chart
new Chart(document.getElementById('healthcareChart'), {
    type: 'bar',
    data: {
        labels: cityNames,
        datasets: [{
            label: 'Index sănătate',
            data: healthcareIndex,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        }
    }
});

// Traffic Index Chart
new Chart(document.getElementById('trafficChart'), {
    type: 'bar',
    data: {
        labels: cityNames,
        datasets: [{
            label: 'Index trafic',
            data: trafficIndex,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        }
    }
});

// Radar Chart - Overall Comparison
// Normalize values to 0-100 scale
function normalize(value, min, max) {
    return ((value - min) / (max - min)) * 100;
}

const radarDatasets = cityNames.map((name, index) => ({
    label: name,
    data: [
        normalize(populations[index], Math.min(...populations), Math.max(...populations)),
        normalize(gdpPerCapita[index], Math.min(...gdpPerCapita), Math.max(...gdpPerCapita)),
        100 - normalize(unemployment[index], Math.min(...unemployment), Math.max(...unemployment)), // Invert
        normalize(lifeExpectancy[index], Math.min(...lifeExpectancy), Math.max(...lifeExpectancy)),
        normalize(universities[index], Math.min(...universities), Math.max(...universities)),
        100 - normalize(airQuality[index], Math.min(...airQuality), Math.max(...airQuality)), // Invert
        100 - normalize(crimeIndex[index], Math.min(...crimeIndex), Math.max(...crimeIndex)), // Invert
        normalize(healthcareIndex[index], Math.min(...healthcareIndex), Math.max(...healthcareIndex)),
    ],
    backgroundColor: colors[index].replace('0.8', '0.2'),
    borderColor: borderColors[index],
    borderWidth: 2,
    pointBackgroundColor: borderColors[index],
    pointBorderColor: '#fff',
    pointHoverBackgroundColor: '#fff',
    pointHoverBorderColor: borderColors[index]
}));

new Chart(document.getElementById('radarChart'), {
    type: 'radar',
    data: {
        labels: ['Populație', 'PIB', 'Ocupare', 'Longevitate', 'Educație', 'Aer Curat', 'Siguranță', 'Sănătate'],
        datasets: radarDatasets
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        weight: 'bold',
                        size: 13
                    },
                    generateLabels: function(chart) {
                        const datasets = chart.data.datasets;
                        return datasets.map((dataset, i) => ({
                            text: dataset.label,
                            fillStyle: colors[i],
                            strokeStyle: borderColors[i],
                            lineWidth: 2,
                            hidden: false,
                            index: i
                        }));
                    }
                }
            }
        },
        scales: {
            r: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    stepSize: 20
                }
            }
        }
    }
});
</script>
@endif

@endsection
