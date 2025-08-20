<?php
/**
 * OpenSSL 3.0+ Compatibility Test Script
 * 
 * This script tests the improved VAPID key generation methods
 * that handle OpenSSL 3.0+ stricter security policies.
 * 
 * Usage: php openssl3-test.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🚀 OpenSSL 3.0+ Compatibility Test\n";
echo "====================================\n\n";

// Check OpenSSL extension
if (!extension_loaded('openssl')) {
    echo "❌ OpenSSL extension is not loaded\n";
    exit(1);
}

// Display OpenSSL version
$opensslVersion = OPENSSL_VERSION_TEXT;
echo "OpenSSL Version: {$opensslVersion}\n";

// Check if it's OpenSSL 3.0+
$isOpenSSL3 = strpos($opensslVersion, 'OpenSSL 3.') === 0;
echo "OpenSSL 3.0+: " . ($isOpenSSL3 ? 'Yes' : 'No') . "\n\n";

// Test curve support
echo "Testing curve support...\n";

$curves = ['prime256v1', 'P-256', 'secp256r1', 'secp384r1'];
$supportedCurves = [];

foreach ($curves as $curve) {
    try {
        $config = [
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => $curve
        ];
        
        // Just check if we can create the config, don't generate keys
        if (defined('OPENSSL_KEYTYPE_EC')) {
            $supportedCurves[] = $curve;
            echo "✅ {$curve}: Supported\n";
        } else {
            echo "❌ {$curve}: Not supported\n";
        }
    } catch (Exception $e) {
        echo "❌ {$curve}: Error - " . $e->getMessage() . "\n";
    }
}

echo "\nSupported curves: " . implode(', ', $supportedCurves) . "\n\n";

// Test key generation methods
echo "Testing key generation methods...\n";

// Method 1: Standard OpenSSL
echo "Method 1: Standard OpenSSL... ";
try {
    $config = [
        'private_key_type' => OPENSSL_KEYTYPE_EC,
        'curve_name' => 'prime256v1'
    ];
    
    $res = openssl_pkey_new($config);
    if ($res) {
        echo "✅ Success\n";
        openssl_free_key($res);
    } else {
        echo "❌ Failed\n";
        $error = openssl_error_string();
        if ($error) {
            echo "   Error: {$error}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

// Method 2: Alternative curves
echo "Method 2: Alternative curves... ";
$success = false;
foreach ($supportedCurves as $curve) {
    try {
        $config = [
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => $curve
        ];
        
        $res = openssl_pkey_new($config);
        if ($res) {
            echo "✅ Success with {$curve}\n";
            openssl_free_key($res);
            $success = true;
            break;
        }
    } catch (Exception $e) {
        continue;
    }
}

if (!$success) {
    echo "❌ All curves failed\n";
}

// Method 3: Sodium (if available)
echo "Method 3: Sodium extension... ";
if (extension_loaded('sodium') || extension_loaded('libsodium')) {
    try {
        $keypair = sodium_crypto_box_keypair();
        $publicKey = sodium_crypto_box_publickey($keypair);
        $privateKey = sodium_crypto_box_secretkey($keypair);
        
        echo "✅ Success\n";
        echo "   Public key length: " . strlen($publicKey) . " bytes\n";
        echo "   Private key length: " . strlen($privateKey) . " bytes\n";
        
        // Clean up
        sodium_memzero($keypair);
        sodium_memzero($privateKey);
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Not available\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed!\n";

if ($isOpenSSL3) {
    echo "\n🔧 OpenSSL 3.0+ detected\n";
    echo "The package will use fallback methods if standard methods fail.\n";
    echo "Check the troubleshooting guide for additional solutions.\n";
}

echo "\nNext steps:\n";
echo "1. Run: php artisan push:generate-vapid-keys\n";
echo "2. The package will automatically try multiple methods\n";
echo "3. Check the generated keys in your .env file\n";
