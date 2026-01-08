<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\BootstrapFormRenderer\DI;

use Nette\DI\Compiler;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class RendererExtension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		// Register onCompile callbacks on Latte factory (Nette 2.2 / Latte 2.2)
		// This installs {control}, base {form}/{input}/{label} and our custom Bootstrap macros.
		$latteFactory = $builder->getDefinition('nette.latteFactory');
		$latteFactory->addSetup('?->onCompile[] = function ($engine) { Nette\Bridges\ApplicationLatte\UIMacros::install($engine->getCompiler()); }', array('@self'));
		$latteFactory->addSetup('?->onCompile[] = function ($engine) { Nette\Bridges\FormsLatte\FormMacros::install($engine->getCompiler()); }', array('@self'));
		$latteFactory->addSetup('?->onCompile[] = function ($engine) { Kdyby\BootstrapFormRenderer\Latte\FormMacros::install($engine->getCompiler()); }', array('@self'));

		// Register Bootstrap2FormFactory for easy form creation with Bootstrap renderer
		$builder->addDefinition($this->prefix('bootstrap2FormFactory'))
			->setClass('Kdyby\BootstrapFormRenderer\Bootstrap2FormFactory');
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
