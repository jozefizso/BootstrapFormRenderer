<?php
// Copyright (c) 2026 Jozef Izso
// Licensed under terms in license.md file.
// SPDX-License-Identifier: BSD-3-Clause OR GPL-2.0 OR GPL-3.0

namespace Kdyby\BootstrapFormRenderer;

use Nette\Application\UI\Form;

/**
 * Form class preconfigured with Bootstrap 2 renderer.
 *
 * This class extends {@see \Nette\Application\UI\Form} and automatically sets
 * the {@see BootstrapRenderer} in its constructor, providing a simple way to create
 * Bootstrap-styled forms without needing a factory or manual renderer setup.
 *
 * <code>
 * class SignupPresenter extends Nette\Application\UI\Presenter
 * {
 *     protected function createComponentContactForm()
 *     {
 *         $form = new Bootstrap2Form;
 *         $form->addText('email', 'E-mail');
 *         $form->addSubmit('submit', 'Submit');
 *         return $form;
 *     }
 * }
 * </code>
 */
class Bootstrap2Form extends Form
{
	/**
	 * Creates a new form with Bootstrap 2 renderer already configured.
	 *
	 * @param \Nette\ComponentModel\IContainer $parent Optional parent component
	 * @param string $name Optional component name
	 */
	public function __construct($parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		$this->setRenderer(new BootstrapRenderer);
	}
}
