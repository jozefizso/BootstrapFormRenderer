# Changelog

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
