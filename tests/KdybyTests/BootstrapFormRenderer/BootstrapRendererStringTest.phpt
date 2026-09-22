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
 * Regression test for the IFormRenderer return-value contract.
 *
 * Nette calls IFormRenderer::render() when a form is converted to a string.
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
}



class StringRenderingTemplate extends Template
{
	const HTML = '<form>template output</form>';


	public function __toString()
	{
		return self::HTML;
	}
}


$testCase = new BootstrapRendererStringTest();
$testCase->run();
