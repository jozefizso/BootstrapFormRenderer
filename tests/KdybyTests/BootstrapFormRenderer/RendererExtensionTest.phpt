<?php

/**
 * Test: Kdyby\BootstrapFormRenderer\DI\RendererExtension.
 *
 * @testCase KdybyTests\BootstrapFormRenderer\RendererExtensionTest
 * @package Kdyby\BootstrapFormRenderer
 */

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\DI\RendererExtension;
use Nette;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';


class RendererExtensionTest extends TestCase
{
	public function testRequiresLatteExtension()
	{
		$compiler = new Nette\DI\Compiler();
		$compiler->addExtension('twBootstrapRenderer', new RendererExtension());

		Assert::exception(function () use ($compiler) {
			$compiler->compile();
		}, 'Nette\InvalidStateException', 'BootstrapFormRenderer requires nette/application LatteExtension to be registered.');
	}
}

run(new RendererExtensionTest());
