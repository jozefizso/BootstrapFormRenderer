<?php
// Copyright (c) 2026 Jozef Izso
// Licensed under terms in license.md file.
// SPDX-License-Identifier: BSD-3-Clause OR GPL-2.0 OR GPL-3.0

namespace Kdyby\BootstrapFormRenderer;

use Nette;

/**
 * Factory for creating Nette forms preconfigured with Bootstrap 2 renderer.
 *
 * This factory simplifies form creation by automatically creating {@see Bootstrap2Form}
 * instances, eliminating the need to manually set the renderer on every form.
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
 *         return $form;
 *     }
 * }
 * </code>
 */
class Bootstrap2FormFactory
{
	/**
	 * Creates a new Bootstrap2Form instance with Bootstrap 2 renderer already configured.
	 *
	 * This method creates a {@see Bootstrap2Form} which automatically uses the
	 * {@see BootstrapRenderer}. The form is ready to use with Bootstrap 2 CSS
	 * classes and markup.
	 *
	 * @param \Nette\ComponentModel\IContainer $parent Optional parent component
	 * @param string $name Optional component name
	 * @return Bootstrap2Form Form instance configured with Bootstrap 2 renderer
	 */
	public function create($parent = NULL, $name = NULL)
	{
		return new Bootstrap2Form($parent, $name);
	}
}
