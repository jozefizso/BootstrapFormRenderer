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
		$builder = $this->getContainerBuilder();
		$engine = $builder->getDefinition('nette.latte');

		$install = 'Kdyby\BootstrapFormRenderer\Latte\FormMacros::install';
		$engine->addSetup($install . '(?->getCompiler())', array('@self'));

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
