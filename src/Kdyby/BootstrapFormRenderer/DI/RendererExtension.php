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



if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
}

if (isset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']) || !class_exists('Nette\Configurator')) {
	unset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']); // fuck you
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

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
				$engine->addSetup('?->onCompile[] = function ($engine) { ?::install($engine->getCompiler()); }', array(
					'@self',
					'Nette\Bridges\ApplicationLatte\UIMacros',
				));
				$engine->addSetup('?->onCompile[] = function ($engine) { ?::install($engine->getCompiler()); }', array(
					'@self',
					'Nette\Bridges\FormsLatte\FormMacros',
				));
				$engine->addSetup('?->onCompile[] = function ($engine) { ?::install($engine->getCompiler()); }', array(
					'@self',
					'Kdyby\BootstrapFormRenderer\Latte\FormMacros',
				));
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
