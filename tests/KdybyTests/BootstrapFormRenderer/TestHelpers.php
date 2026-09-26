<?php

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\DI\RendererExtension;
use Nette\Bootstrap\Configurator;
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
	 * Latte 3 resolves included templates relative to the including file unless the path is absolute,
	 * so stream-wrapper mocks cannot be used as group/control templates.
	 */
	protected function createTemplateFile(string $latte): string
	{
		static $counter = 0;
		$file = TEMP_DIR . '/template-' . (++$counter) . '.latte';
		file_put_contents($file, $latte);
		return $file;
	}


	/**
	 * nette/forms 3.0.7 (and 2.x) appends an IE-only hidden input to </form>; newer releases do not.
	 *
	 * @param string $html
	 * @return string
	 */
	protected function stripLegacyIeHack($html)
	{
		return preg_replace('#<!--\[if IE\]>\s*<input type=IEbug disabled style="display:none">\s*<!\[endif\]-->#', '', $html);
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


/**
 * Presenter without a template factory; HTTP services are injected like in an application.
 */
class PresenterMock extends \Nette\Application\UI\Presenter
{
	public function __construct()
	{
		$this->injectPrimary(new \Nette\Http\Request(new \Nette\Http\UrlScript('http://localhost/')), new \Nette\Http\Response());
	}
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

		return $config->createContainer();
	}
}
