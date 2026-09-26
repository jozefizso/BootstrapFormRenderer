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
		$normalize = function ($s) {
			$s = Strings::normalize($s);
			// Latte versions may differ in insignificant blank lines.
			$s = preg_replace("#\\n[ \\t]*\\n([ \\t]*<!--)#", "\n$1", $s);
			$s = preg_replace("#\\n{3,}#", "\n\n", $s);
			return trim($s);
		};
		Assert::same($normalize($expected), $normalize($this->stripLegacyIeHack($actual)));
	}


	public function testBeginAndEndModesMoveGetActionQueryToHiddenFields()
	{
		$form = new Form();
		$form->setMethod('get');
		$form->setAction('/search?q=a%20b&page=2');
		$form->addText('page', 'Page');
		$form->addHidden('token', 'x');
		$form->setRenderer(new BootstrapRenderer());

		Assert::same('<form action="/search" method="get" class="form-horizontal">', $form->getRenderer()->render($form, 'begin'));
		Assert::same(
			'<input type="hidden" name="q" value="a b"><input type="hidden" name="token" id="frm-token" value="x"></form>' . "\n",
			$form->getRenderer()->render($form, 'end')
		);
	}
}


run(new BootstrapRendererFallbackTest());
