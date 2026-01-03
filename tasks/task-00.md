# task-00: Code map & release readiness (Nette 2.1 + Bootstrap 2)

## Goal
- Prepare this library for publishing a release targeting **Nette 2.1** and **Bootstrap 2.x** form markup, running on **PHP 5.6** and **PHP 7.0**.

## Decisions (confirmed)
- **Yes**: improve `BootstrapRenderer::findErrors()` so `errorsAtInputs` behaves as documented and doesn’t duplicate control errors as both form-level alerts and inline errors.
- **Yes**: target **Nette 2.1 only** and remove all pre-2.1 compatibility shims from `src/`.

## What I reviewed
- All git-tracked project files (`git ls-files`).
- `vendor/` was not exhaustively reviewed (third-party), but I spot-checked **Nette 2.1** Forms sources to confirm the `Form::getErrors()` semantics used by this renderer.
- Context7 was used for Nette docs (**legacy-2.1**) and Bootstrap docs (**v2.3.2**, forms section).

## Repository map
- `composer.json`: package metadata and constraints (`php >=5.6`, `nette/nette ~2.1.0`).
- `README.md`: short usage + requirements (already says Nette 2.1 + PHP 5.6).
- `docs/en/index.md`: longer docs, but currently reflects older origins (Nette 2.0, old package/namespace references).
- `src/Kdyby/BootstrapFormRenderer/BootstrapRenderer.php`: the core `IFormRenderer` implementation (Bootstrap 2 markup).
- `src/Kdyby/BootstrapFormRenderer/@form.latte`: full-form template and rendering blocks (Bootstrap 2 classes).
- `src/Kdyby/BootstrapFormRenderer/@parts.latte`: partial rendering dispatcher for `$form->render($mode, ...)`.
- `src/Kdyby/BootstrapFormRenderer/Latte/FormMacros.php`: Latte macros (`{form}`, `{pair}`, `{group}`, `{container}`) integrating with renderer.
- `src/Kdyby/BootstrapFormRenderer/DI/RendererExtension.php`: DI extension to auto-install the macros into Latte.
- `tests/`: Nette Tester suite (`.phpt`) with Latte input fixtures and expected HTML output fixtures.
- `.github/workflows/test.yml`: CI on PHP 5.6 and 7.0 (parallel-lint + Nette Tester).

## Architecture / code paths

### 1) `Kdyby\BootstrapFormRenderer\BootstrapRenderer`
File: `src/Kdyby/BootstrapFormRenderer/BootstrapRenderer.php`

Entry point:
- `render(Nette\Forms\Form $form, $mode = NULL, $args = NULL)`

High-level flow:
1) Ensures there is a Latte template instance:
   - Prefer cloning the presenter template if the form is in a presenter.
   - Otherwise create `Nette\Templating\FileTemplate` + register `Nette\Latte\Engine`.
2) On first render per form instance: calls `prepareControl()` for each control to:
   - Mark controls as not rendered.
   - Add Bootstrap classes and set renderer options (pair container, prepend/append, required, etc).
   - Ensure `<form>` has a `form-*` class; defaults to `form-horizontal`.
3) Renders:
   - Full form when `$mode === NULL` using `@form.latte`.
   - `begin`/`end` using Nette’s `FormMacros::renderFormBegin/End`.
   - Partial pieces (errors/body/controls/buttons/control/container/group) using `@parts.latte`.

Renderer-level helpers used by templates:
- Errors/groups/controls discovery:
  - `findErrors()`
  - `findGroups()`, `processGroup()`
  - `findControls(Container $container = NULL, $buttons = NULL)`
- Control classification:
  - `isSubmitButton()`, `isButton()`, `isCheckbox()`, `isRadioList()`, `isCheckboxList()`
- Control rendering helpers:
  - `getControlName()`, `getControlTemplate()`
  - `getControlDescription()` → `<p class="help-block">…</p>`
  - `getControlError()` → `<p class="help-inline">…</p>` (when enabled)
  - `getRadioListItems()`, `getCheckboxListItems()`
  - `mergeAttrs()` (supports `{pair name, input-* => ..., label-* => ...}`)

