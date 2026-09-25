<?php

/**
 * Test: {form} in templates created by the Nette 3 application TemplateFactory.
 *
 * @testCase KdybyTests\FormRenderer\ApplicationTemplateFormTest
 * @package Kdyby\BootstrapFormRenderer
 */

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\UI\Control;
use Nette\Forms\Form;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestHelpers.php';


/**
 * Application templates expose the owning control only through the uiControl
 * provider and $control; Nette 3 no longer defines $_control.
 */
class ApplicationTemplateFormTest extends BootstrapContainerTestCase
{

	/**
	 * @return string
	 */
	private function renderControlTemplate(Control $control, $latte)
	{
		/** @var \Nette\Application\UI\ITemplateFactory $templateFactory */
		$templateFactory = $this->container->getByType('Nette\Application\UI\ITemplateFactory');
		$template = $templateFactory->createTemplate($control);
		$template->setFile(FileMock::create($latte, 'latte'));

		return $this->captureOutput(function () use ($template) {
			$template->render();
		});
	}


	public function testNamedBootstrapFormIsResolvedFromControl()
	{
		$control = new ControlMock();
		$control->addComponent($form = new Form(), 'signin');
		$form->setAction('/signin');
		$form->addText('name', 'Name');
		$form->setRenderer(new BootstrapRenderer());

		$html = $this->renderControlTemplate($control, '{form signin class => "form-signin"}{form body}{/form}');

		Assert::match('<form action="/signin" method="post" class="form-signin">%A%name="name"%A%</form>%A?%', $html);
	}


	public function testNamedDefaultRendererFormFiresRenderEvents()
	{
		$control = new ControlMock();
		$control->addComponent($form = new Form(), 'filter');
		$form->setAction('/');
		$form->setMethod('get');
		$form->addText('search', 'Search');
		$rendered = FALSE;
		$form->onRender[] = function (Form $form) use (&$rendered) {
			$rendered = TRUE;
			$form->addHidden('do', 'grid-filter-submit');
		};

		$html = $this->renderControlTemplate($control, '{form filter}{input search}{/form}');

		Assert::true($rendered);
		Assert::match('<form action="/" method="get">%A%name="search"%A%<input type="hidden" name="do" value="grid-filter-submit">%A?%</form>%A?%', $html);
	}
}


run(new ApplicationTemplateFormTest());
