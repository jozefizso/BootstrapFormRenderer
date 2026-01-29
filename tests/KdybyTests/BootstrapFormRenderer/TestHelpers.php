<?php

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\DI\RendererExtension;
use Nette\Configurator;
use Nette\Forms\Form;
use Tester\Assert;

trait BootstrapFormRendererTestHelpers
{
	/**
	 * @param callable $callback
	 * @return string
	 */
	protected function captureOutput(callable $callback)
	{
		ob_start();
		try {
			$callback();
		} catch (\Exception $e) {
			ob_end_clean();
			throw $e;
		}

		return ob_get_clean();
	}


	/**
	 * @param \Nette\Forms\Form $form
	 * @return mixed
	 */
	protected function getFormRenderer(Form $form)
	{
		if (method_exists($form, 'getRenderer')) {
			return $form->getRenderer();
		}

		$reflection = new \ReflectionClass('Nette\\Forms\\Form');
		$property = $reflection->getProperty('renderer');
		$property->setAccessible(TRUE);
		return $property->getValue($form);
	}


	/**
	 * @param \Nette\Forms\Form $form
	 * @return void
	 */
	protected function assertFormUsesBootstrapRenderer(Form $form)
	{
		Assert::type('Kdyby\\BootstrapFormRenderer\\BootstrapRenderer', $this->getFormRenderer($form));
	}


	/**
	 * @param string $html
	 * @return void
	 */
	protected function assertBootstrap2MarkupPresent($html)
	{
		Assert::contains('form-horizontal', $html);
		Assert::contains('control-group', $html);
		Assert::contains('control-label', $html);
		Assert::contains('controls', $html);
	}
}


abstract class BootstrapContainerTestCase extends \Tester\TestCase
{
	use BootstrapFormRendererTestHelpers;

	/**
	 * @var \Nette\DI\Container
	 */
	protected $container;


	public function setUp()
	{
		$this->container = $this->createContainer();
	}


	/**
	 * @return \Nette\DI\Container
	 */
	protected function createContainer()
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
					->setFactory('Nette\\Caching\\Storages\\FileJournal', array(TEMP_DIR . '/cache'));
			}
		};

		RendererExtension::register($config);
		return $config->createContainer();
	}
}

