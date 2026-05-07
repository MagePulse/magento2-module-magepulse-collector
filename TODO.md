# TODO

Items are removed from this file once completed. Numbers re-index after each removal.

---

## Security

### Critical / High

- [ ] **#1 — No authentication on `/magepulse_collector/retrieve/index`** — endpoint is publicly
  accessible with no token, signature, or IP check. Only guards against the `isEnabled()` flag.
  Consider a shared secret or HMAC-signed request from the MagePulse server.
  _Controller/Retrieve/Index.php_

- [ ] **#2 — No rate limiting on endpoints** — both `/retrieve` and `/ping` are unprotected against
  abuse (DoS, scraping). Implement rate limiting or rely on server-level controls and document
  the expectation.


### Medium

- [ ] **#3 — Wrong nonce constant** — `generateNonce()` uses `SODIUM_CRYPTO_SECRETBOX_NONCEBYTES`
  but should use `SODIUM_CRYPTO_BOX_NONCEBYTES`. Both are 24 bytes so it works, but the
  constant name is semantically incorrect and misleading.
  _Model/Encryptor.php:84_

---

## Code Quality

- [ ] **#4 — Missing return type on `execute()`** — should declare `): ResultInterface` (or
  `): ResponseInterface` per `HttpGetActionInterface`).
  _Controller/Retrieve/Index.php:54_

- [ ] **#5 — Inconsistent property visibility** — `$resultFactory` and `$configProvider` are
  `protected`; `$collectorPool` and `$encryptor` are `private`. Make all `private` unless
  intentional extensibility is required.
  _Controller/Retrieve/Index.php:32-35_

- [ ] **#6 — Missing parameter type hint on `checkCollectors()`** — should be `array $collectors`.
  _Model/CollectorPool.php:47_

- [ ] **#7 — Spacing violation in `CollectorInterface`** — `getData():array ;` should be
  `getData(): array;` (space after `:`, no space before `;`).
  _Model/Collectors/CollectorInterface.php:29_

- [ ] **#8 — Unused `Exception` import in `Encryptor`** — only `SodiumException` is caught/thrown.
  Remove the generic `use Exception;`.
  _Model/Encryptor.php:23_

- [ ] **#9 — Duplicate path prefix in `ConfigProvider`** — `$pathPrefix` (property) and
  `PATH_PREFIX` (constant) both hold `'magepulse_collector/'`. Remove the redundant one.
  _Model/ConfigProvider.php:30-37_

- [ ] **#10 — Snake_case variable names in controller** — `$time_start` and `$time_end` should be
  `$timeStart` and `$timeEnd` per PSR-12 / Magento 2 standards.
  _Controller/Retrieve/Index.php:72,74_

- [ ] **#11 — 19 PHPCS warnings** — mostly missing DocBlocks and blank-line formatting across four
  files. Run `phpcs` locally and fix all warnings.
  _Service/Key.php, Controller/Retrieve/Index.php, Setup/Patch/Data/KeyCreation.php,
  Block/System/Config/Form/Fieldset/Hint.php_

- [ ] **#12 — Line length violation** — line 76 of `Controller/Retrieve/Index.php` is over 120 chars;
  Magento 2 standard is 120.

---

## Magento 2 Best Practices

- [ ] **#13 — Unconstrained `magento/framework` dependency** — `"*"` should be replaced with a
  minimum version constraint (e.g. `">=2.4.6 <2.5"`) to prevent installing against
  incompatible framework versions.
  _composer.json:20_

- [ ] **#14 — No service contracts** — public operations (`Collector`, `Encryptor`,
  `ConfigProvider`) expose concrete classes rather than interfaces. Define interfaces to allow
  third-party DI substitution without touching controller code.

- [ ] **#15 — `MagePulse_Collector::config` ACL resource not defined in this module** — it is
  referenced in `system.xml` but assumed to be declared in `MagePulse_Core`. Add an `acl.xml`
  to this module defining the resource explicitly, or document the dependency.
