<?php

/**
 * Test: Kdyby\BootstrapFormRenderer\BootstrapRenderer - fallback template/Latte setup.
 *
 * @testCase KdybyTests\BootstrapFormRenderer\BootstrapRendererFallbackTest
 * @package Kdyby\BootstrapFormRenderer
 */

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Forms\Form;
use Nette\Utils\Strings;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestHelpers.php';


/**
 * Ensures BootstrapRenderer can render without presenter/template injection.
 */
class BootstrapRendererFallbackTest extends TestCase
{
	use BootstrapFormRendererTestHelpers;

	public function testRenderOutsidePresenterUsesFallbackLatte()
	{
		$form = new Form();
		$form->addText('email', 'Email');
		$form->addSubmit('send', 'Send');

		$form->setRenderer(new BootstrapRenderer());

		$actual = $this->captureOutput(function () use ($form) {
			$form->render();
		});

		$expected = file_get_contents(__DIR__ . '/fallback/output/basic.html');
		Assert::same(Strings::normalize($expected), Strings::normalize($actual));
	}
}


run(new BootstrapRendererFallbackTest());
