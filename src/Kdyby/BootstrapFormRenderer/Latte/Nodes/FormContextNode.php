<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

declare(strict_types=1);

namespace Kdyby\BootstrapFormRenderer\Latte\Nodes;

use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {bootstrapFormContext $form} ... {/bootstrapFormContext}
 *
 * Opens the form scope so {input} and {label} resolve, like the core {formContext},
 * but controls already printed before a partial render stay marked as rendered.
 *
 * @internal used by @parts.latte
 */
final class FormContextNode extends StatementNode
{
	public ExpressionNode $form;
	public AreaNode $content;



	/**
	 * @return \Generator<int, ?list<string>, array{AreaNode, ?Tag}, static>
	 */
	public static function create(Tag $tag): \Generator
	{
		$tag->expectArguments();
		$node = $tag->node = new self;
		$node->form = $tag->parser->parseExpression();
		[$node->content] = yield;
		return $node;
	}



	public function print(PrintContext $context): string
	{
		return $context->format(
			'Kdyby\BootstrapFormRenderer\Latte\Runtime::beginContext(%node, $this->global) %line; try { %node } finally { $this->global->forms->end(); }' . "\n",
			$this->form,
			$this->position,
			$this->content,
		);
	}



	public function &getIterator(): \Generator
	{
		yield $this->form;
		yield $this->content;
	}

}
