# Task 05: Plan for supporting Nette 2.2

This repository now targets `nette/nette ~2.2.0` (`composer.lock` currently pins `nette/nette v2.2.14`) and therefore runs on **Latte 2.2** (`vendor/latte/latte/src/Latte/Engine.php`).

The goal of this task is to make the package work cleanly on Nette 2.2 (and keep PHP `>=5.6` support), while removing usage of renamed/deprecated Latte/Nette APIs that currently break the tests.

## Milestone issues (nette-2.2)

Open issues in the milestone and how they map to this task:

- #7 Apply Nette 2.2 rendering changes (umbrella; includes BootstrapRenderer, FormMacros, RendererExtension updates).
- #8 Update dependencies for Nette 2.2 (composer.json / lock updates).
- #9 Update CI workflow for Nette 2.2 (PHP + Nette matrix; target PHP 5.6 + 7.0 only).
- #72 Register UI + Forms macros on `nette.latteFactory`.
- #73 Fix renamed-class warning for `Nette\Latte\Macros\FormMacros` usage in `BootstrapRenderer`.
- #74 Refactor `Latte\FormMacros` to Latte 2.2 (`Latte\*`) APIs.
- #75 Update macro-validation tests to use `Latte\Engine` + `StringLoader`.
- #76 Reevaluate/remove legacy Nette 2.1 shims in `RendererExtension`.
- #77 Reduce dependency on deprecated `Nette\Templating\FileTemplate` in `BootstrapRenderer`.
- #79 Fix unit test fixtures for Nette 2.2 empty `value=""` rendering (TextInput omits empty value).

Notes:
- #7 is an umbrella; the concrete technical steps are mostly captured by #72–#77 and #79.
- #8 and #9 are project-level changes (dependencies/CI), not required to resolve the immediate Latte macro failures but needed for the final release.

## Observed test failures (from `test-results.xml`)

Distinct failure signatures:

1. **Unknown macro `{form}`** (multiple templates)
   - Example: `Latte\CompileException: Unknown macro {form} in .../input/multipleFormsInTemplate.latte:7`
   - Also seen in internal templates: `src/Kdyby/BootstrapFormRenderer/@form.latte:10`

2. **Unknown macro `{control}`**
   - Example: `Latte\CompileException: Unknown macro {control} in .../input/validation-with-translator.latte:7`

3. **Renamed-class warning treated as failure**
   - `E_USER_WARNING: Class Nette\Latte\Macros\FormMacros has been renamed to Nette\Bridges\FormsLatte\FormMacros.`
   - Triggered from `src/Kdyby/BootstrapFormRenderer/BootstrapRenderer.php` (call site in stack trace is around line ~134).

4. **Latte compiler type mismatch**
   - `E_RECOVERABLE_ERROR: Argument 1 passed to Kdyby\BootstrapFormRenderer\Latte\FormMacros::install() must be an instance of Nette\Latte\Compiler, instance of Latte\Compiler given`
   - Triggered in `src/Kdyby/BootstrapFormRenderer/Latte/FormMacros.php:65` via `tests/.../FormMacrosValidationTest.phpt`.

## Root cause analysis

### A) Latte 2.2 engine used by tests does not get macros installed

Nette 2.2’s DI wiring (see `vendor/nette/bootstrap/src/Bridges/Framework/NetteExtension.php`, method `setupLatte()`) introduces:

- `nette.latteFactory` (implements `Nette\Bridges\ApplicationLatte\ILatteFactory`)
- `nette.template` (deprecated `Nette\Templating\FileTemplate`) which registers filter via `latteFactory->create()`

Important detail: the deprecated `nette.template` is configured to use **`Latte\Engine` created by `nette.latteFactory`**, and *by default* this engine **does not install** Nette macros like `{control}` (`UIMacros`) or `{form}` (`FormsLatte\FormMacros`) unless you add them to `latte.onCompile` (or you go through `Nette\Bridges\ApplicationLatte\TemplateFactory`, which installs them internally).

This explains the “Unknown macro `{form}`” and “Unknown macro `{control}`” failures: templates are rendered using an engine that has no UI/forms macros installed.

### B) Usage of renamed `Nette\Latte\*` APIs triggers `E_USER_WARNING`

Nette 2.2 keeps `Nette\Latte\*` as renamed aliases via `Nette\Loaders\NetteLoader` (`vendor/nette/nette/Nette/Loaders/NetteLoader.php`). Referencing old names triggers `E_USER_WARNING`, which the test suite treats as a failure.

### C) Our custom macro set typehints are still Nette 2.1-era

`src/Kdyby/BootstrapFormRenderer/Latte/FormMacros.php` currently:

- Imports `Nette\Latte\*` symbols and typehints (`use Nette\Latte; ... install(Latte\Compiler $compiler)` where `Latte\Compiler` resolves to `Nette\Latte\Compiler`)
- Calls `Nette\Latte\Macros\FormMacros::*` which is a renamed class in Nette 2.2

On Nette 2.2, the actual compiler instance is `Latte\Compiler`, so strict type checks in PHP 5.6 cause recoverable errors.

## Upgrade plan

### Phase 1 — Make macros installation work under Nette 2.2 (fixes `{form}` / `{control}` unknown)

