<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    public function compare(Request $request)
    {
        $ids = $request->input('ids');
        
        if (!$ids) {
            return redirect('/categorii');
        }
        
        $productIds = is_array($ids) ? $ids : explode(',', $ids);
        
        $products = Product::whereIn('id', $productIds)
            ->with(['offers', 'specValues.specKey', 'productType.category', 'category'])
            ->get();
        
        // Check if Versus style is requested
        if ($request->input('style') === 'versus' && $products->isNotEmpty()) {
            return $this->versusCompare($products);
        }
        
        // Check if comparing cities
        $isCityComparison = $products->isNotEmpty() && 
                           $products->first()->category && 
                           $products->first()->category->slug === 'orase';
        
        if ($isCityComparison) {
            return view('compare-cities', compact('products'));
        }
        
        // Determine winner (cheapest product)
        $winner = null;
        if ($products->isNotEmpty()) {
            $winner = $products->sortBy(function($product) {
                return $product->offers->min('price') ?? PHP_FLOAT_MAX;
            })->first();
            
            if ($winner && $winner->offers->isNotEmpty()) {
                $winner->best_price = $winner->offers->min('price');
                $winner->best_offer_id = $winner->offers->sortBy('price')->first()->id;
            }
        }
        
        return view('compare', compact('products', 'winner'));
    }
    
    private function versusCompare($products)
    {
        $items = $products->map(function($product, $index) {
            $colors = ['#76b900', '#ed1c24', '#0071c5', '#f7931e', '#8e44ad', '#16a085'];
            
            // Extract metrics from specValues
            $metrics = [];
            foreach ($product->specValues as $specValue) {
                $key = $this->normalizeSpecKey($specValue->specKey->name);
                $value = $specValue->value_number ?? 
                        $this->extractNumber($specValue->value_string) ?? 
                        $specValue->value_bool;
                
                if ($value !== null) {
                    $metrics[$key] = is_numeric($value) ? (float)$value : $value;
                }
            }
            
            // DON'T add price to metrics - it will be in separate section
            // if ($product->offers->isNotEmpty()) {
            //     $metrics['price_eur'] = (float)$product->offers->min('price');
            // }
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'image_url' => $product->image_url,
                'product_url' => route('products.show', $product->id),
                'metrics' => $metrics,
                'price' => $product->offers->isNotEmpty() ? (float)$product->offers->min('price') : null,
                'color' => $colors[$index % count($colors)],
            ];
        })->values()->toArray();
        
        $metricDefinitions = $this->getMetricDefinitions($products->first()->product_type_id ?? 1);
        
        return \Inertia\Inertia::render('Compare/VersusDemo', [
            'items' => $items,
            'metricDefinitions' => $metricDefinitions,
        ]);
    }
    
    private function normalizeSpecKey($name)
    {
        $map = [
            // GPU
            'CUDA Cores' => 'cuda_cores',
            'Stream Processors' => 'cuda_cores',
            'Memorie Video' => 'memory_gb',
            'Memory' => 'memory_gb',
            'Boost Clock' => 'boost_clock_mhz',
            'Frecvență Boost' => 'frecventa_boost',
            'TDP' => 'tdp_watts',
            'Arhitectură' => 'arhitectura',
            'Lățime bandă memorie' => 'latime_banda_memorie',
            'Magistrală memorie' => 'magistrala_memorie',
            'Frecvență memorie' => 'frecventa_memorie',
            'Număr tranzistori' => 'numar_tranzistori',
            'Proces fabricație' => 'proces_fabricatie',
            'Performanță Ray Tracing' => 'performanta_ray_tracing',
            'Sursă recomandată' => 'sursa_recomandata',
            
            // Smartwatch / Wearables
            'Rezistență la apă' => 'rezistenta_la_apa',
            'Rezistență la praf' => 'rezistenta_la_praf',
            'Adâncime impermeabilitate' => 'adancime_impermeabilitate',
            'Nivel protecție IP' => 'nivel_protectie_ip',
            'Diagonală ecran' => 'diagonala_ecran',
            'Rezoluție lățime' => 'rezolutie_latime',
            'Rezoluție înălțime' => 'rezolutie_inaltime',
            'Luminozitate (tipică)' => 'luminozitate_tipica',
            'Rată de reîmprospătare' => 'rata_de_reimprospatare',
            'Rată refresh' => 'rata_refresh',
            'Lățime curea' => 'latime_curea',
            'Dimensiune carcasă' => 'dimensiune_carcasa',
            'Tip carcasă' => 'tip_carcasa',
            'Greutate' => 'greutate',
            'Grosime' => 'grosime',
            'Oxigen din sânge (SpO2)' => 'oxigen_din_sange_spo2',
            'Presiune sanguină' => 'presiune_sanguina',
            'Urmărire ciclu menstrual' => 'urmarire_ciclu_menstrual',
            'Tracking înot' => 'tracking_inot',
            'Index sănătate' => 'index_sanatate',
            'Temperatură corp' => 'temperatura_corp',
            'Plăți mobile' => 'plati_mobile',
            'Aplicație companion' => 'aplicatie_companion',
            'Busolă' => 'busola',
            'Bărbați' => 'barbati',
            'Stocare muzică' => 'stocare_muzica',
            
            // Smartphone / Laptop
            'RAM' => 'ram_gb',
            'Stocare' => 'storage_gb',
            'Stocare internă' => 'stocare_interna',
            'Baterie' => 'battery_mah',
            'Ecran' => 'screen_inch',
            'Cameră' => 'camera_mp',
            'Cameră frontală' => 'camera_frontala',
            'Cameră principală' => 'camera_principala',
            'Camera frontală' => 'camera_frontala',
            'Camera principală' => 'camera_principala',
            'Megapixeli (cameră frontală)' => 'megapixeli_camera_frontala',
            'Megapixeli (cameră principală)' => 'megapixeli_camera_principala',
            'Apertură camera principală' => 'apertura_camera_principala',
            'Apertură largă (cameră principală)' => 'apertura_larga_camera_principala',
            'Stabilizare optică (OIS)' => 'stabilizare_optica_ois',
            'Înregistrare video' => 'inregistrare_video',
            'Înregistrare video (cameră principală)' => 'inregistrare_video_camera_principala',
            'Număr LED-uri bliț' => 'numar_led_uri_blit',
            'Are bliț LED dual-tone' => 'are_blit_led_dual_tone',
            'Are cameră principală cu lentile multiple' => 'are_camera_principala_cu_lentile_multiple',
            'Are stabilizare optică integrată' => 'are_stabilizare_optica_integrata',
            'Are lumină video' => 'are_lumina_video',
            'Frecvență CPU' => 'frecventa_cpu',
            'Frecvență GPU' => 'frecventa_gpu',
            'Placă video' => 'placa_video',
            'Rezoluție' => 'rezolutie',
            'Rezoluție maximă' => 'rezolutie_maxima',
            'Rată de eșantionare tactilă' => 'rata_de_esantionare_tactila',
            'Rată eșantionare tactilă' => 'rata_esantionare_tactila',
            'Timp de răspuns' => 'timp_de_raspuns',
            'Are încărcare wireless' => 'are_incarcare_wireless',
            'Are încărcare wireless inversă' => 'are_incarcare_wireless_inversa',
            'Suportă încărcare rapidă' => 'suporta_incarcare_rapida',
            'Port încărcare' => 'port_incarcare',
            'Timp încărcare' => 'timp_incarcare',
            'Viteză încărcare' => 'viteza_incarcare',
            'Încărcare rapidă' => 'incarcare_rapida',
            'Încărcare wireless' => 'incarcare_wireless',
            'Viteză încărcare wireless' => 'viteza_incarcare_wireless',
            'Viteză încărcare wireless inversă' => 'viteza_incarcare_wireless_inversa',
            'Vine cu încărcător' => 'vine_cu_incarcator',
            'Încărcare inversă' => 'incarcare_inversa',
            'Are slot memorie externă' => 'are_slot_memorie_externa',
            'Are sticlă rezistentă la șocuri' => 'are_sticla_rezistenta_la_socuri',
            'Are construcție robustă' => 'are_constructie_robusta',
            'Certificare militară' => 'certificare_militara',
            'Are baterie detașabilă' => 'are_baterie_detasabila',
            'Dată lansare' => 'data_lansare',
            'Preț' => 'pret',
            'Preț lansare' => 'pret_lansare',
            'Garanție' => 'garantie',
            'Index reparabilitate (Franța)' => 'index_reparabilitate_franta',
            'Convertibil 2-în-1' => 'convertibil_2_in_1',
            'Blochează urmărire intersit' => 'blocheaza_urmarire_intersit',
            'Poate bloca urmărire aplicații' => 'poate_bloca_urmarire_aplicatii',
            'Are învățare automată pe dispozitiv' => 'are_invatare_automata_pe_dispozitiv',
            'Are protecție confidențialitate email' => 'are_protectie_confidentialitate_email',
            'Are opțiuni confidențialitate locație' => 'are_optiuni_confidentialitate_locatie',
            'Are permisiuni notificări' => 'are_permisiuni_notificari',
            'Are avertizări clipboard' => 'are_avertizari_clipboard',
            'Lățime' => 'latime',
            'Înălțime' => 'inaltime',
            'Adâncime' => 'adancime',
            
            // Căști wireless
            'Anulare zgomot activă (ANC)' => 'anulare_zgomot_activa_anc',
            'Mod transparență' => 'mod_transparenta',
            'Audio spațial' => 'audio_spatial',
            'Autonomie căști' => 'autonomie_casti',
            'Autonomie cu carcasă' => 'autonomie_cu_carcasa',
            'Baterie carcasă' => 'baterie_carcasa',
            'Timp încărcare' => 'timp_incarcare',
            'Încărcare' => 'incarcare',
            'Greutate carcasă' => 'greutate_carcasa',
            'Tip căpăcele' => 'tip_capacele',
            'Număr microfoane' => 'numar_microfoane',
            'Răspuns în frecvență' => 'raspuns_in_frecventa',
            'Impedanță' => 'impedanta',
            'Rază Bluetooth' => 'raza_bluetooth',
            'Viteză wireless' => 'viteza_wireless',
            'Latență redusă' => 'latenta_redusa',
            'Împerechere NFC' => 'imperechere_nfc',
            'Găsire căști' => 'gasire_casti',
            'Pauză automată' => 'pauza_automata',
            'Reducere zgomot vânt' => 'reducere_zgomot_vant',
            'Redare muzică' => 'redare_muzica',
            'Control aplicație' => 'control_aplicatie',
            'Timp rămas' => 'timp_ramas',
            
            // Frigider
            'Capacitate' => 'capacity_l',
            'Capacitate totală (litri)' => 'capacitate_totala_litri',
            'Clasă energetică' => 'clasa_energetica',
            'Etichetă energetică' => 'eticheta_energetica',
            'Consum energetic' => 'energy_kwh',
            'Poziție congelator' => 'pozitie_congelator',
            'Clasă climatică' => 'clasa_climatica',
            'Zgomot' => 'noise_db',
            'Clasă zgomot' => 'clasa_zgomot',
            'Număr rafturi' => 'numar_rafturi',
            'Rafturi ușă' => 'rafturi_usa',
            'Suport ouă' => 'suport_oua',
            'Mod vacanță' => 'mod_vacanta',
            'Răcire rapidă' => 'racire_rapida',
            'Congelare rapidă' => 'congelare_rapida',
            'Iluminare interioară' => 'iluminare_interioara',
            'Alarmă ușă deschisă' => 'alarma_usa_deschisa',
            'Ușă reversibilă' => 'usa_reversibila',
            'Deschidere ușă (grade)' => 'deschidere_usa_grade',
            'Unghi deschidere ușă' => 'unghi_deschidere_usa',
            'Tip ușă' => 'tip_usa',
            'Funcții smart' => 'functii_smart',
            'Sistem răcire' => 'sistem_racire',
            'Tip răcire' => 'tip_racire',
            'Greutate netă' => 'greutate_neta',
            'Greutate netă (kg)' => 'greutate_neta_kg',
            'Lățime (cm)' => 'latime_cm',
            'Înălțime (cm)' => 'inaltime_cm',
            'Adâncime (cm)' => 'adancime_cm',
            'Incorporabilă' => 'incorporabila',
            'Independentă' => 'independenta',
            
            // Mașină de spălat
            'Capacitate de încărcare' => 'capacitate_de_incarcare',
            'Încărcare maximă' => 'incarcare_maxima',
            'Tip încărcare' => 'tip_incarcare',
            'Viteză centrifugare' => 'viteza_centrifugare',
            'Viteză maximă centrifugare' => 'viteza_maxima_centrifugare',
            'Clasă centrifugare' => 'clasa_centrifugare',
            'Eficiență centrifugare' => 'eficienta_centrifugare',
            'Centrifugare variabilă' => 'centrifugare_variabila',
            'Clasă spălare' => 'clasa_spalare',
            'Performanță spălare' => 'performanta_spalare',
            'Nivel zgomot spălare' => 'nivel_zgomot_spalare',
            'Consum apă' => 'consum_apa',
            'Număr programe' => 'numar_programe',
            'Program lână' => 'program_lana',
            'Program bebeluși' => 'program_bebelusi',
            'Program îmbrăcăminte sport' => 'program_imbracaminte_sport',
            'Prespălare' => 'prespalare',
            'Clătire extra' => 'clatire_extra',
            'Clătire plus' => 'clatire_plus',
            'Pornire întârziată' => 'pornire_intarziata',
            'Funcție abur' => 'functie_abur',
            'Funcție adăugare rufe' => 'functie_adaugare_rufe',
            'Adăugare haine în timpul spălării' => 'adaugare_haine_in_timpul_spalarii',
            'Curățare tambur' => 'curatare_tambur',
            'Control spumă' => 'control_spuma',
            'Control temperatură apă' => 'control_temperatura_apa',
            'Dozare automată detergent' => 'dozare_automata_detergent',
            'Protecție scurgeri' => 'protectie_scurgeri',
            'Protecție împotriva revărsării' => 'protectie_impotriva_revarsarii',
            'Garanție motor' => 'garantie_motor',
            'Tip mâner' => 'tip_maner',
            
            // TV
            'Mod artă' => 'mod_arta',
            'Zone de atenuare locală' => 'zone_de_atenuare_locala',
            'Tip formă' => 'tip_forma',
            
            // Cities (Orașe)
            'Populație' => 'populatie',
            'Suprafață' => 'suprafata',
            'Densitate populație' => 'densitate_populatie',
            'Vârstă mediană' => 'varsta_mediana',
            'Rata șomajului' => 'rata_somajului',
            'Temperatură medie' => 'temperatura_medie',
            'Precipitații anuale' => 'precipitatii_anuale',
            'Speranță de viață' => 'speranta_de_viata',
            'Timp mediu navetă' => 'timp_mediu_naveta',
            'Facilitați sport' => 'facilitati_sport',
            'Spații verzi' => 'spatii_verzi',
            'Spații coworking' => 'spatii_coworking',
            'Universități' => 'universitati',
        ];
        
        return $map[$name] ?? strtolower(str_replace(' ', '_', $name));
    }
    
    private function extractNumber($string)
    {
        if (!is_string($string)) return null;
        if (preg_match('/(\d+(?:\.\d+)?)/', $string, $matches)) {
            return (float)$matches[1];
        }
        return null;
    }
    
    private function getMetricDefinitions($productTypeId)
    {
        // GPU (type 3)
        if ($productTypeId == 3) {
            return [
                ['key' => 'cuda_cores', 'label' => 'CUDA Cores', 'higherIsBetter' => true],
                ['key' => 'memory_gb', 'label' => 'Memorie (GB)', 'higherIsBetter' => true],
                ['key' => 'boost_clock_mhz', 'label' => 'Boost Clock (MHz)', 'higherIsBetter' => true],
                ['key' => 'tdp_watts', 'label' => 'TDP (W)', 'higherIsBetter' => false],
            ];
        }
        
        // Smartwatch (type 2)
        if ($productTypeId == 2) {
            return [
                ['key' => 'ecran', 'label' => 'Ecran (inch)', 'higherIsBetter' => true, 'unit' => '"'],
                ['key' => 'greutate', 'label' => 'Greutate (g)', 'higherIsBetter' => false, 'unit' => 'g'],
                ['key' => 'baterie', 'label' => 'Baterie (ore)', 'higherIsBetter' => true, 'unit' => 'h'],
                ['key' => 'rezistență_la_apă', 'label' => 'Rezistență Apă', 'higherIsBetter' => true],
            ];
        }
        
        // Default - no specific metrics
        return [];
    }

    public function redirect(Request $request, $offerId)
    {
        $offer = \App\Models\Offer::findOrFail($offerId);
        
        // Track click
        \App\Models\AffiliateClick::create([
            'offer_id' => $offer->id,
            'product_id' => $offer->product_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect($offer->affiliate_url);
    }
}
