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
	 * @return void
	 */
	protected function assertFormUsesBootstrapRenderer(Form $form)
	{
		Assert::type('Kdyby\\BootstrapFormRenderer\\BootstrapRenderer', $form->getRenderer());
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


class PresenterMock extends \Nette\Application\UI\Presenter
{
}


class ControlMock extends \Nette\Application\UI\Control
{
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


		RendererExtension::register($config);

		if (PHP_VERSION_ID < 80000) {
			return $config->createContainer();
		}

		// Nette Utils 2.4 does not recognize PHP 8 qualified-name tokens.
		// Its use-statement cache can therefore miss a class and emit this warning.
		$previousHandler = NULL;
		$previousHandler = set_error_handler(function ($severity, $message, $file, $line, $context = NULL) use (&$previousHandler) {
			$reflectionFile = '/vendor/nette/utils/src/Utils/Reflection.php';
			if (
				$severity === E_WARNING
				&& strpos($message, 'Undefined array key ') === 0
				&& substr(str_replace('\\', '/', $file), -strlen($reflectionFile)) === $reflectionFile
			) {
				return TRUE;
			}

			return $previousHandler
				? call_user_func($previousHandler, $severity, $message, $file, $line, $context)
				: FALSE;
		});

		try {
			return $config->createContainer();
		} finally {
			restore_error_handler();
		}
	}
}