Bootstrap 2 markup expectations are mostly implemented in `@form.latte`, but `prepareControl()` sets up the right classes/options:
- Form: `form-horizontal` default.
- Pair container: `div.control-group` (+ `required`, + `error`).
- Labels: `control-label` (except checkbox, radio/checkbox lists), checkbox label uses `checkbox`.
- Buttons: `.btn` applied to `Button` and submit controls.
- Input addons: `input-prepend` / `input-append` wrappers with `.add-on`.

### 2) Latte macros (`Kdyby\BootstrapFormRenderer\Latte\FormMacros`)
File: `src/Kdyby/BootstrapFormRenderer/Latte/FormMacros.php`

Purpose:
- Adds `{form ...}`, `{pair ...}`, `{group ...}`, `{container ...}` macros that call `$form->render(...)` parts.

Notes:
- Contains compatibility shims for older Nette/Latte class names (to be removed for Nette 2.1-only release).
- Uses internal compiler token inspection (`findCurrentToken()`) to support “empty macros” edge cases.

### 3) DI extension (`Kdyby\BootstrapFormRenderer\DI\RendererExtension`)
File: `src/Kdyby/BootstrapFormRenderer/DI/RendererExtension.php`

Purpose:
- Hooks into `nette.latte` and installs `FormMacros` into Latte compiler.
- Provides `RendererExtension::register($configurator)` helper for non-NEON bootstrap registration.

Notes:
- Contains legacy class_alias compatibility and an aggressive `Nette\Configurator` alias workaround (to be removed for Nette 2.1-only release).

## Bootstrap 2 contract (as implemented)
Primary template: `src/Kdyby/BootstrapFormRenderer/@form.latte`

Key Bootstrap 2 classes in output:
- Form layout: `form-horizontal`
- Control wrapper: `control-group` + `controls`
- Labels: `control-label`
- Checkbox/radio labels: `checkbox` / `radio` (with optional `.inline`)
- Errors:
  - Form-level: `alert alert-error` with close button
  - Field-level: `control-group error` + `help-inline`
- Button container: `form-actions`
- Input groups: `input-prepend` / `input-append` with `.add-on`

The test fixtures in `tests/KdybyTests/BootstrapFormRenderer/**/output/*.html` lock this markup down.

## Compatibility check (Nette 2.1 + Bootstrap 2)
- Nette 2.1: `composer.json` pins `nette/nette ~2.1.0`. The library uses Nette 2.1-era APIs (`Nette\Object`, `Nette\Templating\FileTemplate`, `Nette\Latte\Engine`, `Nette\Bridges\FormsLatte\FormMacros`) and the test suite is wired around that.
- Bootstrap 2: Templates and tests consistently use Bootstrap 2 form classes (`control-group`, `controls`, etc.). Bootstrap v2.3.2 docs are available in Context7 and were cross-checked against the renderer output.

## Bootstrap 2.3.2 forms CSS cross-check (Context7)
Source: Bootstrap v2.3.2 docs (`docs/base-css.html`).

Matches (current renderer output aligns with docs/examples):
- Horizontal forms: `.form-horizontal` + `.control-group` + `.control-label` + `.controls`
- Validation state (error): `.control-group.error` + `.help-inline` (field error message)
- Checkboxes in horizontal forms: checkbox rendered inside `.controls` with `label.checkbox > input`
- Input addons: `.input-prepend` / `.input-append` with `.add-on` elements
- Form actions: submit buttons wrapped in `.form-actions` so they align with `.controls` in `.form-horizontal`

Gaps / potential mismatches (worth addressing for “Bootstrap 2.3.2 correct” behavior):
- Inline single checkbox: Bootstrap expects `label.checkbox.inline` (class on the label). Current code’s “inline” handling checks the *input* classes and can drop the `.controls` wrapper; that’s not the pattern shown in v2.3.2 docs and may break horizontal alignment.
- Inline radio list: Bootstrap supports `label.radio.inline`, but `getRadioListItems()` always emits `label.radio` (no option to make it inline, unlike checkbox lists).
- Non-error validation states: Bootstrap supports `.warning`, `.info`, `.success` on `.control-group`; renderer only applies `.error` automatically (fine for Nette’s default semantics, but it’s not covering the full Bootstrap state vocabulary).
- `input-prepend`/`input-append` on `<select>`: Bootstrap docs note selects aren’t supported; renderer will wrap any control if options are set (user-driven, but worth documenting).

## Parts that likely need upgrading before publishing

