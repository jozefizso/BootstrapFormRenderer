<?php

/**
 * Test: Submit/button id attributes behavior.
 *
 * @testCase KdybyTests\BootstrapFormRenderer\SubmitButtonIdTest
 * @package Kdyby\BootstrapFormRenderer
 */

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Latte\Engine;
use Nette;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Forms\Form;
use Nette\Utils\Strings;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestHelpers.php';


class SubmitButtonIdTest extends TestCase
{
	use BootstrapFormRendererTestHelpers;

	public function testAnonymousFormWithButtonAndSubmit()
	{
		$form = new Form();
		$form->addButton('btn', 'Btn');
		$form->addSubmit('send', 'Send');
		$form->setRenderer(new BootstrapRenderer());

		$this->warmupRenderer($form);

		$this->assertTemplateOutput(
			$form,
			__DIR__ . '/submit-button-id/input/anonymous.latte',
			__DIR__ . '/submit-button-id/output/anonymous.html'
		);
	}


	public function testNamedFormWithButtonAndSubmit()
	{
		$form = new Form();
		$form->addButton('btn', 'Btn');
		$form->addSubmit('send', 'Send');

		$control = new ControlMock();
		$control['foo'] = $form;

		$form->setRenderer(new BootstrapRenderer());

		$this->warmupRenderer($form);

		$this->assertTemplateOutput(
			$form,
			__DIR__ . '/submit-button-id/input/named.latte',
			__DIR__ . '/submit-button-id/output/named.html'
		);
	}


	public function testAnonymousFormWithExplicitSubmitId()
	{
		$form = new Form();
		$form->addSubmit('send', 'Send')
			->setAttribute('id', 'custom-id');
		$form->setRenderer(new BootstrapRenderer());

		$this->warmupRenderer($form);

		$this->assertTemplateOutput(
			$form,
			__DIR__ . '/submit-button-id/input/explicit-id.latte',
			__DIR__ . '/submit-button-id/output/explicit-id.html'
		);
	}


	/**
	 * @param \Nette\Forms\Form $form
	 * @return void
	 */
	private function warmupRenderer(Form $form)
	{
		// Ensure BootstrapRenderer prepares controls before we read their HTML.
		$this->captureOutput(function () use ($form) {
			$form->render();
		});
	}


	/**
	 * @param \Nette\Forms\Form $form
	 * @param string $latteFile
	 * @param string $expectedOutput
	 * @return void
	 */
	private function assertTemplateOutput(Form $form, $latteFile, $expectedOutput)
	{
		$template = $this->createTemplate()
			->setFile($latteFile)
			->setParameters(array('form' => $form));

		$actual = Strings::normalize($this->captureOutput(function () use ($template) {
			$template->render();
		}));
		$expected = Strings::normalize(file_get_contents($expectedOutput));
		Assert::same($expected, $actual);
	}


	/**
	 * @return \Nette\Bridges\ApplicationLatte\Template
	 */
	private function createTemplate()
	{
		$engine = new Engine();
		$engine->setTempDirectory(TEMP_DIR . '/latte');
		return new Template($engine);
	}
}


class ControlMock extends Nette\Application\UI\Control
{
}

run(new SubmitButtonIdTest());
