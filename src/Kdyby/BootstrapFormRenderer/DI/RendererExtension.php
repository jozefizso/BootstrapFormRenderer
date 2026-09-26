<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

declare(strict_types=1);

namespace Kdyby\BootstrapFormRenderer\DI;

use Kdyby;
use Nette;
use Nette\DI\Compiler;
use Nette\DI\Definitions\Statement;

/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class RendererExtension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration(): void
	{
		$this->getContainerBuilder()->addDefinition($this->prefix('bootstrap2FormFactory'))
			->setType(Kdyby\BootstrapFormRenderer\Bootstrap2FormFactory::class);
	}



	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		// LatteExtension already adds the core FormsExtension; our {form} tags must be added after it.
		$latteFactory = $builder->getByType(Nette\Bridges\ApplicationLatte\LatteFactory::class);
		if ($latteFactory === NULL) {
			throw new Nette\InvalidStateException('BootstrapFormRenderer requires nette/application LatteExtension to be registered.');
		}

		/** @var Nette\DI\Definitions\FactoryDefinition $definition */
		$definition = $builder->getDefinition($latteFactory);
		$definition->getResultDefinition()
			->addSetup('addExtension', [new Statement(Kdyby\BootstrapFormRenderer\Latte\FormsExtension::class)]);

	}



	public static function register(Nette\Bootstrap\Configurator $config): void
	{
		$config->onCompile[] = function (Nette\Bootstrap\Configurator $config, Compiler $compiler): void {
			$compiler->addExtension('twBootstrapRenderer', new RendererExtension());
		};
	}

}