### 1) Remove pre-Nette 2.1 compatibility shims (required)
Files:
- `src/Kdyby/BootstrapFormRenderer/BootstrapRenderer.php` (class alias fallback for `Nette\Bridges\FormsLatte\FormMacros`)
- `src/Kdyby/BootstrapFormRenderer/Latte/FormMacros.php` (class alias fallback for `Nette\Bridges\FormsLatte\FormMacros`; token compatibility branch)
- `src/Kdyby/BootstrapFormRenderer/DI/RendererExtension.php` (aliases for `Nette\Config\*` → `Nette\DI\*` and `Nette\Configurator` workarounds)

Proposed task:
- [ ] Delete all backward-compatibility `class_alias` blocks and remove any `Nette\Config\*` fallback paths.
- [ ] Re-run the full test suite on PHP 5.6 and 7.0 (CI parity).

### 2) Fix `BootstrapRenderer::findErrors()` vs `errorsAtInputs` (required)
File: `src/Kdyby/BootstrapFormRenderer/BootstrapRenderer.php`

Current behavior:
- `findErrors()` returns `$form->getErrors()` regardless of `$errorsAtInputs`.
- In Nette 2.1, `Form::getErrors()` includes global errors **and** control errors (`Form::getErrors() = $this->errors + parent::getErrors()`).
- Result: when `$errorsAtInputs = TRUE` (default), the same field error can be rendered:
  - as a form-level alert (from `#errors`)
  - and as a field-level `help-inline` (from `#control`)

Intended behavior (per property docblock and docs):
- When `$errorsAtInputs = TRUE`: form-level alerts should show only “own” errors (no associated control), i.e. `Form::getOwnErrors()`.
- When `$errorsAtInputs = FALSE`: form-level alerts should include control errors too (and field-level inline errors should be suppressed).

Proposed task:
- [ ] Update `findErrors()` to use `getOwnErrors()` when `$errorsAtInputs` is enabled.
- [ ] Update fixtures that currently expect control errors in the form-level error list.
- [ ] Add a new fixture/test case covering `$errorsAtInputs = FALSE`.

### 3) Update `docs/en/index.md` (stale references)
File: `docs/en/index.md`

Issues:
- Mentions Nette 2.0.x and older package/namespace usage (`kdyby/bootstrap-form-renderer`, `Kdyby\\Extension\\Forms\\...`).
- README and `composer.json` already target Nette 2.1 and this repo/package name.

Proposed task:
- [ ] Rewrite docs to match this repo: `jozefizso/bootstrap-form-renderer`, Nette 2.1, `Kdyby\BootstrapFormRenderer\BootstrapRenderer`.
- [ ] Ensure examples match current DI extension registration and macro installation.
- [ ] Remove any claims/examples implying Nette 2.0 (or older) support.

### 4) CI workflow sanity check
File: `.github/workflows/test.yml`

Potential issues to verify before release:
- `actions/checkout@v6` is unusual (commonly `@v4`). Confirm it exists and is intended.
- `dorny/test-reporter@feature/700-nette-tester-junit-reporter` uses a branch ref; for reproducible releases consider pinning a tag/commit.

Proposed task:
- [ ] Confirm CI still runs as-is on GitHub Actions.
- [ ] If needed, pin stable action versions.

### 5) Minor consistency/hygiene (optional)
- Some headers refer to `license.txt`, but the repo uses `license.md`.
- `src/...` still includes “Kdyby” legacy headers/names (fine, but decide if you want to modernize docs wording).
- Composer autoload is PSR-0; consider PSR-4 if you want modernization (not required for this release goal).

### 6) Licensing cleanup & SPDX (requested)
Current state:
- `license.md` contains full **BSD 3-Clause** text, but **does not include** full GPL v2/v3 texts (only links).
- Source headers are inconsistent (`license.md` vs `license.txt`) and don’t explicitly state the dual-license choice.
- `composer.json` uses an array of SPDX IDs; it can be clearer as a single SPDX license expression.

Best-practice target (common in dual-licensed OSS):
- Keep a single top-level file (`LICENSE` or `LICENSE.md`) that clearly states the license choice, e.g.:
  - `BSD-3-Clause OR GPL-2.0-only OR GPL-3.0-only` (matches “GPL v2 or v3”), or
  - `BSD-3-Clause OR GPL-2.0-or-later` (simpler if “or later” is acceptable).
