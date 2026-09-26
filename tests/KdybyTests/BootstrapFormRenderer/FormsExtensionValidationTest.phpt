<?php
// Copyright (c) 2026 Jozef Izso
// Licensed under terms in license.md file.
// SPDX-License-Identifier: BSD-3-Clause OR GPL-2.0 OR GPL-3.0

/**
 * Test: Kdyby\BootstrapFormRenderer\Latte\FormsExtension - compile-time validation.
 *
 * @testCase KdybyTests\BootstrapFormRenderer\FormsExtensionValidationTest
 * @package Kdyby\BootstrapFormRenderer
 */

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\Latte\FormsExtension;
use Latte\CompileException;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use Nette\Bridges\ApplicationLatte\UIExtension;
use Nette\Bridges\FormsLatte\FormsExtension as NetteFormsExtension;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';


/**
 * Tests for FormsExtension compile-time validation.
 */
class FormsExtensionValidationTest extends TestCase
{

	/**
	 * @param string $template
	 * @return string Compiled PHP code
	 */
	private function compile($template)
	{
		$engine = new Engine();
		$engine->setLoader(new StringLoader());
		$engine->addExtension(new UIExtension(NULL));
		$engine->addExtension(new NetteFormsExtension());
		$engine->addExtension(new FormsExtension());
		return $engine->compile($template);
	}



	/**
	 * install() must leave the Bootstrap {form} tag in effect when it has to add the core extension.
	 */
	public function testInstallKeepsBootstrapFormTagAfterAddingCoreExtension()
	{
		$engine = new Engine();
		$engine->setLoader(new StringLoader());
		$engine->addExtension(new FormsExtension());
		FormsExtension::install($engine);

		$compiled = $engine->compile('{form myForm}{/form}');
		Assert::contains('Kdyby\\BootstrapFormRenderer\\Latte\\Runtime::resolveForm', $compiled);
	}



	/**
	 * {form scope} and {form detached} keep the nette/forms 3.3 semantics instead of the Bootstrap {form} tag.
	 */
	public function testCoreFormModesUseNetteFormNode()
	{
		$scope = $this->compile('{form scope myForm}{input name}{/form}');
		Assert::notContains('Kdyby\\BootstrapFormRenderer\\Latte\\Runtime', $scope);
		Assert::notContains('renderFormBegin', $scope);

		$detached = $this->compile('{form detached myForm}{/form}');
		Assert::contains('detached: true', $detached);
		Assert::notContains('Kdyby\\BootstrapFormRenderer\\Latte\\Runtime', $detached);
	}



	/**
	 * Forms named "scope" or "detached" still render through the Bootstrap {form} tag.
	 */
	public function testFormsNamedLikeCoreModesUseBootstrapFormNode()
	{
		foreach (['{form scope}{/form}', '{form detached /}', '{form scope, class: x}{/form}'] as $template) {
			Assert::contains('Kdyby\\BootstrapFormRenderer\\Latte\\Runtime::resolveForm', $this->compile($template), $template);
		}
	}



	/**
	 * Test that {form} without name throws CompileException
	 */
	public function testFormWithoutNameThrowsException()
	{
		Assert::exception(function () {
			$this->compile('{form}{/form}');
		}, CompileException::class, 'Missing form name in {form}%a%');
	}


	/**
	 * Test that {pair} without name throws CompileException
	 */
	public function testPairWithoutNameThrowsException()
	{
		Assert::exception(function () {
			$this->compile('{form myForm}{pair}{/form}');
		}, CompileException::class, 'Missing name in {pair}%a%');
	}


	/**
	 * Test that {group} without name throws CompileException
	 */
	public function testGroupWithoutNameThrowsException()
	{
		Assert::exception(function () {
			$this->compile('{form myForm}{group}{/form}');
		}, CompileException::class, 'Missing name in {group}%a%');
	}


	/**
	 * Test that {container} without name throws CompileException
	 */
	public function testContainerWithoutNameThrowsException()
	{
		Assert::exception(function () {
			$this->compile('{form myForm}{container}{/form}');
		}, CompileException::class, 'Missing name in {container}%a%');
	}


	/**
	 * Test that using {form} as an n: attribute throws CompileException
	 */
	public function testFormAsAttributeThrowsException()
	{
		Assert::exception(function () {
			$this->compile('<form n:form="myForm"></form>');
		}, CompileException::class, 'Did you mean <form n:name=...> ?%a%');
	}


	/**
	 * Test that {form body} can be used inside <form n:name="..."> element
	 */
	public function testFormBodyInsideNamedFormCompiles()
	{
		$compiled = $this->compile('<form n:name="myForm">{form body}</form>');
		Assert::type('string', $compiled);
		Assert::contains("\$this->global->forms->getScope()->render('body', [])", $compiled);
	}


	/**
	 * Test that {form name} inside a literal <form> element throws CompileException
	 */
	public function testFormInsideFormElementThrowsException()
	{
		Assert::exception(function () {
			$this->compile('<form>{form myForm}{/form}</form>');
		}, CompileException::class, 'Cannot render {form} inside an existing <form> element%a%');
	}


	/**
	 * Test that inline form parts can be used inside <form n:name="..."> element
	 */
	public function testFormInlinePartsInsideNamedFormCompile()
	{
		$compiled = $this->compile('<form n:name="myForm">{form errors}{form controls}{form buttons}</form>');
		Assert::type('string', $compiled);
		Assert::contains("\$this->global->forms->getScope()->render('errors', [])", $compiled);
		Assert::contains("\$this->global->forms->getScope()->render('controls', [])", $compiled);
		Assert::contains("\$this->global->forms->getScope()->render('buttons', [])", $compiled);
	}

}


$testCase = new FormsExtensionValidationTest();
$testCase->run();
