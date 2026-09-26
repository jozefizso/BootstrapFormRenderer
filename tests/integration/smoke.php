<?php

require __DIR__ . '/vendor/autoload.php';

use Composer\InstalledVersions;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Forms\Form;

set_error_handler(function ($severity, $message, $file, $line, $context = NULL) {
	if (!(error_reporting() & $severity)) {
		return FALSE;
	}
	throw new ErrorException($message, 0, $severity, $file, $line);
});

function createIntegrationForm()
{
	$form = new Form();
	$form->setRenderer(new BootstrapRenderer());
	$form->addText('name', 'Name')->setRequired();
	$form->addEmail('email', 'E-mail')->setRequired();
	$form->addSubmit('send', 'Send');
	return $form;
}

function captureIntegrationOutput($callback)
{
	ob_start();
	try {
		$callback();
	} catch (Exception $e) {
		ob_end_clean();
		throw $e;
	}
	return ob_get_clean();
}

function assertIntegrationContains($needle, $haystack)
{
	if (strpos($haystack, $needle) === false) {
		throw new RuntimeException("Rendered form does not contain expected markup: $needle");
	}
}

if (!class_exists('Nette\\Application\\Application')) {
	throw new RuntimeException('Nette Application is not available.');
}

$html = captureIntegrationOutput(function () {
	createIntegrationForm()->render();
});
assertIntegrationContains('<form', $html);
assertIntegrationContains('form-horizontal', $html);
assertIntegrationContains('control-group required', $html);
assertIntegrationContains('type="email"', $html);
assertIntegrationContains('class="form-actions"', $html);

$partial = captureIntegrationOutput(function () {
	createIntegrationForm()->render('body');
});
assertIntegrationContains('control-group required', $partial);
if (strpos($partial, '<form') !== false) {
	throw new RuntimeException('Partial body rendering unexpectedly emitted a form element.');
}

$string = (string) createIntegrationForm();
assertIntegrationContains('form-horizontal', $string);

$latte = new Latte\Engine();
$latte->setLoader(new Latte\Loaders\StringLoader());
$latte->addExtension(new Nette\Bridges\FormsLatte\FormsExtension());
$latte->addExtension(new Kdyby\BootstrapFormRenderer\Latte\FormsExtension());
$template = $latte->renderToString('{form $form}{form body}{/form}', array('form' => createIntegrationForm()));
assertIntegrationContains('class="form-horizontal"', $template);
assertIntegrationContains('control-group required', $template);
assertIntegrationContains('</form>', $template);

$packages = array(
	'php' => PHP_VERSION,
	'latte/latte' => InstalledVersions::getPrettyVersion('latte/latte'),
	'nette/application' => InstalledVersions::getPrettyVersion('nette/application'),
	'nette/forms' => InstalledVersions::getPrettyVersion('nette/forms'),
	'nette/utils' => InstalledVersions::getPrettyVersion('nette/utils'),
	'nette/component-model' => InstalledVersions::getPrettyVersion('nette/component-model'),
);
echo json_encode($packages, JSON_UNESCAPED_SLASHES) . PHP_EOL;
