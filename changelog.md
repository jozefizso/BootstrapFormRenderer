# Changelog

## v2.2.0-preview

This release targets Nette Framework 2.2, Latte 2.2 and requires PHP 5.6+.
The Bootstrap v2.3.2 is the reference version for markup and test fixtures.


### Rendering

Enable Bootstrap forms rendering in a Nette 2.2 application with this config:

```neon
nette:
	latte:
		macros:
			- Kdyby\BootstrapFormRenderer\Latte\FormMacros
```

### Breaking Changes
* **Breaking**: `BootstrapRenderer` no longer calls `$template->setTranslator($form->getTranslator())`. If you relied on this for `{_...}` / `|translate` in Latte templates, configure Latte translation in your application instead. Form translations still work via `$form->setTranslator($translator)`.

### Changes
* BootstrapFormRenderer extensions will register into Latte 2.2 engine factory
* Use `Latte\Engine` as a fallback when rendering form outside a Nette presenter ([#73](https://github.com/jozefizso/BootstrapFormRenderer/issues/73))


## v2.1.4

This release removes the class alias for legacy Nette objects:

* `Nette\Config\CompilerExtension`
* `Nette\Config\Compiler`
* `Nette\Config\Helpers`
* `Nette\Config\Configurator`

_These are not required for the library to function correctly in Nette 2.1 only._


## v2.1.3

### Fixes
* The `{form body}` expression can be used inside a `<form>` element.

Allows the expected syntax with HTML form and Latte control:

```html
<form n:name="frm" class="custom-styling">
  {form body}
</form>
```


## v2.1.2

### Breaking Changes

#### Form Macros - Latte 2.1 Runtime Variables

- **Breaking**: Form macros now use only Latte 2.1 / Nette 2.1 core runtime variables (`$_control`, `$_form`) and no longer support internal aliases or fallback variables. ([#65](https://github.com/jozefizso/BootstrapFormRenderer/issues/65))
  - Removed `$__form` internal variable from macro-generated code
  - Removed fallback resolution for `$control`, `$form`, `$__control`, and `$__form` variables
  - Form macros now rely exclusively on:
    - `$_control` for component/form lookup in `{form name}` macros
    - `$_form` for the current form context inside `{form}...{/form}` blocks
  - These variables are automatically provided by Nette 2.1 presenter templates
  - **Migration**:
    - Custom templates that referenced `$__form` must switch to `$form` or `$_form`
    - Ensure templates are rendered in a standard Nette 2.1 presenter context that provides `$_control`
    - Custom renderer templates should pass only `form` and `_form` (not `__form`) to includes
  - Reference: Aligns with `Nette\Latte\Macros\FormMacros` behavior in Latte 2.1

#### Form Macros - Latte 2.1 Semantics

- **Breaking**: `{form name /}` now aligns with standard Latte 2.1 behavior and renders only the opening and closing form tags (begin + end), without the form body. ([#60](https://github.com/jozefizso/BootstrapFormRenderer/issues/60))
  - Previously, the self-closing `{form name /}` syntax rendered the entire form (begin + errors + body + end) via `$form->render(NULL)`, diverging from Latte 2.1 expectations.
  - Now, `{form name /}` is equivalent to `{form name}{/form}` and only outputs the `<form>` opening tag and `</form>` closing tag with hidden fields.
  - **Migration**: Replace `{form name /}` with `{control name}` (recommended) or `{form name}{form errors}{form body}{/form}` for full form rendering.
  - Removed `findCurrentToken()` reflection method and compiler internals that were used to detect the trailing `/` syntax.

## v2.1.0

This release targets Nette Framework 2.1 and requires PHP 5.6+.
The Bootstrap v2.3.2 is now the reference version for markup and test fixtures.

### Rendering
- **Breaking**: `BootstrapRenderer::$errorsAtInputs` now strictly splits error sources (removes duplicate control errors in alerts + inline output). ([#27](https://github.com/jozefizso/BootstrapFormRenderer/issues/27), [#49](https://github.com/jozefizso/BootstrapFormRenderer/issues/49))
  - `TRUE` (default): `findErrors()` returns `$form->getOwnErrors()`; `getControlError()` renders the first control error inline.
  - `FALSE`: `findErrors()` returns `$form->getErrors()`; `getControlError()` returns an empty element.
- `BootstrapRenderer::prepareControl()` adds the Bootstrap `.btn` class to both submitter controls (`Nette\Forms\ISubmitterControl`) and plain `Nette\Forms\Controls\Button`. ([#19](https://github.com/jozefizso/BootstrapFormRenderer/issues/19))

### Translation / i18n (Nette 2.1)
- Stop translating the `placeholder` control option in the renderer (passed through as-is). ([#29](https://github.com/jozefizso/BootstrapFormRenderer/issues/29), [#41](https://github.com/jozefizso/BootstrapFormRenderer/issues/41))
- Stop translating validation errors during rendering (`findErrors()` / `getControlError()`); rule messages are expected to be already translated by Nette 2.1 (`Rules::formatMessage()`), and `Nette\Utils\Html` values pass through unchanged. ([#42](https://github.com/jozefizso/BootstrapFormRenderer/issues/42), [#52](https://github.com/jozefizso/BootstrapFormRenderer/issues/52))

### Latte
- Dropped compatibility aliasing for `Nette\Bridges\FormsLatte\FormMacros`; macros use `Nette\Latte\Macros\FormMacros`. ([#43](https://github.com/jozefizso/BootstrapFormRenderer/issues/43), [#47](https://github.com/jozefizso/BootstrapFormRenderer/issues/47))

### Documentation
- Documented the release strategy / compatibility targets and Nette 2.1 translation boundaries (placeholders, rule messages, choice items). ([#6](https://github.com/jozefizso/BootstrapFormRenderer/issues/6), [#44](https://github.com/jozefizso/BootstrapFormRenderer/issues/44), [#53](https://github.com/jozefizso/BootstrapFormRenderer/issues/53), [#54](https://github.com/jozefizso/BootstrapFormRenderer/issues/54))


## v2.0.0

### Upgrading v1.1.0 -> v2.0.0

- The repo has been renamed, so you have to manually delete `vendor/kdyby/bootstrap-form-renderer` and then run `$ composer update kdyby/bootstrap-form-renderer`
- Namespace has changed "Kdyby\Extension\Forms\BootstrapRenderer" -> "Kdyby\BootstrapFormRenderer"
