<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "🔄 Traducere specificații pentru toate categoriile...\n\n";

// Dicționar de traduceri pentru fiecare categorie
$translations = [
    // Smartphone (ID: 1)
    1 => [
        'AnTuTu Score' => 'Scor AnTuTu',
        'AnTuTu benchmark score' => 'Scor benchmark AnTuTu',
        'Android version' => 'Versiune Android',
        'Baterie' => 'Baterie',
        'Battery life' => 'Autonomie baterie',
        'Bluetooth version' => 'Versiune Bluetooth',
        'Boxe stereo' => 'Boxe stereo',
        'CPU speed' => 'Frecvență CPU',
        'Camera Frontala' => 'Cameră frontală',
        'Camera Principala' => 'Cameră principală',
        'Capacitate baterie' => 'Capacitate baterie',
        'Cartele SIM' => 'Cartele SIM',
        'Chipset' => 'Chipset',
        'Chipset (SoC) name' => 'Nume chipset (SoC)',
        'Densitate pixeli' => 'Densitate pixeli',
        'Dimensiune ecran' => 'Dimensiune ecran',
        'Dimensiuni' => 'Dimensiuni',
        'Display' => 'Display',
        'Display type' => 'Tip display',
        'French Repairability Index' => 'Index reparabilitate (Franța)',
        'GPU' => 'GPU',
        'GPU name' => 'Nume GPU',
        'Gorilla Glass version' => 'Versiune Gorilla Glass',
        'Greutate' => 'Greutate',
        'Grosime' => 'Grosime',
        'Has USB Type-C' => 'Are USB Type-C',
        'Has a dual-lens (or multi-lens) main camera' => 'Are cameră principală cu lentile multiple',
        'Has a dual-tone LED flash' => 'Are bliț LED dual-tone',
        'Has a radio' => 'Are radio FM',
        'Has an ultra power-saving mode' => 'Are mod ultra-economisire',
        'Incarcare' => 'Încărcare',
        'Ingress Protection (IP) rating' => 'Nivel protecție IP',
        'Jack Audio' => 'Jack audio',
        'LDAC' => 'LDAC',
        'Luminozitate' => 'Luminozitate',
        'Macro' => 'Macro',
        'Nuclee CPU' => 'Nuclee CPU',
        'OIS' => 'Stabilizare optică (OIS)',
        'Procesor' => 'Procesor',
        'RAM' => 'RAM',
        'Refresh Rate' => 'Rată de reîmprospătare',
        'Rezistenta Apa' => 'Rezistență apă',
        'SIM cards' => 'Cartele SIM',
        'Sistem de Operare' => 'Sistem de operare',
        'Sistem de operare' => 'Sistem de operare',
        'Stocare' => 'Stocare',
        'Supports fast charging' => 'Suportă încărcare rapidă',
        'Teleobiectiv' => 'Teleobiectiv',
        'Tip USB' => 'Tip USB',
        'Tip display' => 'Tip display',
        'Touch sampling rate' => 'Rată de eșantionare tactilă',
        'USB version' => 'Versiune USB',
        'Ultra wide' => 'Ultra wide',
        'Versiune Android' => 'Versiune Android',
        'Versiune Bluetooth' => 'Versiune Bluetooth',
        'Versiune OS' => 'Versiune sistem operare',
        'Versiune WiFi' => 'Versiune Wi-Fi',
        'Video' => 'Video',
        'Wi-Fi version' => 'Versiune Wi-Fi',
        'aptX' => 'aptX',
        'battery power' => 'Capacitate baterie',
        'blocks cross-site tracking' => 'Blochează urmărire intersit',
        'brightness (typical)' => 'Luminozitate (tipică)',
        'can block app tracking' => 'Poate bloca urmărire aplicații',
        'charging speed' => 'Viteză încărcare',
        'comes with a charger' => 'Vine cu încărcător',
        'has LDAC' => 'Are LDAC',
        'has Mail Privacy Protection' => 'Are protecție confidențialitate email',
        'has NFC' => 'Are NFC',
        'has a BSI sensor' => 'Are senzor BSI',
        'has a battery level indicator' => 'Are indicator nivel baterie',
        'has a removable battery' => 'Are baterie detașabilă',
        'has a rugged build' => 'Are construcție robustă',
        'has a video light' => 'Are lumină video',
        'has an external memory slot' => 'Are slot memorie externă',
        'has aptX' => 'Are aptX',
        'has aptX Adaptive' => 'Are aptX Adaptive',
        'has aptX HD' => 'Are aptX HD',
        'has aptX Lossless' => 'Are aptX Lossless',
        'has branded damage-resistant glass' => 'Are sticlă rezistentă la șocuri',
        'has built-in optical image stabilization' => 'Are stabilizare optică integrată',
        'has clipboard warnings' => 'Are avertizări clipboard',
        'has location privacy options' => 'Are opțiuni confidențialitate locație',
        'has notification permissions' => 'Are permisiuni notificări',
        'has on-device machine learning' => 'Are învățare automată pe dispozitiv',
        'has reverse wireless charging' => 'Are încărcare wireless inversă',
        'has stereo speakers' => 'Are boxe stereo',
        'has theme customization' => 'Are personalizare teme',
        'has wireless charging' => 'Are încărcare wireless',
        'included SD card (memory size)' => 'Card SD inclus (capacitate)',
        'internal storage' => 'Stocare internă',
        'megapixels (front camera)' => 'Megapixeli (cameră frontală)',
        'megapixels (main camera)' => 'Megapixeli (cameră principală)',
        'number of flash LEDs' => 'Număr LED-uri bliț',
        'number of microphones' => 'Număr microfoane',
        'pixel density' => 'Densitate pixeli',
        'release date' => 'Dată lansare',
        'reverse wireless charging speed' => 'Viteză încărcare wireless inversă',
        'screen size' => 'Dimensiune ecran',
        'touch sampling rate' => 'Rată eșantionare tactilă',
        'video recording (main camera)' => 'Înregistrare video (cameră principală)',
        'volume' => 'Volum',
        'water resistance' => 'Rezistență apă',
        'waterproof depth rating' => 'Adâncime impermeabilitate',
        'wide aperture (main camera)' => 'Apertură largă (cameră principală)',
        'wireless charging speed' => 'Viteză încărcare wireless',
    ],
    
    // Oraș (ID: 2)
    2 => [
        'Cafenele' => 'Cafenele',
        'Calitate aer (index)' => 'Calitate aer (index)',
        'Centre comerciale' => 'Centre comerciale',
        'Cost trai' => 'Cost trai',
        'Femei' => 'Femei',
        'Index criminalitate' => 'Index criminalitate',
        'Index trafic' => 'Index trafic',
        'Linii transport public' => 'Linii transport public',
        'Mortalitate' => 'Mortalitate',
        'Muzee' => 'Muzee',
        'Natalitate' => 'Natalitate',
        'PIB per capita' => 'PIB per capita',
        'Piste biciclete' => 'Piste biciclete',
        'Puncte WiFi publice' => 'Puncte Wi-Fi publice',
        'Regiune' => 'Regiune',
        'Restaurante' => 'Restaurante',
        'Spitale' => 'Spitale',
        'Teatre' => 'Teatre',
    ],
    
    // Smartwatch (ID: 3)
    3 => [
        'Accelerometru' => 'Accelerometru',
        'Always-on display' => 'Display always-on',
        'Asistent vocal' => 'Asistent vocal',
        'Autonomie baterie' => 'Autonomie baterie',
        'Barometru' => 'Barometru',
        'Capacitate baterie' => 'Capacitate baterie',
        'Chipset' => 'Chipset',
        'Ciclu menstrual' => 'Urmărire ciclu menstrual',
        'Compatibil cu' => 'Compatibil cu',
        'Densitate pixeli' => 'Densitate pixeli',
        'ECG' => 'ECG',
        'GLONASS' => 'GLONASS',
        'Giroscop' => 'Giroscop',
        'Greutate' => 'Greutate',
        'Grosime' => 'Grosime',
        'Luminozitate' => 'Luminozitate',
        'Moduri antrenament' => 'Moduri antrenament',
        'Monitor ritm cardiac' => 'Monitor ritm cardiac',
        'Monitorizare somn' => 'Monitorizare somn',
        'Monitorizare stres' => 'Monitorizare stres',
        'Nuclee CPU' => 'Nuclee CPU',
        'RAM' => 'RAM',
        'Sistem operare' => 'Sistem operare',
        'Tip display' => 'Tip display',
        'Touchscreen' => 'Touchscreen',
        'Versiune Bluetooth' => 'Versiune Bluetooth',
        'Versiune OS' => 'Versiune sistem operare',
        'eSIM' => 'eSIM',
    ],
    
    // Placă video (ID: 4)
    4 => [
        'CUDA Cores' => 'Nuclee CUDA',
        'Chip GPU' => 'Chip GPU',
        'Conector alimentare' => 'Conector alimentare',
        'DLSS' => 'DLSS',
        'Data lansare' => 'Dată lansare',
        'Dimensiune die' => 'Dimensiune die',
        'DirectX' => 'DirectX',
        'DisplayPort' => 'DisplayPort',
        'FSR' => 'FSR',
        'Lungime' => 'Lungime',
        'Memorie video' => 'Memorie video',
        'Monitoare maxime' => 'Monitoare maxime',
        'OpenCL' => 'OpenCL',
        'OpenGL' => 'OpenGL',
        'PSU recomandat' => 'Sursă recomandată',
        'Porturi HDMI' => 'Porturi HDMI',
        'ROPs' => 'ROPs',
        'RT Cores' => 'Nuclee RT',
        'Ray Tracing' => 'Ray Tracing',
        'Sloturi ocupate' => 'Sloturi ocupate',
        'Stream Processors' => 'Procesoare stream',
        'TDP' => 'TDP',
        'TMUs' => 'TMUs',
        'Tensor Cores' => 'Nuclee Tensor',
        'Tip memorie' => 'Tip memorie',
        'Versiune DisplayPort' => 'Versiune DisplayPort',
        'Versiune HDMI' => 'Versiune HDMI',
        'Vulkan' => 'Vulkan',
    ],
    
    // Căști wireless (ID: 5)
    5 => [
        'Asistent vocal' => 'Asistent vocal',
        'Calitate apeluri' => 'Calitate apeluri',
        'Capacitate baterie' => 'Capacitate baterie',
        'Codecuri audio' => 'Codecuri audio',
        'Conectare multipunct' => 'Conectare multipunct',
        'Controale tactile' => 'Controale tactile',
        'Culori disponibile' => 'Culori disponibile',
        'Dimensiune driver' => 'Dimensiune driver',
        'Greutate (per earbud)' => 'Greutate (per earbud)',
        'Microfoane ANC' => 'Microfoane ANC',
        'Mod gaming' => 'Mod gaming',
        'Moduri de sunet' => 'Moduri de sunet',
        'Personalizare EQ' => 'Personalizare EQ',
        'Sensibilitate' => 'Sensibilitate',
        'Sunet ambient' => 'Sunet ambient',
        'Versiune Bluetooth' => 'Versiune Bluetooth',
    ],
    
    // Frigider (ID: 6)
    6 => [
        'Blocare copii' => 'Blocare copii',
        'Brand' => 'Brand',
        'Capacitate congelator (litri)' => 'Capacitate congelator (litri)',
        'Capacitate frigider (litri)' => 'Capacitate frigider (litri)',
        'Cod EAN' => 'Cod EAN',
        'Cod produs' => 'Cod produs',
        'Consum anual (kWh)' => 'Consum anual (kWh)',
        'Culoare' => 'Culoare',
        'Display extern' => 'Display extern',
        'Frost Free' => 'Frost Free',
        'Mod Eco' => 'Mod Eco',
        'Model' => 'Model',
        'Multi Airflow' => 'Multi Airflow',
        'Nivel zgomot (dB)' => 'Nivel zgomot (dB)',
        'No Frost' => 'No Frost',
        'Sertar legume' => 'Sertar legume',
        'Stele congelator' => 'Stele congelator',
        'Suport sticle' => 'Suport sticle',
        'Tip compresor' => 'Tip compresor',
        'Tip degivrare' => 'Tip degivrare',
        'Tip display' => 'Tip display',
        'Tip instalare' => 'Tip instalare',
    ],
    
    // Mașină de spălat (ID: 7)
    7 => [
        'AquaStop' => 'AquaStop',
        'Blocare copii' => 'Blocare copii',
        'Brand' => 'Brand',
        'Cod EAN' => 'Cod EAN',
        'Consum energetic' => 'Consum energetic',
        'Control dezechilibru' => 'Control dezechilibru',
        'Culoare' => 'Culoare',
        'Diametru hublou (cm)' => 'Diametru hublou (cm)',
        'Direct Drive' => 'Direct Drive',
        'Display LED' => 'Display LED',
        'Display digital' => 'Display digital',
        'Finisaj' => 'Finisaj',
        'Greutate' => 'Greutate',
        'Material tambur' => 'Material tambur',
        'Model' => 'Model',
        'Motor inverter' => 'Motor inverter',
        'Nivel zgomot centrifugare' => 'Nivel zgomot centrifugare',
        'Picioare reglabile' => 'Picioare reglabile',
        'Program alergii' => 'Program alergii',
        'Program bumbac' => 'Program bumbac',
        'Program delicat' => 'Program delicat',
        'Program delicate' => 'Program delicate',
        'Program eco' => 'Program eco',
        'Program rapid' => 'Program rapid',
        'Program sintetice' => 'Program sintetice',
        'Program sportswear' => 'Program sportswear',
        'Programe automate' => 'Programe automate',
        'Programe disponibile' => 'Programe disponibile',
        'Smart Diagnosis' => 'Smart Diagnosis',
        'Tehnologie AI' => 'Tehnologie AI',
        'Tip display' => 'Tip display',
        'Tip instalare' => 'Tip instalare',
        'Tip motor' => 'Tip motor',
        'Touchscreen' => 'Touchscreen',
        'Volum tambur' => 'Volum tambur',
    ],
    
    // TV (ID: 8)
    8 => [
        'Ambilight' => 'Ambilight',
        'Consum de energie (tipic)' => 'Consum de energie (tipic)',
        'Control vocal' => 'Control vocal',
        'Dimensiune ecran' => 'Dimensiune ecran',
        'Format HDR' => 'Format HDR',
        'Greutate' => 'Greutate',
        'Porturi HDMI' => 'Porturi HDMI',
        'Porturi USB' => 'Porturi USB',
        'Sistem de operare' => 'Sistem de operare',
        'Suport HDR' => 'Suport HDR',
        'Tehnologie display' => 'Tehnologie display',
        'Unghi de vizionare' => 'Unghi de vizionare',
        'Wi-Fi integrat' => 'Wi-Fi integrat',
        'lifestyle design' => 'Design lifestyle',
    ],
];

