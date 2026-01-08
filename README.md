# BootstrapFormRenderer

[![Tests](https://github.com/jozefizso/BootstrapFormRenderer/actions/workflows/test.yml/badge.svg)](https://github.com/jozefizso/BootstrapFormRenderer/actions/workflows/test.yml)
[![Downloads this Month](https://img.shields.io/packagist/dm/jozefizso/bootstrap-form-renderer.svg)](https://packagist.org/packages/jozefizso/bootstrap-form-renderer)
[![Latest stable](https://img.shields.io/packagist/v/jozefizso/bootstrap-form-renderer.svg)](https://packagist.org/packages/jozefizso/bootstrap-form-renderer)

> **BootstrapFormRenderer** is a PHP library that automatically styles Nette Framework forms to look great with Bootstrap CSS.
> Instead of manually adding Bootstrap classes to each form field, this library handles all the styling for you.

When you build web forms using the Nette Framework, they normally render as plain HTML. This library acts as a renderer that
wraps your form fields in the proper Bootstrap markup - adding the right CSS classes, error styling, field grouping,
and layout structure that Bootstrap requires.

## Key features

- **Automatic Bootstrap styling** - Transforms standard Nette forms into Bootstrap-styled forms without manual HTML markup
- **Form validation styling** - Automatically applies Bootstrap error states and displays validation messages
- **Flexible layouts** - Supports horizontal and other Bootstrap form layouts
- **Smart field handling** - Properly handles different input types (text fields, checkboxes, radio buttons, buttons)
- **Input add-ons** - Supports prepend/append icons or text to form fields (like the @ symbol for email fields)
- **Translator support** - Works with Nette's translation system for multilingual forms
- **Group support** - Handles form field grouping with proper Bootstrap styling


## Requirements

- PHP 5.6 or 7.0
- [Nette Framework](https://github.com/nette/nette) 2.2


## Getting Started

Use [composer](http://getcomposer.org/doc/00-intro.md) to install the library:

```sh
composer require jozefizso/bootstrap-form-renderer
```

### Compatibility with Nette Framework

| Version  | Branch      | PHP      | compatible | Nette series  |
|----------|-------------|----------|------------|---------------|
| `^3.0.0` | `main`      | `>= 7.1` | `8.0`      | Nette 3.0     |
| `^2.4.0` | `nette-2.4` | `>= 5.6` | `8.0`      | Nette 2.4     |
| `^2.3.0` | `nette-2.3` | `>= 5.6` | `7.1`      | Nette 2.3     |
| `^2.2.0` | `nette-2.2` | `>= 5.6` | `7.0`      | Nette 2.2     |
| `^2.1.0` | `nette-2.1` | `>= 5.6` | `7.0`      | Nette 2.1     |
| `^2.0.0` | `nette-2.0` | `>= 5.2` | `5.6`      | Nette 2.0     |

_**Note:** Compatibility always applies to the latest patch release within each minor version series._


## Usage

### Step 1: Register the Renderer Extension

The easiest way to set up BootstrapFormRenderer is to register the extension in your `config.neon` configuration file:

```neon
extensions:
    twBootstrapRenderer: Kdyby\BootstrapFormRenderer\DI\RendererExtension
```

This automatically registers the Latte macros globally, making them available in all your templates.

**Alternative setup methods:**

If you prefer to register the extension programmatically in `app/bootstrap.php`:

```php
Kdyby\BootstrapFormRenderer\DI\RendererExtension::register($configurator);
```

Or if you need to register just the Latte macros manually:

```php
$engine->onCompile[] = function (Latte\Engine $engine) {
    Kdyby\BootstrapFormRenderer\Latte\FormMacros::install($engine->getCompiler());
};
```

### Step 2: Create Bootstrap-Styled Forms

There are three ways to create forms with Bootstrap styling:

#### Option A: Using Bootstrap2FormFactory (Recommended)

The recommended approach is to use the factory that's automatically registered when you enable the extension:

```php
use Kdyby\BootstrapFormRenderer\Bootstrap2FormFactory;

class RegistrationPresenter extends Nette\Application\UI\Presenter
{
    /** @var Bootstrap2FormFactory @inject */
    public $bootstrap2FormFactory;

    protected function createComponentContactForm()
    {
        // Factory creates Bootstrap2Form instances
        $form = $this->bootstrap2FormFactory->create();

        // Add your form fields as usual
        $form->addText('name', 'Name');
        $form->addText('email', 'E-mail')->setType('email');
        $form->addSubmit('register', 'Register');

        return $form;
    }
}
```

#### Option B: Using Bootstrap2Form Class

For simple cases or rapid prototyping, you can directly instantiate the `Bootstrap2Form` class:

```php
use Kdyby\BootstrapFormRenderer\Bootstrap2Form;

class RegistrationPresenter extends Nette\Application\UI\Presenter
{
    protected function createComponentContactForm()
    {
        // Bootstrap2Form has BootstrapRenderer already configured
        $form = new Bootstrap2Form;

        // Add your form fields as usual
        $form->addText('name', 'Name');
        $form->addText('email', 'E-mail')->setType('email');
        $form->addSubmit('register', 'Register');

        return $form;
    }
}
```

#### Option C: Manual Renderer Setup

If you need maximum control or are working with existing code, you can apply
the `BootstrapRenderer` directly:

```php
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;

// In your presenter or form factory method
protected function createComponentContactForm()
{
    $form = new Nette\Application\UI\Form;

    // Apply Bootstrap styling
    $form->setRenderer(new BootstrapRenderer);

    // Add your form fields as usual
    $form->addText('name', 'Name');
    $form->addText('email', 'E-mail')->setType('email');
    $form->addSubmit('send', 'Send');

    return $form;
}
```


### Step 3: Render Your Form in Templates

Now you can use the special macros in your Latte templates to render the form. See the examples below for various rendering options.


## Form Rendering Examples

### Complete Form Rendering

The simplest way to render a complete form with all Bootstrap styling:

```latte
{control contactForm}
```

### Partial Form Rendering

For more control over the form layout, you can render individual parts:

#### Opening and Closing Tags Only

To render just the form's opening `<form>` and closing `</form>` tags (without any form body), use the self-closing syntax:

```latte
{form contactForm /}
```

This is equivalent to:

```latte
{form contactForm}{/form}
```

Both render only the begin and end tags, following standard Latte 2.2 semantics. Use this when you want to manually render form content or integrate with other components.

#### Opening Tag with Custom Content

```latte
{form contactForm}
    {* Your custom content here *}
{/form}
```

#### Form Errors

Renders validation errors that aren't associated with specific fields (like form-level errors):

```latte
{form errors}
```

#### Form Body

Renders all form controls and groups that haven't been rendered yet:

```latte
{form body}
```

#### Controls Only

Renders all input controls (excluding buttons):

```latte
{form controls}
```

#### Buttons Only

Renders all submit and button controls:

```latte
{form buttons}
```

#### Complete Custom Layout Example

```latte
{form contactForm}
    {form errors}

    <div class="row">
        <div class="col-md-6">
            {pair name}
            {pair email}
        </div>
        <div class="col-md-6">
            {pair message}
        </div>
    </div>

    {form buttons}
{/form}
```

### Rendering Individual Form Components

#### Single Control

Renders a complete control with its label, input field, and validation messages:

```latte
{pair name}
```

Or using the verbose syntax:

```latte
{$form->render($form['name'])}
```

#### Container

Renders all controls within a container:

```latte
{container personalInfo}
```

#### Group

Renders a form group with its fieldset, legend, and all controls:

```latte
{group "Personal Information"}
```

## Validation Errors Rendering

The renderer provides control over how validation errors are displayed
through the `$errorsAtInputs` property:

### Default Behavior (errorsAtInputs = TRUE)

By default, the renderer distinguishes between form-level and control-level errors:
- **Form-level errors** (added with `$form->addError()`) appear in Bootstrap alert boxes at the top of the form
- **Control errors** (added with `$control->addError()`) appear inline next to their inputs with the `help-inline` class

```php
$form->setRenderer(new BootstrapRenderer());
// errorsAtInputs = TRUE by default
```

This prevents duplicate error messages and provides better UX by showing each error in the most appropriate location.

### Show All Errors in Alerts (errorsAtInputs = FALSE)

If you prefer to display all errors (both form-level and control errors) in alert boxes without inline errors:

```php
$renderer = new BootstrapRenderer();
$renderer->errorsAtInputs = FALSE;
$form->setRenderer($renderer);
```

With this setting:
- All errors appear in Bootstrap alert boxes at the top
- No inline error messages are displayed next to inputs
- The control groups still receive the `error` CSS class for styling


## Translation Behavior

BootstrapFormRenderer works seamlessly with Nette 2.2's built-in translation system. When you set a translator on your form using `$form->setTranslator($translator)`, almost everything is automatically translated—you don't need to manually translate individual elements.

### What Gets Translated Automatically

**Nette 2.2 automatically translates:**
- Control labels and captions
- Placeholders on text inputs and textareas
- Validation rule messages (both default and custom)
- Select options, radio button labels, and checkbox labels

**BootstrapFormRenderer translates:**
- Form group labels and descriptions
- Control descriptions (the `description` option)

### Important Guidelines

1. **Manual errors** - If you add errors manually using `$form->addError()` or `$control->addError()` with plain strings, translate them yourself before calling `addError()`. Rule-generated errors are translated automatically.

2. **Pre-rendered HTML** - Use `Nette\Utils\Html` for content that shouldn't be translated:
   ```php
   $htmlError = Html::el('strong')->setText('Already exists');
   $form['username']->addError($htmlError); // Not translated
   ```


## Latte Variable Conventions

BootstrapFormRenderer aligns with Latte 2.2 standard runtime variable conventions:

- **`$_control`** - The current component/presenter context (required for form lookup)
- **`$_form`** - The current form inside `{form}...{/form}` blocks

These variables are automatically provided by Nette 2.2 presenter templates.

### Template Requirements

When rendering forms in your templates:
- Ensure templates are rendered within a Nette presenter context
- The `$_control` variable must be available for the `{form name}` macro to resolve forms
- Inside `{form}...{/form}` blocks, `$_form` provides access to the current form

This is automatically handled in standard Nette 2.2 presenter templates and requires no additional configuration.


## License

You may use BootstrapFormRenderer library under the terms of either
the New BSD License or the GNU General Public License (GPL) version 2 or 3.

Read [LICENSE](license.md) for more information.

_Forked from the original [Kdyby/BootstrapFormRenderer](https://github.com/kdyby/BootstrapFormRenderer) by Filip Procházka._
