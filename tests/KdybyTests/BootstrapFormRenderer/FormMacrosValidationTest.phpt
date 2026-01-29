<?php
// Copyright (c) 2026 Jozef Izso
// Licensed under terms in license.md file.
// SPDX-License-Identifier: BSD-3-Clause OR GPL-2.0 OR GPL-3.0

/**
 * Test: Kdyby\BootstrapFormRenderer\Latte\FormMacros - compile-time validation.
 *
 * @testCase KdybyTests\BootstrapFormRenderer\FormMacrosValidationTest
 * @package Kdyby\BootstrapFormRenderer
 */

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\Latte\FormMacros;
use Latte\CompileException;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use Nette\Bridges\ApplicationLatte\UIMacros;
use Nette\Bridges\FormsLatte\FormMacros as NetteFormMacros;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';


/**
 * Tests for FormMacros compile-time validation.
 */
class FormMacrosValidationTest extends TestCase
{

	/**
	 * @param string $template
	 * @return string Compiled PHP code
	 */
	private function compile($template)
	{
		$engine = new Engine();
		$engine->setLoader(new StringLoader());
		$engine->onCompile[] = function (Engine $engine) {
			UIMacros::install($engine->getCompiler());
			NetteFormMacros::install($engine->getCompiler());
			FormMacros::install($engine->getCompiler());
		};
		return $engine->compile($template);
	}



	/**
	 * Test that {form} without name throws CompileException
	 */
	public function testFormWithoutNameThrowsException()
	{
		Assert::exception(function () {
			$this->compile('{form}{/form}');
		}, CompileException::class, '#^(Missing form name in \\{form\\}\\.|Invalid content of tag)$#');
	}


	/**
	 * Test that {pair} without name throws CompileException
	 */
	public function testPairWithoutNameThrowsException()
	{
		Assert::exception(function () {
			$this->compile('{form myForm}{pair}{/form}');
		}, CompileException::class, '#^(Missing name in \\{pair\\}\\.|Invalid content of tag)$#');
	}


	/**
	 * Test that {group} without name throws CompileException
	 */
	public function testGroupWithoutNameThrowsException()
	{
		Assert::exception(function () {
			$this->compile('{form myForm}{group}{/form}');
		}, CompileException::class, '#^(Missing name in \\{group\\}\\.|Invalid content of tag)$#');
	}


	/**
	 * Test that {container} without name throws CompileException
	 */
	public function testContainerWithoutNameThrowsException()
	{
		Assert::exception(function () {
			$this->compile('{form myForm}{container}{/form}');
		}, CompileException::class, '#^(Missing name in \\{container\\}\\.|Invalid content of tag)$#');
	}


	/**
	 * Test that using {form} as an n: attribute throws CompileException
	 */
	public function testFormAsAttributeThrowsException()
	{
		Assert::exception(function () {
			$this->compile('<form n:form="myForm"></form>');
		}, CompileException::class, 'Did you mean <form n:name=...> ?');
	}


	/**
	 * Test that {form body} can be used inside <form n:name="..."> element
	 */
	public function testFormBodyInsideNamedFormCompiles()
	{
		$compiled = $this->compile('<form n:name="myForm">{form body}</form>');
		Assert::type('string', $compiled);
		Assert::contains('renderFormPart', $compiled);
	}


	/**
	 * Test that {form name} inside a literal <form> element throws CompileException
	 */
	public function testFormInsideFormElementThrowsException()
	{
		Assert::exception(function () {
			$this->compile('<form>{form myForm}{/form}</form>');
		}, CompileException::class, 'Cannot render {form} inside an existing <form> element.');
	}


	/**
	 * Test that inline form parts can be used inside <form n:name="..."> element
	 */
	public function testFormInlinePartsInsideNamedFormCompile()
	{
		$compiled = $this->compile('<form n:name="myForm">{form errors}{form controls}{form buttons}</form>');
		Assert::type('string', $compiled);
		Assert::contains('renderFormPart', $compiled);
	}

}


$testCase = new FormMacrosValidationTest();
$testCase->run();
