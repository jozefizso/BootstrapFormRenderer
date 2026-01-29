<?php
// Copyright (c) 2026 Jozef Izso
// Licensed under terms in license.md file.
// SPDX-License-Identifier: BSD-3-Clause OR GPL-2.0 OR GPL-3.0

/**
 * Test: Kdyby\BootstrapFormRenderer\Bootstrap2Form.
 *
 * @testCase KdybyTests\BootstrapFormRenderer\Bootstrap2FormTest
 * @package Kdyby\BootstrapFormRenderer
 */

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\Bootstrap2Form;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestHelpers.php';


/**
 * Tests for Bootstrap2Form.
 */
class Bootstrap2FormTest extends TestCase
{
	use BootstrapFormRendererTestHelpers;

	/**
	 * Test that Bootstrap2Form can be instantiated
	 */
	public function testFormCanBeInstantiated()
	{
		$form = new Bootstrap2Form();
		Assert::type('Kdyby\BootstrapFormRenderer\Bootstrap2Form', $form);
	}


	/**
	 * Test that Bootstrap2Form extends Nette\Application\UI\Form
	 */
	public function testFormExtendsNetteForm()
	{
		$form = new Bootstrap2Form();
		Assert::type('Nette\Application\UI\Form', $form);
	}


	/**
	 * Test that Bootstrap2Form has BootstrapRenderer configured by default
	 */
	public function testFormHasBootstrapRenderer()
	{
		$form = new Bootstrap2Form();
		$this->assertFormUsesBootstrapRenderer($form);
	}


	/**
	 * Test that Bootstrap2Form can be created with parent component
	 */
	public function testFormCanBeCreatedWithParent()
	{
		$parent = new \Nette\ComponentModel\Container();
		$form = new Bootstrap2Form($parent, 'testForm');

		Assert::type('Kdyby\BootstrapFormRenderer\Bootstrap2Form', $form);
		Assert::same($parent, $form->getParent());
		Assert::same('testForm', $form->getName());
	}


	/**
	 * Integration test: verify form renders with Bootstrap markup
	 */
	public function testFormRendersWithBootstrapMarkup()
	{
		$presenter = new PresenterMock();
		$form = new Bootstrap2Form($presenter, 'foo');
		$form->setAction('');
		$form->addText('email', 'Email');
		$form->addSubmit('send', 'Submit');

		$output = $this->captureOutput(function () use ($form) {
			$form->render();
		});
		$this->assertBootstrap2MarkupPresent($output);
	}


	/**
	 * Test that multiple forms can be created independently
	 */
	public function testMultipleFormsCanBeCreated()
	{
		$form1 = new Bootstrap2Form();
		$form1->addText('field1', 'Field 1');

		$form2 = new Bootstrap2Form();
		$form2->addText('field2', 'Field 2');

		Assert::type('Kdyby\BootstrapFormRenderer\Bootstrap2Form', $form1);
		Assert::type('Kdyby\BootstrapFormRenderer\Bootstrap2Form', $form2);
		Assert::notSame($form1, $form2);

		// Verify each form has its own controls
		Assert::true(isset($form1['field1']));
		Assert::false(isset($form1['field2']));
		Assert::false(isset($form2['field1']));
		Assert::true(isset($form2['field2']));
	}


	/**
	 * Test that renderer can be changed after instantiation
	 */
	public function testRendererCanBeChanged()
	{
		$form = new Bootstrap2Form();

		// Change renderer to default
		$newRenderer = new \Nette\Forms\Rendering\DefaultFormRenderer();
		$form->setRenderer($newRenderer);

		$renderer = $this->getFormRenderer($form);
		Assert::type('Nette\Forms\Rendering\DefaultFormRenderer', $renderer);
		Assert::notSame('Kdyby\BootstrapFormRenderer\BootstrapRenderer', get_class($renderer));
	}

}


class PresenterMock extends \Nette\Application\UI\Presenter
{
}


run(new Bootstrap2FormTest());
