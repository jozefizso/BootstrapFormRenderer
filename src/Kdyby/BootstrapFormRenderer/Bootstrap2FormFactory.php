<?php
// Copyright (c) 2026 Jozef Izso
// Licensed under terms in license.md file.
// SPDX-License-Identifier: BSD-3-Clause OR GPL-2.0 OR GPL-3.0

namespace Kdyby\BootstrapFormRenderer;

use Nette;
use Nette\Application\UI\Form;

/**
 * Factory for creating Nette forms preconfigured with Bootstrap 2 renderer.
 *
 * This factory simplifies form creation by automatically setting up the {@see BootstrapRenderer}
 * on a form, eliminating the need to manually call setRenderer() on every form.
 *
 * <code>
 * class RegistrationPresenter extends Nette\Application\UI\Presenter
 * {
 *     /** @var \Kdyby\BootstrapFormRenderer\Bootstrap2FormFactory @inject *\/
 *     public $bootstrap2FormFactory;
 *
 *     protected function createComponentContactForm()
 *     {
 *         $form = $this->bootstrap2FormFactory->create();
 *         $form->addText('email', 'E-mail');
 *         $form->addSubmit('submit', 'Submit');
 *         return $form;s
 *     }
 * }
 * </code>
 */
class Bootstrap2FormFactory extends Nette\Object
{
	/**
	 * Creates a new form instance with Bootstrap 2 renderer already configured.
	 *
	 * This method creates a standard {@see \Nette\Application\UI\Form} and automatically
	 * sets the {@see BootstrapRenderer} on it. The form is ready to use with
	 * Bootstrap 2 CSS classes and markup.
	 *
	 * @param \Nette\ComponentModel\IContainer $parent Optional parent component
	 * @param string $name Optional component name
	 * @return Form Form instance configured with Bootstrap 2 renderer
	 */
	public function create($parent = NULL, $name = NULL)
	{
		$form = new Form($parent, $name);
		$form->setRenderer(new BootstrapRenderer);
		return $form;
	}
}
