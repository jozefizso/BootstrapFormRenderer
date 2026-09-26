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
use Nette\Bridges\FormsLatte\Runtime as FormsLatteRuntime;
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
	 * Renders the opening form tag, through BootstrapRenderer when the form uses it.
	 */
	public static function renderBegin(Form $form, array $args): string
	{
		$renderer = $form->getRenderer();
		if ($renderer instanceof BootstrapRenderer) {
			$form->fireRenderEvents();
			return $renderer->render($form, 'begin', $args);
		}

		FormsLatteRuntime::initializeForm($form);
		return FormsLatteRuntime::renderFormBegin($form, $args);
	}

}
