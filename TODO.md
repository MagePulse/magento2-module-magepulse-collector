# TODO

Items are removed from this file once completed.

---

## Security

### Critical / High

- [ ] **No authentication on `/magepulse_collector/retrieve/index`** — endpoint is publicly
  accessible with no token, signature, or IP check. Only guards against the `isEnabled()` flag.
  Consider a shared secret or HMAC-signed request from the MagePulse server.
  _Controller/Retrieve/Index.php_

- [ ] **No rate limiting on endpoints** — both `/retrieve` and `/ping` are unprotected against
  abuse (DoS, scraping). Implement rate limiting or rely on server-level controls and document
  the expectation.


### Medium

- [ ] **Wrong nonce constant** — `generateNonce()` uses `SODIUM_CRYPTO_SECRETBOX_NONCEBYTES`
  but should use `SODIUM_CRYPTO_BOX_NONCEBYTES`. Both are 24 bytes so it works, but the
  constant name is semantically incorrect and misleading.
  _Model/Encryptor.php:84_

- [ ] **`json_encode()` without `JSON_THROW_ON_ERROR`** — silent failure if data cannot be
  serialised. Add the flag (PHP 7.3+).
  _Controller/Retrieve/Index.php:69_

- [ ] **Native PHP file functions in `PluginModel`** — uses `file_exists()` and
  `file_get_contents()` directly instead of Magento's `File` driver (as `ModuleMetaInfo`
  already does). Inconsistent and bypasses Magento's filesystem abstraction.
  _Model/Collectors/PluginModel.php:93-98_

- [ ] **`json_decode()` without error checking in `PluginModel`** — if a `composer.json` is
  malformed, `json_decode()` returns `null` and the subsequent array access silently returns
  `'N/A'` without logging. Use `json_decode(..., true, 512, JSON_THROW_ON_ERROR)` inside the
  existing try/catch.
  _Model/Collectors/PluginModel.php:96_

---

## Code Quality

- [ ] **Missing return type on `execute()`** — should declare `): ResultInterface` (or
  `): ResponseInterface` per `HttpGetActionInterface`).
  _Controller/Retrieve/Index.php:54_

- [ ] **Inconsistent property visibility** — `$resultFactory` and `$configProvider` are
  `protected`; `$collectorPool` and `$encryptor` are `private`. Make all `private` unless
  intentional extensibility is required.
  _Controller/Retrieve/Index.php:32-35_

- [ ] **Missing parameter type hint on `checkCollectors()`** — should be `array $collectors`.
  _Model/CollectorPool.php:47_

- [ ] **Spacing violation in `CollectorInterface`** — `getData():array ;` should be
  `getData(): array;` (space after `:`, no space before `;`).
  _Model/Collectors/CollectorInterface.php:29_

- [ ] **Missing parameter type hints in `PluginModel`** — `getVersion($moduleName)` and
  `getStatus($moduleName)` should declare `string $moduleName`.
  _Model/Collectors/PluginModel.php:110,125_

- [ ] **Unused `Exception` import in `Encryptor`** — only `SodiumException` is caught/thrown.
  Remove the generic `use Exception;`.
  _Model/Encryptor.php:23_

- [ ] **Duplicate path prefix in `ConfigProvider`** — `$pathPrefix` (property) and
  `PATH_PREFIX` (constant) both hold `'magepulse_collector/'`. Remove the redundant one.
  _Model/ConfigProvider.php:30-37_

- [ ] **Weak return type on `ModuleMetaInfo::getModuleMeta()`** — returns `array` on success
  and `''` (empty string) on failure. Use a typed return (`array`) and return `[]` on failure,
  or throw an exception.
  _Model/ModuleMetaInfo.php:48_

- [ ] **Snake_case variable names in controller** — `$time_start` and `$time_end` should be
  `$timeStart` and `$timeEnd` per PSR-12 / Magento 2 standards.
  _Controller/Retrieve/Index.php:66-68_

- [ ] **19 PHPCS warnings** — mostly missing DocBlocks and blank-line formatting across four
  files. Run `phpcs` locally and fix all warnings.
  _Service/Key.php, Controller/Retrieve/Index.php, Setup/Patch/Data/KeyCreation.php,
  Block/System/Config/Form/Fieldset/Hint.php_

- [ ] **Line length violation** — line 73 of `Controller/Retrieve/Index.php` is 172 chars;
  Magento 2 standard is 120.

- [ ] **Version mismatch** — `composer.json` declares `0.0.5`; `etc/module.xml` declares
  `0.1.0`. Align these.

---

## Testing

- [ ] **No unit tests exist** — the module has zero test coverage despite handling
  security-sensitive operations (encryption, key management, data collection).
  Priority classes to test:
  - `Model/Encryptor.php` — encrypt/decrypt round-trip, nonce uniqueness, memory zeroing
  - `Model/ConfigProvider.php` — enabled/disabled state, null site license handling
  - `Model/Collectors/MagentoModel.php` — data shape validation
  - `Model/Collectors/PluginModel.php` — missing/malformed composer.json handling
  - `Model/CollectorPool.php` — invalid collector rejection
  - `Service/Key.php` — keypair generation and hex encoding

---

## Magento 2 Best Practices

- [ ] **Unconstrained `magento/framework` dependency** — `"*"` should be replaced with a
  minimum version constraint (e.g. `">=2.4.6 <2.5"`) to prevent installing against
  incompatible framework versions.
  _composer.json:20_

- [ ] **Overly broad `\Exception` catch in `PluginModel`** — catch specific exceptions
  (`\Magento\Framework\Exception\FileSystemException`, `\RuntimeException`) instead of the
  base `\Exception`.
  _Model/Collectors/PluginModel.php:99_

- [ ] **No service contracts** — public operations (`Collector`, `Encryptor`,
  `ConfigProvider`) expose concrete classes rather than interfaces. Define interfaces to allow
  third-party DI substitution without touching controller code.

- [ ] **`MagePulse_Collector::config` ACL resource not defined in this module** — it is
  referenced in `system.xml` but assumed to be declared in `MagePulse_Core`. Add an `acl.xml`
  to this module defining the resource explicitly, or document the dependency.