- Add full license texts as separate files (tooling-friendly), ideally in a `LICENSES/` folder using SPDX filenames:
  - `LICENSES/BSD-3-Clause.txt`
  - `LICENSES/GPL-2.0-only.txt` (or `GPL-2.0-or-later.txt`)
  - `LICENSES/GPL-3.0-only.txt`
- Add SPDX headers to source files for automated scanning (REUSE-style):
  - `SPDX-License-Identifier: BSD-3-Clause OR GPL-2.0-only OR GPL-3.0-only`
  - (Optional) `SPDX-FileCopyrightText: ...`

Proposed tasks:
- [ ] Decide whether GPL is “v2 or v3 only” vs “v2 or later” and standardize the SPDX expression everywhere.
- [ ] Split license texts into individual files (include full GPL texts, not just URLs).
- [ ] Update file headers to consistently reference the license and/or use SPDX (and remove `license.txt` mentions).
- [ ] Update `composer.json` `license` to the SPDX expression string for clarity.

## Tests: what exists and what’s missing

### Existing coverage (good)
Core test file:
- `tests/KdybyTests/BootstrapFormRenderer/BootstrapRendererTest.phpt` renders Latte templates and matches output against golden `.html` fixtures.

Fixture sets:
- Basic: form begin/end, errors, body, whole form, and macro usage (`tests/KdybyTests/BootstrapFormRenderer/basic/*`).
- Components: controls vs buttons, group/container rendering, partial rendering (`tests/KdybyTests/BootstrapFormRenderer/components/*`).
- Individual: per-control rendering for text/checkbox/radiolist/select/textarea/upload/submit and group attribute propagation (`tests/KdybyTests/BootstrapFormRenderer/individual/*`).
- Edge: multiple forms per template (`tests/KdybyTests/BootstrapFormRenderer/edge/*`).

Bootstrap-relevant assertions already covered via fixtures:
- `form-horizontal`, `control-group`, `controls`, `control-label`
- `.btn` class on buttons/submits
- `.input-prepend` with `.add-on` (email default `@`)
- `.control-group.error` + `.help-inline` for field errors

### Gaps / missing tests worth adding
1) `errorsAtInputs` behavior (see upgrade item #2)
   - [ ] Fixture for `$errorsAtInputs = TRUE` that ensures form-level errors do **not** include control errors.
   - [ ] Fixture for `$errorsAtInputs = FALSE` that ensures form-level errors **do** include control errors and inline errors are suppressed.

2) Required control styling
   - [ ] Add a control with `$control->setRequired()` and assert:
     - label gets `.required`
     - pair container gets `.required`

3) Placeholder translation / placeholder option
   - [ ] Set `setOption('placeholder', '...')` and verify `placeholder="..."` output (and translation if a translator is used).

4) `input-append` option branch
   - [ ] Add a control with `setOption('input-append', ...)` and assert `input-append` wrapper + `.add-on`.

5) Group ordering via `$renderer->priorGroups`
   - [ ] Set `priorGroups` and ensure the requested group renders first.

6) Group template override (`$group->setOption('template', ...)`)
   - [ ] Fixture ensuring custom group template is invoked.

7) Checkbox list rendering
   - [ ] Add/enable a checkbox list control and assert `.checkbox` labels and `.inline` behavior based on `display` option.

8) Inline checkbox/radio markup (Bootstrap 2.3.2)
   - [ ] Single checkbox: if inline is requested, assert `label.checkbox.inline` (and that it still renders inside `.controls` in `.form-horizontal`).
   - [ ] Radio list: add a fixture for inline rendering (`label.radio.inline`) once the renderer supports it.

9) “Don’t override existing `form-*` class”
   - [ ] Ensure that if the `<form>` already has e.g. `form-inline` / `form-search`, the renderer does not add `form-horizontal`.

## Open questions (remaining)
1) Do you want `docs/en/index.md` to be the primary docs (and expanded), or should we move/duplicate key usage docs into `README.md`?
2) Should we hard-limit supported PHP in `composer.json` (e.g. `<8`) to avoid installs on runtimes where Nette 2.1 isn’t supported?
3) Do you want CI action refs pinned to stable tags/commits for reproducible releases?
