# task-01: Which missing tests to add now (Nette 2.1) vs later (Nette 2.4)

## Problem
We have 9 “missing test” gaps captured as GitHub issues (#27–#35). Some tests validate behavior already present in the Nette 2.1 codebase (low risk, high value now), while others effectively specify new/changed behavior or require test-harness refactors that are likely to be redone during a Nette upgrade.

## Why it matters
- Adding the right tests **before upgrading Nette** gives guardrails so the renderer’s Bootstrap 2 output doesn’t drift while moving 2.1 → 2.4.
- Tests that depend heavily on **templating/rendering bootstrapping** are more likely to require rewrites when modernizing for Nette 2.4.
- Versions before Nette 2.4 are no longer supported, so we should avoid investing in tests that will be immediately obsolete or require significant rework unless they directly protect a near-term change.

## Recommendation: include in Nette 2.1 release (add now)
These are “contract tests” for existing features/branches and should remain valuable through the Nette upgrade:

1) #27: `errorsAtInputs` behavior (form alerts vs inline errors)\n
   - Rationale: this is directly tied to the planned `findErrors()` improvement; we need coverage to prevent regressions and avoid duplicated errors in output.\n
   - Minimum: cover the default mode (no control errors in form-level alerts when `errorsAtInputs = TRUE`).\n
   - Nice-to-have: add the `errorsAtInputs = FALSE` mode once the test harness supports configuring renderer properties cleanly.

2) #28: Required control styling (`.required` on label and `.control-group`)
   - Rationale: validates a common UX convention; low coupling to Nette internals.

3) #29: Placeholder option (with and without translator)
   - Rationale: validates a real feature in `prepareControl()`; stable across Nette upgrades; improves i18n confidence.

4) #30: `input-append` option renders `.input-append` + `.add-on`
   - Rationale: complements existing prepend coverage; pure Bootstrap 2 markup; low risk.

5) #33: CheckboxList rendering (stacked + inline)
   - Rationale: code paths exist; Bootstrap label conventions are clear; adding fixtures reduces regression risk during upgrades.

6) #35: Don’t add `form-horizontal` when form already has `form-*`
   - Rationale: ensures renderer respects developer-selected Bootstrap 2 form type (`form-inline`, `form-search`); simple and high value.

## Recommendation: delay to Nette 2.4 work (do later)
These are either larger behavior decisions, missing features, or more coupled to template/renderer wiring likely to change for Nette 2.4:

1) #34: Inline checkbox/radio markup (`label.*.inline`) alignment with Bootstrap 2.3.2
   - Rationale: current behavior is not fully aligned and improving it likely changes markup; treat as a deliberate feature/behavior change with changelog notes.\n
   - Better timing: implement alongside Nette 2.4 modernization to avoid reworking templates twice.

2) #31: `BootstrapRenderer::$priorGroups` ordering
   - Rationale: valuable but requires configuring renderer state during tests; easiest after upgrading/modernizing the test harness.\n
   - Better timing: add after the Nette 2.4 harness is stabilized.

3) #32: Group `template` option override
   - Rationale: relies on how templates are instantiated/included; likely to shift when Latte integration is modernized.\n
   - Better timing: add once Nette 2.4 template wiring is finalized.

## Verification strategy
- Nette 2.1 release:
  - Add fixtures/tests for the “add now” set.\n
  - Ensure the `findErrors()` change is locked down by #27.\n
  - CI stays green on the supported PHP matrix for this release.
- Nette 2.4 upgrade:
  - Modernize the test harness first.\n
  - Then implement #31/#32/#34 to avoid rewriting these tests multiple times.

