<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ImageProxyController extends Controller
{
    public function show(Request $request)
    {
        $url = $request->query('url');
        
        if (!$url) {
            abort(404);
        }
        
        // Cache imaginea pentru 1 zi
        $cacheKey = 'image_proxy_' . md5($url);
        
        $imageData = Cache::remember($cacheKey, 86400, function () use ($url) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Referer' => parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST),
                    ])
                    ->get($url);
                
                if ($response->successful()) {
                    return [
                        'content' => $response->body(),
                        'type' => $response->header('Content-Type') ?? 'image/jpeg'
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Image proxy error: ' . $e->getMessage());
            }
            
            return null;
        });
        
        if ($imageData) {
            return response($imageData['content'])
                ->header('Content-Type', $imageData['type'])
                ->header('Cache-Control', 'public, max-age=86400');
        }
        
        abort(404);
    }
}
