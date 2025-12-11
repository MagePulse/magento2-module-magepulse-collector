#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Simple utility to generate a MagePulse keypair using the same logic as the module's Key service.
 * Usage: php bin/generate_magepulse_keys.php
 * If you have composer dependencies installed, the script will use vendor/autoload.php so
 * class loading will be consistent with the module. Otherwise it will require the Service/Key.php file directly.
 */

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (file_exists($autoload)) {
    require $autoload;
} else {
    // Try to require the Service class directly so the script works even without composer install
    $keyFile = $root . '/Service/Key.php';
    if (!file_exists($keyFile)) {
        fwrite(STDERR, "Could not find vendor/autoload.php or Service/Key.php.\n");
        exit(2);
    }
    require $keyFile;
}

use MagePulse\Collector\Service\Key;

// Ensure sodium functions are available
if (!function_exists('sodium_crypto_box_keypair')) {
    fwrite(STDERR, "The sodium extension is required (ext-sodium).\n");
    exit(3);
}

try {
    $key = new Key();
    $keyPair = $key->createKeyPair(); // returns internal keypair string

    $public = $key->getPublicKey();
    $private = $key->getPrivateKey();

    // Defensive checks
    if (!is_string($public) || $public === '') {
        fwrite(STDERR, "Warning: public key appears empty.\n");
    }
    if (!is_string($private) || $private === '') {
        fwrite(STDERR, "Warning: private key appears empty.\n");
    }

    // Print in a copy-paste friendly format
    echo "--- MAGEPULSE KEYPAIR ---" . PHP_EOL;
    echo "PUBLIC (hex, " . strlen($public) . " chars):\n" . $public . PHP_EOL . PHP_EOL;
    echo "PRIVATE (hex, " . strlen($private) . " chars):\n" . $private . PHP_EOL;

    // Exit success
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "Error generating keys: " . $e->getMessage() . "\n");
    exit(1);
}
