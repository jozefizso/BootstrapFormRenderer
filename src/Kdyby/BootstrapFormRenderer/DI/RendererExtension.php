<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\BootstrapFormRenderer\DI;

use Kdyby;
use Nette\DI\Compiler;
use Nette;

/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class RendererExtension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$this->getContainerBuilder()->addDefinition($this->prefix('bootstrap2FormFactory'))
			->setType(Kdyby\BootstrapFormRenderer\Bootstrap2FormFactory::class);
	}



	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// LatteExtension already installs UI and form macros; our {form} overrides must be installed last.
		$latteFactory = $builder->getByType(Nette\Bridges\ApplicationLatte\ILatteFactory::class);
		if ($latteFactory === NULL) {
			throw new Nette\InvalidStateException('BootstrapFormRenderer requires nette/application LatteExtension to be registered.');
		}

		$builder->getDefinition($latteFactory)->getResultDefinition()
			->addSetup('?->onCompile[] = function ($engine) { Kdyby\BootstrapFormRenderer\Latte\FormMacros::install($engine->getCompiler()); }', array('@self'));
	}



	/**
	 * @param \Nette\Configurator $config
	 */
	public static function register(Nette\Configurator $config)
	{
		$config->onCompile[] = function (Nette\Configurator $config, Compiler $compiler) {
			$compiler->addExtension('twBootstrapRenderer', new RendererExtension());
		};
	}

}
