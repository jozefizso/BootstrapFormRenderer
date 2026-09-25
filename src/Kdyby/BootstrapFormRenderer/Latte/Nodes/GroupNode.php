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
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {group name}
 * Renders a part of the current form through its renderer.
 */
final class GroupNode extends StatementNode
{
	public ExpressionNode $name;



	public static function create(Tag $tag): self
	{
		if ($tag->parser->isEnd()) {
			throw new CompileException("Missing name in {{$tag->name}}.", $tag->position);
		}

		$node = new self;
		$node->name = $tag->parser->parseUnquotedStringOrExpression();
		return $node;
	}



	public function print(PrintContext $context): string
	{
		return $context->format(
			'$ʟ_f = end($this->global->formsStack); $ʟ_f->render(is_object($ʟ_g = %node) ? $ʟ_g : $ʟ_f->getGroup($ʟ_g)) %line;',
			$this->name,
			$this->position,
		);
	}



	public function &getIterator(): \Generator
	{
		yield $this->name;
	}

}
