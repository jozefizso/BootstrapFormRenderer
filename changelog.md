# Changelog

## v3.3.0

This release targets Nette Framework 3.3 with Latte 3.1.4+ on PHP 8.3 through 8.5.

### Breaking changes

- Requires PHP >= 8.3, `latte/latte` `^3.1.4`, `nette/application` and `nette/forms` `~3.3.0`, `nette/utils` `^4.1` and `nette/component-model` `^3.2 || ^4.0`.
- The `{form}`, `{pair}`, `{group}` and `{container}` tags use the nette/forms 3.3 `forms` runtime provider (`$this->global->forms`). The `formsStack` provider and the static `Nette\Bridges\FormsLatte\Runtime::renderFormBegin()`/`renderFormEnd()` calls are gone. Templates that pushed a form with `addProvider('formsStack', …)` must call `$latte->getProviders()['forms']->begin($form)` before rendering and `->end()` after it instead.
- `Kdyby\BootstrapFormRenderer\Latte\Runtime::renderBegin()` takes the Latte `$global` as a third argument (internal helper of compiled templates).

### Changes

- `BootstrapRenderer::render()` renders the `begin` and `end` modes itself (ported from the removed forms 3.2 static runtime): GET forms drop the action query and emit its parameters as hidden fields, followed by unrendered hidden controls and `</form>`.
- `{form scope name}` and `{form detached name}`, new in nette/forms 3.3, are delegated to the nette/forms core node, so they keep their semantics when the Bootstrap extension overrides `{form}`.
- Partial rendering keeps controls printed before it (e.g. with `{input}`) marked as rendered, although `FormsLatte\Runtime::begin()` resets that option.
- `findControls()` wraps the iterable returned by forms 3.3 `Container::getControls()`.
- `RendererExtension` no longer hooks `TemplateFactory::$onCreate`; that workaround was needed only for nette/application 3.2.0.
- CI tests PHP 8.3–8.5 and the lowest (Latte 3.1.4, application/forms 3.3.0, utils 4.1.0, component-model 3.2.0) and highest Nette 3.3 dependency sets.

## v3.2.0

This release targets Nette Framework 3.2 with Latte 3.0.18+ on PHP 8.1 through 8.5.

_Note: Projects that must stay on Latte 2 engine, must use the BootstrapRenderer v3.0.x, which supports Nette 3.0 and 3.1 with Latte 2.x.

### Breaking changes

- Requires PHP >= 8.1, `latte/latte` `^3.0.18`, `nette/application` and `nette/forms` `~3.2.0`, `nette/utils` `^4.0.4` and `nette/component-model` `^3.1`. Latte 2 is not supported.
- `Kdyby\BootstrapFormRenderer\Latte\FormMacros` is removed. It is replaced by the Latte 3 extension `Kdyby\BootstrapFormRenderer\Latte\FormsExtension` (tags `{form}`, `{pair}`, `{group}`, `{container}`), which must be added after `Nette\Bridges\FormsLatte\FormsExtension`. Replace `latte: macros:` configuration with `latte: extensions:`.
- `{form name}` resolves the form from `$this->global->uiControl` only; the `$_control` template variable is no longer used, and the renderer no longer assigns `$_control`, `$_presenter` or `$_form`. `{form}` still accepts a `Form` instance.
- Compile errors keep their text, but Latte 3 drops the trailing period and appends the position, e.g. `Missing form name in {form} (on line 1 at column 1)`.
- Native types on the public API: `BootstrapRenderer::render(Form $form, string|object|null $mode = NULL, ?array $args = NULL): string`, `findErrors(): array`, `findGroups(): array`, `findControls(?Container $container = NULL, ?bool $buttons = NULL): \Iterator`, `processGroup(): ?\stdClass`, `isSubmitButton(?Control $control = NULL): bool` and the other helpers.
- Uses the non-`I` Nette APIs: `Nette\Forms\FormRenderer`, `Control`, `SubmitterControl`; `RendererExtension::register()` takes `Nette\Bootstrap\Configurator`.
- Custom group/control templates are included with Latte 3 rules: stream-wrapper paths such as `mock://…` are resolved relative to the internal template; use absolute file paths.

