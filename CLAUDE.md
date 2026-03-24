# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Module Overview

`MagePulse_Collector` is a Magento 2 module that gathers system and plugin/module information from installed Magento instances and securely transmits it to MagePulse central servers. Data is encrypted using libsodium asymmetric cryptography (`ext-sodium`) before transmission.

- **Composer package**: `magepulse/magento2-module-collector`
- **Magento module name**: `MagePulse_Collector`
- **Depends on**: `magepulse/magento2-module-core`

## Commands

Tests and code quality checks run through Magento's test framework via CI. The project uses a custom GitHub Action (`clivewalkden/magento-actions@master`) targeting Magento 2.4.6-p8.

CI runs these checks on every push:
- **Unit tests**: `dev/tests/unit/phpunit.xml.dist` (run within Magento instance)
- **PHPStan**: Static analysis on module code
- **Mess Detector (PHPMD)**: Code quality metrics
- **PHPCS**: Magento2 coding standards

Local key generation utility:
```bash
php bin/generate_magepulse_keys.php
```

## Architecture

### Data Flow

```
GET /magepulse_collector/retrieve/index
  → Check enabled (ConfigProvider)
  → Collector::collect() → CollectorPool (mainGroup)
      → MagentoModel::getData()   # Magento version, edition, mode, PHP version
      → PluginModel::getData()    # All installed modules with composer metadata
  → Encryptor::encrypt(json)      # libsodium crypto_box with private key + site license
  → JSON response: {error, encryptedData, executionTime}
```

Health check endpoint: `GET /magepulse_collector/ping/index` → returns plain text "OK".

### Key Components

| Component | Purpose |
|-----------|---------|
| `Controller/Retrieve/Index.php` | Main data endpoint; orchestrates collection and encryption |
| `Controller/Ping/Index.php` | Health check endpoint |
| `Model/CollectorPool.php` | Registry of collector groups; validates `CollectorInterface` compliance |
| `Model/Collector.php` | Iterates collectors in a group and aggregates results |
| `Model/Collectors/CollectorInterface.php` | Single-method interface: `getData(): array` |
| `Model/Collectors/MagentoModel.php` | Collects Magento/PHP system info |
| `Model/Collectors/PluginModel.php` | Collects all module metadata by reading composer.json files |
| `Model/Encryptor.php` | Asymmetric encryption via `sodium_crypto_box()`; nonce prepended to ciphertext |
| `Service/Key.php` | Keypair generation using `sodium_crypto_box_keypair()` |
| `Model/ConfigProvider.php` | Config access: enabled flag, site license, stored keypair |
| `Model/ModuleMetaInfo.php` | Reads and caches composer.json for any module directory |
| `Setup/Patch/Data/KeyCreation.php` | Data patch: auto-generates keypair on install if not present |

### Adding a New Collector

1. Create a class implementing `Model/Collectors/CollectorInterface.php` (implement `getData(): array`)
2. Register it in `etc/di.xml` under the `mainGroup` argument of `CollectorPool`

### Encryption Scheme

- Uses `sodium_crypto_box()` (Curve25519/XSalsa20/Poly1305)
- Private key stored encrypted in Magento config; retrieved via `ConfigProvider::getPrivateKey()`
- Site license (MagePulse public key) combined with store private key to form the encryption keypair
- Output format: `{nonce_hex}.{ciphertext_hex}`
- Keys are zeroed from memory after use via `sodium_memzero()`

## Release Process

See `NOTES.md` for full details. Summary:
1. Update version in `etc/module.xml`
2. Merge `development` → `main` with `--no-ff`
3. Push to `main` — CI auto-updates `composer.json`, creates version tags, generates `CHANGELOG.md`
4. Merge `main` back to `development` with `[skip ci]` in commit message
