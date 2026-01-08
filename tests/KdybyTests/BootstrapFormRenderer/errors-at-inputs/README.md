# Error Rendering Tests for `errorsAtInputs` Property

This directory contains tests for the `BootstrapRenderer::$errorsAtInputs` property, which controls how form validation errors are displayed.

## Test Cases

### `errorsAtInputs = TRUE` (default behavior)
These tests verify that when `errorsAtInputs = TRUE`:
- **Form-level errors** appear in Bootstrap alert boxes
- **Control errors** appear inline next to their inputs using `<p class="help-inline">`
- Control errors do **not** appear in the alert boxes

Test files:
- `true-errors-only.latte` - Tests form errors rendering only (alerts show only form-level error "General failure!")
- `true-errors-and-body.latte` - Tests combined errors + body rendering (alert shows "General failure!", inline shows "chybka!")

### `errorsAtInputs = FALSE`
These tests verify that when `errorsAtInputs = FALSE`:
- **All errors** (both form-level and control errors) appear in Bootstrap alert boxes
- **No inline errors** appear next to inputs
- The `<p class="help-inline">` element is not rendered for control errors

Test files:
- `false-errors-only.latte` - Tests form errors rendering (alerts show both "General failure!" and "chybka!")
- `false-errors-and-body.latte` - Tests combined errors + body rendering (alerts show both errors, no inline errors)

## Naming Convention

- `true-*` prefix: Tests with `errorsAtInputs = TRUE`
- `false-*` prefix: Tests with `errorsAtInputs = FALSE`

## Form Setup

All tests use `dataCreateRichForm()` which includes:
- Form-level error: "General failure!"
- Control error on `other[heslo]` field: "chybka!"

## Expected Behavior (Bootstrap 2.3.2)

### With `errorsAtInputs = TRUE` (default):
```html
<!-- Form alerts: only form-level errors -->
<div class="alert alert-error">
    <a class="close" data-dismiss="alert">×</a>General failure!
</div>

<!-- Control with inline error -->
<div id="frm-foo-other-heslo-pair" class="control-group error">
    <label class="control-label" for="frm-foo-other-heslo">Heslo</label>
    <div class="controls">
        <input type="password" name="other[heslo]" id="frm-foo-other-heslo">
        <p class="help-inline">chybka!</p>
    </div>
</div>
```

### With `errorsAtInputs = FALSE`:
```html
<!-- Form alerts: all errors (form + control) -->
<div class="alert alert-error">
    <a class="close" data-dismiss="alert">×</a>General failure!
</div>
<div class="alert alert-error">
    <a class="close" data-dismiss="alert">×</a>chybka!
</div>

<!-- Control without inline error -->
<div id="frm-foo-other-heslo-pair" class="control-group error">
    <label class="control-label" for="frm-foo-other-heslo">Heslo</label>
    <div class="controls">
        <input type="password" name="other[heslo]" id="frm-foo-other-heslo">
    </div>
</div>
```

## Implementation

The fix uses Nette 2.2's API methods:
- `Form::getOwnErrors()` - Returns only form-level errors (used when `errorsAtInputs = TRUE`)
- `Form::getErrors()` - Returns all errors including control errors (used when `errorsAtInputs = FALSE`)

See [src/Kdyby/BootstrapFormRenderer/BootstrapRenderer.php:226-251](../../../../src/Kdyby/BootstrapFormRenderer/BootstrapRenderer.php#L226-L251) for implementation details.
