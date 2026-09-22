<?php
// Copyright (c) 2026 Jozef Izso
// Licensed under terms in license.md file.
// SPDX-License-Identifier: BSD-3-Clause OR GPL-2.0 OR GPL-3.0

/**
 * Test: Kdyby\BootstrapFormRenderer\UiControlProxy.
 *
 * @testCase KdybyTests\BootstrapFormRenderer\UiControlProxyTest
 * @package Kdyby\BootstrapFormRenderer
 */

namespace KdybyTests\FormRenderer;

use Kdyby\BootstrapFormRenderer\UiControlProxy;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';


class UiControlProxyPresenterMock extends \Nette\Application\UI\Presenter
{
}


class UiControlProxyControlMock extends \Nette\Application\UI\Control
{
}


class UiControlProxyTest extends TestCase
{
	public function testSupportsArrayAccess()
	{
		$presenter = new UiControlProxyPresenterMock();
		$child = new UiControlProxyControlMock();
		$proxy = new UiControlProxy($presenter);

		$proxy['child'] = $child;

		Assert::true(isset($proxy['child']));
		Assert::same($child, $proxy['child']);

		unset($proxy['child']);
		Assert::false(isset($proxy['child']));
	}
}


run(new UiControlProxyTest());
