<?php

/**
 * Test: BootstrapRenderer supports Nette Form string conversion.
 *
 * @testCase KdybyTests\BootstrapFormRenderer\BootstrapRendererStringTest
 * @package Kdyby\BootstrapFormRenderer
 */

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Latte\Engine;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Forms\Form;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';


/**
 * Regression test for the FormRenderer return-value contract.
 *
 * Nette calls FormRenderer::render() when a form is converted to a string.
 * BootstrapRenderer must use the Latte template's string-conversion API so the
 * generated HTML is returned without leaking output to the response.
 */
class BootstrapRendererStringTest extends TestCase
{
	public function testRendererReturnsTemplateOutputWithoutWritingIt()
	{
		// Arrange
		$form = new Form();
		$renderer = new BootstrapRenderer(new StringRenderingTemplate(new Engine()));

		// Act
		ob_start();
		$actual = $renderer->render($form);
		$output = ob_get_clean();

		// Assert
		Assert::same(StringRenderingTemplate::HTML, $actual);
		Assert::same('', $output, "BootstrapRenderer->render() must not write any text to the output buffer.");
	}


	public function testFormCanBeRenderedToString()
	{
		// Arrange
		$form = new Form();
		$form->setRenderer(new BootstrapRenderer(new StringRenderingTemplate(new Engine())));

		// Act
		$expectedFormHtml = (string) $form;

		// Assert
		Assert::same(StringRenderingTemplate::HTML, $expectedFormHtml);
	}

	public function testInjectedTemplateIsKeptForPresenterForm()
	{
		$presenter = new RendererStringPresenter();
		$form = new \Nette\Application\UI\Form($presenter, 'form');
		$engine = new Engine();
		$engine->addProvider('uiControl', $presenter);
		$engine->addProvider('uiNonce', 'injected-nonce');
		$renderer = new BootstrapRenderer(new StringRenderingTemplate($engine));

		Assert::same(StringRenderingTemplate::HTML, $renderer->render($form));
		$providers = $engine->getProviders();
		Assert::same($presenter, $providers['uiControl']);
		Assert::same($presenter, $providers['uiPresenter']);
		Assert::same('injected-nonce', $providers['uiNonce']);
	}

	public function testPresenterTemplateSubclassIsKept()
	{
		$presenter = new RendererStringPresenter();
		$form = new \Nette\Application\UI\Form($presenter, 'form');
		$renderer = new BootstrapRenderer();

		Assert::same('presenter-template-state', $renderer->render($form));
	}
}



class RendererStringPresenter extends \Nette\Application\UI\Presenter
{
	protected function createTemplate(?string $class = NULL): \Nette\Application\UI\Template
	{
		return new StringRenderingTemplate(new Engine(), 'presenter-template-state');
	}
}



#[\AllowDynamicProperties]
class StringRenderingTemplate extends Template
{
	const HTML = '<form>template output</form>';

	/** @var string */
	private $html;


	public function __construct(Engine $engine, $html = self::HTML)
	{
		parent::__construct($engine);
		$this->html = $html;
	}


	public function __toString(): string
	{
		return $this->html;
	}
}


$testCase = new BootstrapRendererStringTest();
$testCase->run();
