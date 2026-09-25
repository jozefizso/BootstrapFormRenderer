<?php

/**
 * Test: custom templates resolve {form name} through the control that owns the rendered form.
 *
 * @testCase KdybyTests\FormRenderer\ChildControlFormTest
 * @package Kdyby\BootstrapFormRenderer
 */

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Tester\Assert;
use Tester\FileMock;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestHelpers.php';


class ChildControlFormTest extends TestCase
{
	use BootstrapFormRendererTestHelpers;


	/**
	 * @return Form[]
	 */
	private function createForms(Control $owner)
	{
		$main = new Form();
		$owner->addComponent($main, 'main');
		$main->setAction('/main');
		$main->setRenderer(new BootstrapRenderer());

		$sibling = new Form();
		$owner->addComponent($sibling, 'sibling');
		$sibling->setAction('/sibling');
		$sibling->addHidden('token', 'sibling-token');

		$main->addText('name', 'Name')
			->setOption('template', FileMock::create('{form sibling}{/form}', 'latte'));

		return array($main, $sibling);
	}


	public function testSiblingFormOfChildControlIsResolvedInControlTemplate()
	{
		$presenter = new PresenterMock();
		$child = new ControlMock();
		$presenter->addComponent($child, 'box');
		list($main) = $this->createForms($child);

		$html = $this->captureOutput(function () use ($main) {
			$main->render('body');
		});

		Assert::contains('action="/sibling"', $html);
		Assert::contains('value="sibling-token"', $html);
	}


	public function testSiblingFormOfPresenterIsResolvedInControlTemplate()
	{
		$presenter = new PresenterMock();
		list($main) = $this->createForms($presenter);

		$html = $this->captureOutput(function () use ($main) {
			$main->render('body');
		});

		Assert::contains('action="/sibling"', $html);
		Assert::contains('value="sibling-token"', $html);
	}


	public function testTemplateKeepsPresenterWhenFormBelongsToChildControl()
	{
		$presenter = new PresenterMock();
		$child = new ControlMock();
		$presenter->addComponent($child, 'box');
		list($main) = $this->createForms($child);
		$main['name']->setOption('template', FileMock::create(
			'{get_class($this->global->uiControl)}|{get_class($presenter)}',
			'latte'
		));

		$html = $this->captureOutput(function () use ($main) {
			$main->render('body');
		});

		Assert::contains('KdybyTests\FormRenderer\ControlMock|KdybyTests\FormRenderer\PresenterMock', $html);
	}
}


run(new ChildControlFormTest());
