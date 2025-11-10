<?php

/**
 * Simple Icecat API Test (without Laravel bootstrap)
 */

// Read credentials from .env
$envFile = __DIR__ . '/.env';
$username = null;
$password = null;

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'ICECAT_USERNAME=') === 0) {
            $username = trim(substr($line, 16));
        }
        if (strpos($line, 'ICECAT_PASSWORD=') === 0) {
            $password = trim(substr($line, 16));
        }
    }
}

echo "=== ICECAT API TEST ===\n\n";

if (!$username || !$password) {
    echo "❌ Credentials not found in .env\n";
    echo "Please add:\n";
    echo "ICECAT_USERNAME=your_username\n";
    echo "ICECAT_PASSWORD=your_password\n\n";
    exit(1);
}

echo "✓ Credentials found\n";
echo "Username: $username\n\n";

// Test connection with cURL
echo "Testing API connection...\n";

// Try with a common product EAN (Apple iPhone - widely available in Icecat)
$testEAN = "0194252098097"; // iPhone 13 Pro 128GB
$url = "https://live.icecat.biz/api/?UserName=" . urlencode($username) . "&Language=en&GTIN={$testEAN}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Skip SSL verification for development
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "\nHTTP Status: $httpCode\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
    exit(1);
}

if ($httpCode === 401) {
    echo "\n❌ Authentication Failed (HTTP 401)\n";
    echo "Your credentials are incorrect or the account is not activated.\n\n";
    echo "Please check:\n";
    echo "1. Username and password are correct\n";
    echo "2. Account is activated at https://icecat.biz\n";
    echo "3. You have confirmed your email address\n\n";
    exit(1);
}

if ($httpCode === 404) {
    echo "\n⚠️ Product not found (HTTP 404)\n";
    echo "Test EAN might not exist in Icecat database, but API connection works!\n\n";
    exit(0);
}

if ($httpCode !== 200) {
    echo "\n❌ Unexpected HTTP status: $httpCode\n";
    echo "Response: " . substr($response, 0, 200) . "...\n\n";
    exit(1);
}

// Parse JSON response
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "\n❌ Invalid JSON response\n";
    echo "Response: " . substr($response, 0, 500) . "...\n\n";
    exit(1);
}

echo "\n✅ SUCCESS! API connection works!\n\n";

// Display product info
if (isset($data['data']['GeneralInfo'])) {
    $info = $data['data']['GeneralInfo'];
    
    echo "Product Details:\n";
    echo "---------------\n";
    echo "Name: " . ($info['Title'] ?? 'N/A') . "\n";
    echo "Brand: " . ($info['Brand'] ?? 'N/A') . "\n";
    echo "MPN: " . ($info['PartCode'] ?? 'N/A') . "\n";
    echo "EAN: " . ($info['GTIN'][0] ?? 'N/A') . "\n";
    
    if (isset($data['data']['Image']['Pic500x500'])) {
        echo "Image: " . substr($data['data']['Image']['Pic500x500'], 0, 60) . "...\n";
    }
    
    // Count specifications
    $specCount = 0;
    if (isset($data['data']['FeaturesGroups'])) {
        foreach ($data['data']['FeaturesGroups'] as $group) {
            if (isset($group['Features'])) {
                $specCount += count($group['Features']);
            }
        }
    }
    
    echo "\nSpecifications found: $specCount\n";
    
    if ($specCount > 0) {
        echo "\nSample specifications:\n";
        echo "---------------------\n";
        $count = 0;
        foreach ($data['data']['FeaturesGroups'] as $group) {
            if (isset($group['Features'])) {
                foreach ($group['Features'] as $feature) {
                    if ($count++ >= 5) break 2;
                    $name = $feature['Feature']['Name']['Value'] ?? 'Unknown';
                    $value = $feature['Value'] ?? $feature['RawValue'] ?? 'N/A';
                    echo "- $name: $value\n";
                }
            }
        }
    }
} else {
    echo "⚠️ Unexpected response structure\n";
    echo "Raw response (first 500 chars):\n";
    echo substr($response, 0, 500) . "...\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "\nYou can now run: php test-icecat.php\n";