$totalUpdated = 0;

foreach ($translations as $productTypeId => $specTranslations) {
    $productType = DB::table('product_types')->find($productTypeId);
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📦 {$productType->name}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $updated = 0;
    
    foreach ($specTranslations as $english => $romanian) {
        // Căutăm cheia în engleză
        $specKey = DB::table('spec_keys')
            ->where('product_type_id', $productTypeId)
            ->where('name', $english)
            ->first();
        
        if (!$specKey) {
            continue;
        }
        
        // Verificăm dacă există deja o cheie cu același slug
        $existingKey = DB::table('spec_keys')
            ->where('product_type_id', $productTypeId)
            ->where('slug', Str::slug($romanian))
            ->where('id', '!=', $specKey->id)
            ->first();
        
        if ($existingKey) {
            // Mergem specificațiile de la cheia veche la cea existentă
            DB::table('spec_values')
                ->where('spec_key_id', $specKey->id)
                ->update(['spec_key_id' => $existingKey->id]);
            
            // Ștergem cheia veche
            DB::table('spec_keys')->where('id', $specKey->id)->delete();
            
            echo "   ✓ {$english} → {$romanian} (fuzionat)\n";
        } else {
            // Actualizăm cu traducerea în română
            DB::table('spec_keys')
                ->where('id', $specKey->id)
                ->update([
                    'name' => $romanian,
                    'slug' => Str::slug($romanian),
                ]);
            
            echo "   ✓ {$english} → {$romanian}\n";
        }
        
        $updated++;
    }
    
    echo "\n   Total actualizate: $updated\n\n";
    $totalUpdated += $updated;
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ FINALIZAT!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Total specificații traduse: $totalUpdated\n";
