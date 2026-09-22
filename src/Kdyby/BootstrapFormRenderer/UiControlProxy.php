<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\BootstrapFormRenderer;

use Nette\Application\UI\Presenter;

/**
 * Proxy for Latte UI macros provider `uiControl`.
 *
 * Nette UI runtime auto-layout kicks in when `uiControl` is a Presenter and the
 * rendered template defines blocks. Internal renderer templates (`@form.latte`,
 * `@parts.latte`) define blocks, but must not auto-extend presenter layouts.
 *
 * We keep `link` / `{control ...}` support by delegating to the presenter, while
 * avoiding the `instanceof Presenter` check.
 */
class UiControlProxy implements \ArrayAccess
{
	/** @var Presenter */
	private $presenter;


	public function __construct(Presenter $presenter)
	{
		$this->presenter = $presenter;
	}


	public function link($destination, $args = [])
	{
		return $this->presenter->link($destination, $args);
	}


	public function getComponent($name, $throw = true)
	{
		return $this->presenter->getComponent($name, $throw);
	}


	public function offsetExists($offset)
	{
		return $this->presenter->offsetExists($offset);
	}


	public function offsetGet($offset)
	{
		return $this->presenter->offsetGet($offset);
	}


	public function offsetSet($offset, $value)
	{
		$this->presenter->offsetSet($offset, $value);
	}


	public function offsetUnset($offset)
	{
		$this->presenter->offsetUnset($offset);
	}


	public function __call($name, array $args)
	{
		return call_user_func_array([$this->presenter, $name], $args);
	}
}

