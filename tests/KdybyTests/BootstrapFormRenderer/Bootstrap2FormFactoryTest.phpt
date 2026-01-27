<?php
// Copyright (c) 2026 Jozef Izso
// Licensed under terms in license.md file.
// SPDX-License-Identifier: BSD-3-Clause OR GPL-2.0 OR GPL-3.0

/**
 * Test: Kdyby\BootstrapFormRenderer\Bootstrap2FormFactory.
 *
 * @testCase KdybyTests\BootstrapFormRenderer\Bootstrap2FormFactoryTest
 * @package Kdyby\BootstrapFormRenderer
 */

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\Bootstrap2FormFactory;
use Kdyby\BootstrapFormRenderer\DI\RendererExtension;
use Nette\Configurator;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';


/**
 * Tests for Bootstrap2FormFactory.
 */
class Bootstrap2FormFactoryTest extends TestCase
{

	/**
	 * @var \Nette\DI\Container
	 */
	protected $container;


	public function setUp()
	{
		$config = new Configurator();
		$config->setTempDirectory(TEMP_DIR);
		$config->addParameters(array('container' => array('class' => 'SystemContainer_' . md5(TEMP_DIR))));
		// Nette CacheExtension (2.3.x) uses SQLiteJournal by default, which requires pdo_sqlite.
		// Test runner uses `php -n`, so pdo_sqlite may be unavailable depending on runtime config.
		// Override journal to FileJournal to make tests deterministic across PHP installations.
		$config->onCompile[] = function ($config, $compiler) {
			$builder = $compiler->getContainerBuilder();
			if (method_exists($builder, 'hasDefinition') && $builder->hasDefinition('cache.journal')) {
				$builder->getDefinition('cache.journal')
					->setFactory('Nette\Caching\Storages\FileJournal', array(TEMP_DIR . '/cache'));
			}
		};
		RendererExtension::register($config);
		$this->container = $config->createContainer();
	}


	/**
	 * Test that Bootstrap2FormFactory can be instantiated directly
	 */
	public function testFactoryCanBeInstantiated()
	{
		$factory = new Bootstrap2FormFactory();
		Assert::type('Kdyby\BootstrapFormRenderer\Bootstrap2FormFactory', $factory);
	}


	/**
	 * Test that factory creates a valid Nette Application UI Form
	 */
	public function testFactoryCreatesForm()
	{
		$factory = new Bootstrap2FormFactory();
		$form = $factory->create();

		Assert::type('Nette\Application\UI\Form', $form);
	}


	/**
	 * Test that created form has BootstrapRenderer configured
	 */
	public function testCreatedFormHasBootstrapRenderer()
	{
		$factory = new Bootstrap2FormFactory();
		$form = $factory->create();

		// Get the form's renderer through reflection since there's no getter
		// In Nette 2.1, the renderer property is in the parent Form class
		$reflection = new \ReflectionClass('Nette\Forms\Form');
		$property = $reflection->getProperty('renderer');
		$property->setAccessible(TRUE);
		$renderer = $property->getValue($form);

		Assert::type('Kdyby\BootstrapFormRenderer\BootstrapRenderer', $renderer);
	}


	/**
	 * Test that factory can create form with parent component
	 */
	public function testFactoryCreatesFormWithParent()
	{
		$factory = new Bootstrap2FormFactory();
		$parent = new \Nette\ComponentModel\Container();
		$form = $factory->create($parent, 'testForm');

		Assert::type('Nette\Application\UI\Form', $form);
		Assert::same($parent, $form->getParent());
		Assert::same('testForm', $form->getName());
	}


	/**
	 * Test that factory is registered as DI service
	 */
	public function testFactoryIsRegisteredAsDIService()
	{
		Assert::true($this->container->hasService('twBootstrapRenderer.bootstrap2FormFactory'));

		$factory = $this->container->getService('twBootstrapRenderer.bootstrap2FormFactory');
		Assert::type('Kdyby\BootstrapFormRenderer\Bootstrap2FormFactory', $factory);
	}


	/**
	 * Test that factory service can be retrieved by type
	 */
	public function testFactoryCanBeRetrievedByType()
	{
		$factory = $this->container->getByType('Kdyby\BootstrapFormRenderer\Bootstrap2FormFactory');
		Assert::type('Kdyby\BootstrapFormRenderer\Bootstrap2FormFactory', $factory);
	}


	/**
	 * Test that forms created via DI service have Bootstrap renderer
	 */
	public function testFormCreatedViaDIServiceHasRenderer()
	{
		$factory = $this->container->getByType('Kdyby\BootstrapFormRenderer\Bootstrap2FormFactory');
		$form = $factory->create();

		// Get the form's renderer through reflection
		// In Nette 2.1, the renderer property is in the parent Form class
		$reflection = new \ReflectionClass('Nette\Forms\Form');
		$property = $reflection->getProperty('renderer');
		$property->setAccessible(TRUE);
		$renderer = $property->getValue($form);

		Assert::type('Kdyby\BootstrapFormRenderer\BootstrapRenderer', $renderer);
	}


	/**
	 * Integration test: verify form renders with Bootstrap markup
	 */
	public function testFormRendersWithBootstrapMarkup()
	{
		$factory = new Bootstrap2FormFactory();
		$form = $factory->create();

		$form->addText('email', 'Email');
		$form->addSubmit('send', 'Submit');

		// Render the form
		ob_start();
		$form->render();
		$output = ob_get_clean();

		// Check for Bootstrap 2 CSS classes
		Assert::contains('form-horizontal', $output);
		Assert::contains('control-group', $output);
		Assert::contains('control-label', $output);
		Assert::contains('controls', $output);
	}


	/**
	 * Test that multiple forms can be created independently
	 */
	public function testMultipleFormsCanBeCreated()
	{
		$factory = new Bootstrap2FormFactory();

		$form1 = $factory->create();
		$form1->addText('field1', 'Field 1');

		$form2 = $factory->create();
		$form2->addText('field2', 'Field 2');

		Assert::type('Nette\Application\UI\Form', $form1);
		Assert::type('Nette\Application\UI\Form', $form2);
		Assert::notSame($form1, $form2);

		// Verify each form has its own controls
		Assert::true(isset($form1['field1']));
		Assert::false(isset($form1['field2']));
		Assert::false(isset($form2['field1']));
		Assert::true(isset($form2['field2']));
	}
}


run(new Bootstrap2FormFactoryTest());