### Changes

- `BootstrapRenderer::render()` no longer rewrites the readonly `Template::$latte` (an `Error` on nette/application 3.2). With a presenter it clones the owning control's template, whose engine already carries the `uiControl`/`uiPresenter`/`uiNonce` providers, and adds the Latte extensions if missing; it no longer adds providers to that shared engine. When the presenter has no template factory, `getTemplate()` fails and the renderer falls back to a private engine wrapped in `DefaultTemplate`. That engine gets `UIExtension` for the form's control, so custom templates keep `{link}`, `{control}`, `{snippet}` and the `uiControl` lookup.
- `RendererExtension` adds the extension to the Latte factory in `beforeCompile()` and, for nette/application 3.2.0, which adds the core forms extension in `TemplateFactory::createTemplate()`, again through `TemplateFactory::$onCreate`.
- Internal templates are ported to Latte 3: top-level `{define}` blocks (`form`, `errors`, `body`, `group`, `controls`, `control`) instead of blocks nested in `{foreach}`, `{continueIf true}`, `{include block, key: val}`, and `{include $template}` for custom templates.
- Partial rendering (`$form->render('body')` etc.) pushes the form onto the forms stack through the internal `{bootstrapFormContext}` tag instead of overriding the `formsStack` provider. Unlike the core `{formContext}`, it keeps controls rendered earlier (e.g. with `{input}`) marked as rendered.
- `declare(strict_types=1)` in all sources; deprecated `getOption(..., $default)` calls replaced by `??`.
- CI tests PHP 8.1–8.5 and the lowest (Latte 3.0.18, application/forms 3.2.0, utils 4.0.4) and highest Nette 3.2 dependency sets. The integration smoke test also renders `{form}` on a plain Latte 3 engine.

### Regenerated test fixtures

- `fallback/basic`: whitespace-only blank-line differences produced by Latte 3.

## v3.0.0 — Nette 3.0/3.1, Latte 2.6+, PHP 7.1–8.3

This release targets Nette Framework 3.0 and 3.1 with Latte 2.6 through 2.11 on PHP 7.1 through 8.3.

### BC breaks

- Drops PHP < 7.1 and Nette 2.x. Requires `nette/application` and `nette/forms` `^3.0.8`/`^3.0.7` below 3.2, `nette/utils` `^3.1 || ~4.0.0`, `nette/component-model` `^3.0` and `latte/latte` `^2.6`.
- `BootstrapRenderer::render()` declares the `string` return type required by `Nette\Forms\IFormRenderer`.
- `BootstrapRenderer::mergeAttrs()` signature is `mergeAttrs(?Html $_this, array $attrs)`; `$_this` is no longer optional.
- `Bootstrap2Form::__construct()` and `Bootstrap2FormFactory::create()` take `?IContainer $parent = NULL, ?string $name = NULL` like `Nette\Application\UI\Form`; `create()` returns `Bootstrap2Form`.
- `RendererExtension` installs only the `Kdyby\BootstrapFormRenderer\Latte\FormMacros` on the Latte factory found by type in `beforeCompile()`; Nette's `LatteExtension` installs the UI and form macros. Containers without `LatteExtension` fail with `Nette\InvalidStateException`.

### Changes

