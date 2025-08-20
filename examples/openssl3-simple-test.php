<?php
/**
 * Simple OpenSSL 3.0+ Compatibility Test Script
 * 
 * This script tests basic OpenSSL functionality without requiring
 * the full Laravel framework or vendor autoload.
 * 
 * Usage: php openssl3-simple-test.php
 */

echo "🚀 Simple OpenSSL 3.0+ Compatibility Test\n";
echo "==========================================\n\n";

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

// Test basic OpenSSL constants
echo "Testing OpenSSL constants...\n";
echo "OPENSSL_KEYTYPE_EC: " . (defined('OPENSSL_KEYTYPE_EC') ? '✅ Defined' : '❌ Not defined') . "\n";
echo "OPENSSL_KEYTYPE_RSA: " . (defined('OPENSSL_KEYTYPE_RSA') ? '✅ Defined' : '❌ Not defined') . "\n\n";

// Test curve support checking
echo "Testing curve support checking...\n";

// Method 1: Check if openssl_get_curve_names function exists
if (function_exists('openssl_get_curve_names')) {
    echo "✅ openssl_get_curve_names function available\n";
    $curves = openssl_get_curve_names();
    if (is_array($curves)) {
        echo "Available curves: " . implode(', ', array_slice($curves, 0, 10)) . "...\n";
        
        // Check for our required curves
        $requiredCurves = ['prime256v1', 'P-256', 'secp256r1'];
        $foundCurves = [];
        foreach ($requiredCurves as $curve) {
            if (in_array($curve, $curves)) {
                $foundCurves[] = $curve;
            }
        }
        
        if (!empty($foundCurves)) {
            echo "✅ Found required curves: " . implode(', ', $foundCurves) . "\n";
        } else {
            echo "⚠️  Required curves not found in available curves list\n";
        }
    }
} else {
    echo "❌ openssl_get_curve_names function not available\n";
}

echo "\n";

// Test key generation (without actually generating)
echo "Testing key generation configuration...\n";

try {
    // Test if we can create the configuration
    $config = [
        'private_key_type' => OPENSSL_KEYTYPE_EC,
        'curve_name' => 'prime256v1'
    ];
    
    echo "✅ Configuration array created successfully\n";
    echo "   private_key_type: " . $config['private_key_type'] . "\n";
    echo "   curve_name: " . $config['curve_name'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Configuration creation failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test actual key generation (this is what the package does)
echo "Testing actual key generation...\n";

try {
    $config = [
        'private_key_type' => OPENSSL_KEYTYPE_EC,
        'curve_name' => 'prime256v1'
    ];
    
    $res = openssl_pkey_new($config);
    if ($res) {
        echo "✅ Key generation successful with prime256v1\n";
        openssl_free_key($res);
    } else {
        echo "❌ Key generation failed with prime256v1\n";
        $error = openssl_error_string();
        if ($error) {
            echo "   Error: {$error}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Key generation exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test alternative curves
echo "Testing alternative curves...\n";
$alternativeCurves = ['secp256r1', 'P-256', 'secp384r1'];
$successfulCurve = null;

foreach ($alternativeCurves as $curve) {
    try {
        $config = [
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => $curve
        ];
        
        $res = openssl_pkey_new($config);
        if ($res) {
            echo "✅ Key generation successful with {$curve}\n";
            $successfulCurve = $curve;
            openssl_free_key($res);
            break;
        } else {
            echo "❌ Key generation failed with {$curve}\n";
            $error = openssl_error_string();
            if ($error) {
                echo "   Error: {$error}\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Key generation exception with {$curve}: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Test sodium extension (fallback method)
echo "Testing sodium extension (fallback method)...\n";
if (extension_loaded('sodium') || extension_loaded('libsodium')) {
    echo "✅ Sodium extension available\n";
    
    try {
        $keypair = sodium_crypto_box_keypair();
        $publicKey = sodium_crypto_box_publickey($keypair);
        $privateKey = sodium_crypto_box_secretkey($keypair);
        
        echo "✅ Sodium key generation successful\n";
        echo "   Public key length: " . strlen($publicKey) . " bytes\n";
        echo "   Private key length: " . strlen($privateKey) . " bytes\n";
        
        // Clean up
        sodium_memzero($keypair);
        sodium_memzero($privateKey);
        
    } catch (Exception $e) {
        echo "❌ Sodium key generation failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Sodium extension not available\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed!\n";

if ($isOpenSSL3) {
    echo "\n🔧 OpenSSL 3.0+ detected\n";
    echo "If you encounter curve support issues:\n";
    echo "1. The package will automatically try alternative methods\n";
    echo "2. Check the troubleshooting guide for OpenSSL 3.0+ solutions\n";
    echo "3. Consider enabling legacy algorithms in OpenSSL config\n";
} else {
    echo "\n✅ OpenSSL version should work normally\n";
}

echo "\nNext steps:\n";
echo "1. Install this package in a Laravel application\n";
echo "2. Run: php artisan push:generate-vapid-keys\n";
echo "3. The package will automatically handle OpenSSL compatibility\n";