1. **Update DI integration to target `nette.latteFactory` (and keep `nette.latte` for deprecated users)**
   - Related: #72
   - File: `src/Kdyby/BootstrapFormRenderer/DI/RendererExtension.php`
   - Today we only add setup to `nette.latte`, but templates created by `nette.template` use `nette.latteFactory->create()`.
   - Follow the Nette 2.2 pattern used in `NetteExtension::setupLatte()`:
     - Add an `onCompile` callback to **both** `nette.latteFactory` and `nette.latte` so the macros are installed regardless of which engine is used.
   - In the callback, install macros in this order:
     1. `Nette\Bridges\ApplicationLatte\UIMacros::install($engine->getCompiler());` (enables `{control}`)
     2. `Nette\Bridges\FormsLatte\FormMacros::install($engine->getCompiler());` (baseline form macros)
     3. `Kdyby\BootstrapFormRenderer\Latte\FormMacros::install($engine->getCompiler());` (our override/enhancements)
   - Rationale: TemplateFactory already installs (1) and (2) for application templates, but the deprecated `nette.template` path does not. Installing them here makes tests and non-Presenter rendering consistent.

2. **Decide macro precedence explicitly**
   - Ensure our `{form ...}` macro remains the effective one (it implements extra modes like `{form errors}`, `{form body}`, etc).
   - Installing our macro set last should override the macro handler for the `form` macro name.

Acceptance criteria for Phase 1:
- `{form}` and `{control}` compile in all test templates.
- `test-results.xml` no longer contains “Unknown macro …”.

### Phase 2 — Remove renamed/deprecated Latte API usage (fixes `E_USER_WARNING` failures)

3. **Replace renamed macro class usage in renderer**
   - Related: #73
   - File: `src/Kdyby/BootstrapFormRenderer/BootstrapRenderer.php`
   - Replace `use Nette\Latte\Macros\FormMacros;` with `use Nette\Bridges\FormsLatte\FormMacros;` (or fully-qualified calls).
   - Ensure begin/end rendering uses `Nette\Bridges\FormsLatte\FormMacros::renderFormBegin()` / `renderFormEnd()` instead of the renamed `Nette\Latte\Macros\FormMacros`.

4. **Refactor our macro implementation to Latte 2.2 namespaces**
   - Related: #74
   - File: `src/Kdyby/BootstrapFormRenderer/Latte/FormMacros.php`
   - Replace `Nette\Latte\*` imports with `Latte\*` imports:
     - `Latte\CompileException`, `Latte\MacroNode`, `Latte\PhpWriter`, `Latte\Macros\MacroSet`, `Latte\Compiler`
   - Update method signatures accordingly (accept `Latte\Compiler`).
   - Replace calls to `Nette\Latte\Macros\FormMacros::*` with `Nette\Bridges\FormsLatte\FormMacros::*`.

Acceptance criteria for Phase 2:
- No `E_USER_WARNING: Class Nette\Latte\... has been renamed ...` in tests.
- No PHP 5.6 `E_RECOVERABLE_ERROR` due to compiler type mismatch.

### Phase 3 — Update tests to use Latte 2.2 engine correctly

5. **Fix compile-time macro validation tests to compile from strings using Latte 2.2**
   - Related: #75
   - File: `tests/KdybyTests/BootstrapFormRenderer/FormMacrosValidationTest.phpt`
   - Use `Latte\Engine` + `Latte\Loaders\StringLoader` and `Engine::compile()` (or `renderToString()` if needed) so the engine treats the input as source text, not a file.
   - Install the macros via `$engine->onCompile[] = function (Latte\Engine $engine) { ... }` rather than manually reaching into parser/compiler where possible.

6. **Keep the integration tests but ensure they exercise the same macro installation path as production**
   - The existing container-based tests should pass once Phase 1 is done (macros installed on `nette.latteFactory`).
   - If any template rendering still depends on deprecated `Nette\Templating\FileTemplate` behaviour, consider switching tests to `Nette\Bridges\ApplicationLatte\TemplateFactory` to match Nette 2.2 “happy path”.
   - Related: #77 (reducing `FileTemplate` usage).

7. **Update HTML fixtures for Nette 2.2 control rendering differences**
   - Related: #79
   - Update golden fixtures where `TextInput` omits empty `value=""` on Nette 2.2.

Acceptance criteria for Phase 3:
- `composer test` passes under PHP 5.6 and PHP 7.0 with `nette/nette ~2.2.0`.

### Phase 4 — Cleanup (required for Nette 2.2-only support)

7. **Remove Nette 2.1 compatibility shims (Nette 2.2-only)**
   - Related: #76
   - File: `src/Kdyby/BootstrapFormRenderer/DI/RendererExtension.php`
   - Remove `class_alias()` and `NetteLoader::renamed` hacks; no Nette 2.1 dual-support required.

8. **Consider migrating internal rendering away from deprecated `Nette\Templating\FileTemplate`**
   - Related: #77
   - `BootstrapRenderer` can be made to accept `Nette\Application\UI\ITemplate` (or a minimal abstraction) so it naturally works with `Nette\Bridges\ApplicationLatte\Template`.
   - This reduces reliance on deprecated templating APIs and matches Nette 2.2 architecture.

## Verification checklist

- Run `composer check` (lint + tests) on PHP 5.6 and 7.0.
- Confirm `test-results.xml` contains:
  - no “Unknown macro …”
  - no “has been renamed …” warnings
  - no recoverable errors related to Latte typehints