- The fallback template outside a presenter is `Nette\Bridges\ApplicationLatte\DefaultTemplate` when available, avoiding PHP 8.2 dynamic-property deprecations on Nette 3.1.
- Internal templates check `$mode === NULL` instead of `isset($mode)`; the renderer always assigns `mode`.
- Group containers render through `Html::setName('')`, required by the typed Nette 3 `Html` API.
- `{form name}` resolves the form through the `uiControl` Latte provider that Nette 3 application templates register; Nette 3 no longer defines `$_control`, which remains a fallback for plain Latte engines. Only string names are looked up.
- `{form}` on forms without `BootstrapRenderer` calls nette/forms 3.1's `Runtime::initializeForm()`, so render events (and `UI\Form`'s hidden `do` signal field) fire as with Nette's own `{form}`.
- Removes Nette 2.x-era code comments, the `nette/safe-stream` dev dependency and test polyfills (`JSON_UNESCAPED_UNICODE`, `id()`, the global `Assert` alias).
- CI tests PHP 7.1–8.3 and the lowest and highest Nette 3.0 and 3.1 dependency sets.

### Regenerated test fixtures

- `edge`, `fallback` and `form-styling` outputs: nette/forms 3.0.8+ no longer renders the `<!--[if IE]><input type=IEbug …><![endif]-->` input before `</form>`; tests strip it from nette/forms 3.0.7 output.
- All outputs for forms attached to a test control: a named `Nette\Forms\Form` prefixes control ids with its name in Nette 3 (`frm-foo-foo-email` instead of `frm-foo-email`).
- `basic`, `components`, `errors-at-inputs` and `individual/image` outputs: upload controls render Nette 3's `:fileSize` rule in `data-nette-rules`.
- `translation/validation-with-translator`: Nette 3 no longer emits the `{"op":"optional"}` rule.
- `fallback/basic`: whitespace-only blank line before `</form>`.


## v2.4.0

This release targets Nette Framework 2.4 and Latte 2.x on PHP 5.6 through 8.0.

### Changes

- Allows Latte 2.4 through 2.11 and Nette Utils 2.4 or 2.5.5+.
- Uses Latte's `{import}` macro, avoiding the `{includeblock}` deprecation in Latte 2.11.
- Tests the lowest runtime-compatible and highest available 2.x dependency sets on every supported PHP runtime.
- Uses Nette 2.4 form runtime helpers, control-group accessors, renderer accessors, and list-control part APIs.
- Keeps application Latte filters, providers, template parameters, and initialized template subclass state in custom group and control templates.
- Uses Latte's native `{layout none}` directive for internal renderer templates instead of a custom presenter proxy.
- Tests all supported PHP runtimes without hiding warnings from renderer code.

### Fixes

- Resolves `{form name}` in custom group and control templates through the control that owns the rendered form, so sibling forms of a child control are found instead of being rendered as a mode of the current form.


## v2.3.1

This release targets Nette Framework 2.3, Latte 2.3 and requires PHP 5.6+.
The Bootstrap v2.3.2 is the reference version for markup and test fixtures.

### Changes

- **IFormRenderer implementation**: `BootstrapRenderer::render()` now returns generated HTML as string value, as required by `IFormRenderer`.
  The library now requires the `ApplicationLatte\Template` object to correctly render forms.
  _This is a breaking change._


## v2.3.0

This release targets Nette Framework 2.3, Latte 2.3 and requires PHP 5.6+.
The Bootstrap v2.3.2 is the reference version for markup and test fixtures.

### Changes

- **Nette 2.3 Forms Rendering**: submit buttons are rendered differently compared to Nette 2.2 forms. The `id` attribute might not be always present.


## v2.2.0

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
* **Breaking**: `BootstrapRenderer` requires individual runtime dependencies. This is a potential breaking change.
* **Breaking**: `BootstrapRenderer` no longer calls `$template->setTranslator($form->getTranslator())`. If you relied on this for `{_...}` / `|translate` in Latte templates, configure Latte translation in your application instead. Form translations still work via `$form->setTranslator($translator)`.
* **Breaking**: `BootstrapRenderer` and `Bootstrap2FormFactory` classes no longer extend from `Nette\Object` base class. This is a potential breaking change.

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
