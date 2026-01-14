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

		// Install macros on both nette.latte (deprecated) and nette.latteFactory (Nette 2.2)
		// This ensures macros work regardless of which path is used for template creation
		foreach (array('nette.latte', 'nette.latteFactory') as $serviceName) {
			if ($builder->hasDefinition($serviceName)) {
				$engine = $builder->getDefinition($serviceName);
				$engine->addSetup('?->onCompile[] = function ($engine) { Nette\Bridges\ApplicationLatte\UIMacros::install($engine->getCompiler()); }', array('@self'));
				$engine->addSetup('?->onCompile[] = function ($engine) { Nette\Bridges\FormsLatte\FormMacros::install($engine->getCompiler()); }', array('@self'));
				$engine->addSetup('?->onCompile[] = function ($engine) { Kdyby\BootstrapFormRenderer\Latte\FormMacros::install($engine->getCompiler()); }', array('@self'));
			}
		}

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
