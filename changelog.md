# Changelog

## Unreleased

### Fixed
- Fixed `errorsAtInputs` property to properly prevent duplicate control errors. When `errorsAtInputs = TRUE` (default), control errors no longer appear in both form alerts and inline next to inputs - they now only appear inline. Set `errorsAtInputs = FALSE` to show all errors in alert boxes without inline errors. ([#27](https://github.com/jozefizso/BootstrapFormRenderer/issues/27))

## Upgrading v1.1.0 -> v2.0.0

- The repo has been renamed, so you have to manually delete `vendor/kdyby/bootstrap-form-renderer` and then run `$ composer update kdyby/bootstrap-form-renderer`
- Namespace has changed "Kdyby\Extension\Forms\BootstrapRenderer" -> "Kdyby\BootstrapFormRenderer"
