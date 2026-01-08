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
use Nette\Latte\CompileException;
use Nette\Latte\Engine;
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
		FormMacros::install($engine->getCompiler());
		return $engine->getCompiler()->compile($engine->getParser()->parse($template));
	}



	/**
	 * Test that {form} without name throws CompileException
	 */
	public function testFormWithoutNameThrowsException()
	{
		Assert::exception(function () {
			$this->compile('{form}{/form}');
		}, CompileException::class, 'Missing form name in {form}.');
	}


	/**
	 * Test that {pair} without name throws CompileException
	 */
	public function testPairWithoutNameThrowsException()
	{
		Assert::exception(function () {
			$this->compile('{form myForm}{pair}{/form}');
		}, CompileException::class, 'Missing name in {pair}.');
	}


	/**
	 * Test that {group} without name throws CompileException
	 */
	public function testGroupWithoutNameThrowsException()
	{
		Assert::exception(function () {
			$this->compile('{form myForm}{group}{/form}');
		}, CompileException::class, 'Missing name in {group}.');
	}


	/**
	 * Test that {container} without name throws CompileException
	 */
	public function testContainerWithoutNameThrowsException()
	{
		Assert::exception(function () {
			$this->compile('{form myForm}{container}{/form}');
		}, CompileException::class, 'Missing name in {container}.');
	}


	/**
	 * Test that {form} inside <form> element throws CompileException
	 */
	public function testFormInsideFormElementThrowsException()
	{
		Assert::exception(function () {
			$this->compile('<form>{form myForm}{/form}</form>');
		}, CompileException::class, 'Did you mean <form n:name=...> ?');
	}

}


$testCase = new FormMacrosValidationTest();
$testCase->run();
