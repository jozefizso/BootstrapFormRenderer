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

use Latte\CompileException;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {pair name [, args]}
 * {container name [, args]}
 * Renders a part of the current form through its renderer.
 */
final class PairNode extends StatementNode
{
	public ExpressionNode $name;
	public ArrayNode $args;



	public static function create(Tag $tag): self
	{
		if ($tag->parser->isEnd()) {
			throw new CompileException("Missing name in {{$tag->name}}.", $tag->position);
		}

		$node = new self;
		$node->name = $tag->parser->parseUnquotedStringOrExpression();
		$tag->parser->stream->tryConsume(',');
		$node->args = $tag->parser->parseArguments();
		return $node;
	}



	public function print(PrintContext $context): string
	{
		return $context->format(
			'end($this->global->formsStack)->render(end($this->global->formsStack)[%node], %node) %line;',
			$this->name,
			$this->args,
			$this->position,
		);
	}



	public function &getIterator(): \Generator
	{
		yield $this->name;
		yield $this->args;
	}

}
