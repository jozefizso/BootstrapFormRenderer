<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

declare(strict_types=1);

namespace Kdyby\BootstrapFormRenderer\Latte;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette;
use Nette\Forms\Form;


/**
 * @internal runtime helpers of compiled {@see FormsExtension} tags
 */
final class Runtime
{
	use Nette\StaticClass;

	/**
	 * Resolves {form} argument: a Form instance, or a form component of the template's uiControl.
	 *
	 * @throws Nette\InvalidStateException
	 */
	public static function resolveForm(mixed $name, \stdClass $global): Form
	{
		if ($name instanceof Form) {
			return $name;
		}

		$control = $global->uiControl ?? NULL;
		if ((is_string($name) || is_int($name)) && $control instanceof Nette\ComponentModel\IContainer
			&& ($form = $control->getComponent((string) $name, FALSE)) instanceof Form
		) {
			return $form;
		}

		throw new Nette\InvalidStateException('No instanceof Nette\Forms\Form found in local scope. Ensure the template has a uiControl provider (a control template, or an engine with the uiControl provider).');
	}



	/**
	 * Renders the opening tag of the form on top of the forms runtime,
	 * through BootstrapRenderer when the form uses it.
	 */
	public static function renderBegin(Form $form, array $args, \stdClass $global): string
	{
		$renderer = $form->getRenderer();
		return $renderer instanceof BootstrapRenderer
			? $renderer->render($form, 'begin', $args)
			: $global->forms->renderFormBegin($args);
	}



	/**
	 * Opens the form scope for {input} and {label} without printing <form>.
	 * FormsLatte\Runtime::begin() resets the "rendered" option of every control;
	 * controls printed before a partial render must stay rendered, so the options are restored.
	 */
	public static function beginContext(Form $form, \stdClass $global): void
	{
		$rendered = [];
		foreach ($form->getControls() as $control) {
			$rendered[] = [$control, $control->getOption('rendered')];
		}

		$global->forms->begin($form, global: $global);

		foreach ($rendered as [$control, $value]) {
			$control->setOption('rendered', $value);
		}
	}

}
