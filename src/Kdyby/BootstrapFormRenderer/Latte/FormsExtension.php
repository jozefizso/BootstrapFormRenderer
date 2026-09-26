<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

declare(strict_types=1);

namespace Kdyby\BootstrapFormRenderer\Latte;

use Latte;
use Nette\Bridges\FormsLatte\FormsExtension as NetteFormsExtension;


/**
 * Latte 3 tags rendering forms through BootstrapRenderer.
 *
 * <code>
 * {form name} as {$form->render('begin')}
 * {form errors} as {$form->render('errors')}
 * {form body} as {$form->render('body')}
 * {form controls} as {$form->render('controls')}
 * {form buttons} as {$form->render('buttons')}
 * {/form} as {$form->render('end')}
 * {form name /} as {form name}{/form} (begin + hidden fields + end; no body)
 * {pair name} as {$form->render($form['name'])}
 * {group name} as {$form->render($form->getGroup('name'))}
 * {container name} as {$form->render($form['name'])}
 * </code>
 *
 * Must be added after {@see \Nette\Bridges\FormsLatte\FormsExtension}, whose {form} tag it overrides
 * and whose forms runtime provider, {input} and {label} tags it relies on.
 */
final class FormsExtension extends Latte\Extension
{

	public function getTags(): array
	{
		return [
			'form' => Nodes\FormNode::create(...),
			'pair' => Nodes\PairNode::create(...),
			'container' => Nodes\PairNode::create(...),
			'group' => Nodes\GroupNode::create(...),
			'bootstrapFormContext' => Nodes\FormContextNode::create(...),
		];
	}



	/**
	 * Adds the core Nette forms extension when missing, and this extension after it
	 * unless it is already registered later than the core one.
	 */
	public static function install(Latte\Engine $engine): void
	{
		$core = $own = NULL;
		foreach ($engine->getExtensions() as $i => $extension) {
			if ($extension instanceof NetteFormsExtension) {
				$core = $i;
			} elseif ($extension instanceof self) {
				$own = $i;
			}
		}

		if ($core === NULL) {
			$engine->addExtension(new NetteFormsExtension());
			$core = PHP_INT_MAX; // now the last extension
		}
		if ($own === NULL || $own < $core) {
			$engine->addExtension(new self());
		}
	}



	public function getCacheKey(Latte\Engine $engine): mixed
	{
		return 1;
	}

}
