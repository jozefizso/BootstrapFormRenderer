# BootstrapFormRenderer

[![Tests](https://github.com/jozefizso/BootstrapFormRenderer/actions/workflows/test.yml/badge.svg)](https://github.com/jozefizso/BootstrapFormRenderer/actions/workflows/test.yml)
[![Downloads this Month](https://img.shields.io/packagist/dm/jozefizso/bootstrap-form-renderer.svg)](https://packagist.org/packages/jozefizso/bootstrap-form-renderer)
[![Latest stable](https://img.shields.io/packagist/v/jozefizso/bootstrap-form-renderer.svg)](https://packagist.org/packages/jozefizso/bootstrap-form-renderer)

> **BootstrapFormRenderer** is a PHP library that automatically styles Nette Framework forms to look great with Bootstrap CSS.
> Instead of manually adding Bootstrap classes to each form field, this library handles all the styling for you.

When you build web forms using the Nette Framework, they normally render as plain HTML. This library acts as a renderer that
wraps your form fields in the proper Bootstrap markup - adding the right CSS classes, error styling, field grouping,
and layout structure that Bootstrap requires.

### Key features
- **Automatic Bootstrap styling** - Transforms standard Nette forms into Bootstrap-styled forms without manual HTML markup
- **Form validation styling** - Automatically applies Bootstrap error states and displays validation messages
- **Flexible layouts** - Supports horizontal and other Bootstrap form layouts
- **Smart field handling** - Properly handles different input types (text fields, checkboxes, radio buttons, buttons)
- **Input add-ons** - Supports prepend/append icons or text to form fields (like the @ symbol for email fields)
- **Translator support** - Works with Nette's translation system for multilingual forms
- **Group support** - Handles form field grouping with proper Bootstrap styling


## Requirements

* PHP 5.6 or 7.0
* [Nette Framework](https://github.com/nette/nette) 2.1


## Getting Started

Use [composer](http://getcomposer.org/doc/00-intro.md) to install the library:

```sh
$ composer require jozefizso/bootstrap-form-renderer
```


## Usage

First you have to register the renderer to form.

```php
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
$form->setRenderer(new BootstrapRenderer);
```

For performance optimizations, you can provider your own template instance.

```php
// $this instanceof Nette\Application\UI\Presenter
$form->setRenderer(new BootstrapRenderer($this->createTemplate()));
```

All the usage cases expects you to have the form component in variable named <code>$form</code>


### Macros

If you wanna use the special macros, you have to register them into Latte Engine.

```php
Kdyby\BootstrapFormRenderer\Latte\FormMacros::install($engine->compiler);
```

Or simply register the extension in `app/bootstrap.php` to allow them globally.

```php
Kdyby\BootstrapFormRenderer\DI\RendererExtension::register($configurator);
```


### Basic rendering

Entire form

```smarty
{control formName} or {form formName /}
```

Beginning of the form

```smarty
{$form->render('begin')} or {form $form} or {form formName}
```

Errors

> Renders only errors, that have not associated form element.

```smarty
{$form->render('errors')} or {form errors}
```

Body

> Renders all controls and groups, that are not yet rendered.

```smarty
{$form->render('body')} or {form body}
```

Controls

> Renders all controls, that are not yet rendered. Doesn't render buttons.

```smarty
{$form->render('controls')} or {form controls}
```

Buttons

> Renders all buttons, that are not yet rendered.

```smarty
{$form->render('buttons')} or {form buttons}
```

End

> Renders all hidden inputs, and then the closing tag of form.

```smarty
{$form->render('end')} or {/form}
```


### Rendering of form components

Control

> Renders the container div around the control, its label and input.

```smarty
{$form->render($form['control-name'])} or {pair control-name}
```

Container

> Renders all the inputs in container, that are not yet rendered.

```smarty
{$form->render($form['container-name'])} or {container container-name}
```

Group

> Renders fieldset, legend and all the controls in group, that are not yet rendered.

```smarty
{$form->render($form->getGroup('Group name'))} or {group "Group name"}
```

## License

You may use BootstrapFormRenderer library under the terms of either
the New BSD License or the GNU General Public License (GPL) version 2 or 3.

Read [LICENSE](license.md) for more information.

_Forked from the original [Kdyby/BootstrapFormRenderer](https://github.com/kdyby/BootstrapFormRenderer) by Filip Procházka._
