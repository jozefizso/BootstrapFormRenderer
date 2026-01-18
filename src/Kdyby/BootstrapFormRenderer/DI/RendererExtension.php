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

		// Install macros on the Latte engine produced by `nette.latteFactory`.
		//
		// Nette 2.2 installs UI/form macros automatically only when templates are created via TemplateFactory.
		// If the app uses the deprecated `nette.template` service (Nette\Templating\FileTemplate), these macros
		// must be installed on the engine itself, otherwise `{control}` / `{form}` / `{input}` won't compile.
		//
		// We install Nette macros first, then our `{form ...}` overrides last (to keep `{input}` / `{label}` from Nette).
		foreach (array('nette.latteFactory', 'nette.latte') as $serviceName) {
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
