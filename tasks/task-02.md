# task-02: New GitHub issues from Nette 2.1 “duplicate functionality” findings

This file proposes **new GitHub issues** to open based on the discovery that some translation logic historically implemented in `BootstrapFormRenderer` is already handled by **Nette 2.1** (and can therefore cause double-translation or inconsistencies when this package targets Nette 2.1 only).

Note: the placeholder translation finding is already covered by GitHub issue `#29`, so it is not listed here as a new issue to open.

Context summary (Nette 2.1):
- `Nette\Forms\Controls\TextBase::getControl()` translates the HTML `placeholder` attribute when present.
- `Nette\Forms\Rules::formatMessage()` translates validation rule messages using the **form translator**.
- Choice controls (`SelectBox`, `RadioList`, `CheckboxList`) translate prompt/items during control rendering.

---

## Proposed issue 1: Stop translating error messages in `BootstrapRenderer` (avoid double translation + support `Html` errors)

**Title**
- “BootstrapRenderer: do not translate form/control errors (Nette 2.1 already translates rule messages)”

**Problem**
- `BootstrapRenderer` currently translates errors at render time:
  - `BootstrapRenderer::findErrors()` translates values from `$form->getErrors()`.
  - `BootstrapRenderer::getControlError()` translates the first error from `$control->getErrors()`.
- In Nette 2.1, validation errors produced by rules are already translated in `Rules::formatMessage()` via `$form->getTranslator()`.
- If `BootstrapRenderer` translates these messages again, it can cause:
  - double-translation (“translation of translated text”)
  - “missing translation” artifacts when translators expect keys/IDs, not already-localized strings
  - inconsistent behavior compared to Nette’s `DefaultFormRenderer` (which renders errors as-is and does not translate them during rendering)

**Extra bug surfaced**
- `findErrors()` currently translates every error value without guarding `Html` instances. Nette supports `Html` errors in renderers; translating a `Html` object can break depending on translator implementation.

**How to reproduce**
1) Create a form with a translator that maps keys (e.g. `"This field is required." => "Toto pole je povinné."`) and returns a sentinel for unknown keys (like `MISSING_TRANSLATION: ...`).
2) Add a validation rule that produces an error message (required, email, etc.).
3) Render the form using `BootstrapRenderer`.
4) Observe the error output being translated twice (or ending as `MISSING_TRANSLATION: Toto pole je povinné.`).

**Expected behavior (Nette 2.1 target)**
- Rule-generated errors are translated exactly once by Nette (in `Rules::formatMessage()`).
- Renderer outputs error strings/`Html` as-is (no additional translation step).

**Proposed change**
- Remove render-time translation from:
  - `BootstrapRenderer::findErrors()`
  - `BootstrapRenderer::getControlError()`
- Ensure both code paths support `Nette\Utils\Html` errors (pass-through), matching Nette renderer expectations.
- If backward-compatibility is required for applications that relied on renderer-time translation of `addError()` strings, introduce an explicit opt-in flag (e.g. `$translateErrors`) defaulting to `FALSE` for Nette 2.1-only releases.

**Acceptance criteria**
- With a translator enabled, validation-rule errors appear translated once (no “missing translation” artifacts).
- `Html` errors render correctly.
- Existing fixture-based rendering tests still pass, with new test coverage added for this behavior.

**Suggested tests**
- Add tests similar to the placeholder tests:
  - form with translator + required rule → verify translated error appears once
  - form with translator + manual `$form->addError(Html::el(...))` → verify it renders (and is not passed through translator)

---

## Proposed issue 2: Remove remaining Latte FormMacros compatibility aliasing (Nette 2.1-only cleanup)

**Title**
- “Nette 2.1-only: drop `Nette\\Bridges\\FormsLatte\\FormMacros` aliasing and use `Nette\\Latte\\Macros\\FormMacros` directly”

**Problem**
- The codebase still contains compatibility aliasing for `Nette\Bridges\FormsLatte\FormMacros` (mapping it to `Nette\Latte\Macros\FormMacros`).
- Nette 2.1 ships `Nette\Latte\Macros\FormMacros` (the bridge namespace is not part of the Nette 2.1 package layout).
- Since this project now targets **only Nette 2.1**, the aliasing is unnecessary and makes the code harder to reason about.

**Proposed change**
- Replace imports/usages of `Nette\Bridges\FormsLatte\FormMacros` with `Nette\Latte\Macros\FormMacros`.
- Remove the `class_alias(...)` blocks that exist solely to support alternate namespaces.

**Acceptance criteria**
- No `class_alias` shims remain for Latte macros.
- Existing tests continue to pass.

---

## Proposed issue 3: Audit translation responsibilities and document “what is translated by Nette vs renderer”

**Title**
- “Document translation boundaries (Nette 2.1): placeholders, rule messages, choice items”

**Problem**
- The placeholder finding shows how easy it is for renderer logic to drift into responsibilities that Nette already covers.
- Without a clear contract, future changes can re-introduce double-translation bugs (errors, placeholders, choice items, etc.).

**Proposed change**
- Add a short developer doc (or README section) documenting:
  - What Nette 2.1 translates automatically (placeholder, rule messages, choice items, captions/labels).
  - What this renderer translates (if anything) and why.
  - Guidance for users: translate `addError()` messages themselves if they are not rule-generated, and use `Html` for already-rendered content.

**Acceptance criteria**
- A concise “Translation behavior” section exists and matches current implementation + tests.
