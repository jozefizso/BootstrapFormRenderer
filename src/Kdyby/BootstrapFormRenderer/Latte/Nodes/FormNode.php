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
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\Position;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Compiler\Token;
use Nette\Bridges\FormsLatte\Nodes\FormNode as CoreFormNode;


/**
 * {form name [, attributes]} ... {/form}
 * {form name /}
 * {form errors|body|controls|buttons [, args]}
 * {form scope name}...{/form} and {form detached name, attrs}...{/form} use the nette/forms core node
 */
final class FormNode extends StatementNode
{
	private const INLINE_PARTS = ['errors', 'body', 'controls', 'buttons'];
	private const CORE_MODES = ['scope', 'detached'];

	public ExpressionNode $name;
	public ArrayNode $attributes;
	public ?AreaNode $content = NULL;
	public ?Position $endLine = NULL;



	/**
	 * @return static|\Generator<int, ?list<string>, array{AreaNode, ?Tag}, static>
	 */
	public static function create(Tag $tag): self|\Generator
	{
		if ($tag->isNAttribute()) {
			throw new CompileException('Did you mean <form n:name=...> ?', $tag->position);
		}
		if ($tag->parser->isEnd()) {
			throw new CompileException("Missing form name in {{$tag->name}}.", $tag->position);
		}
		if (self::isCoreMode($tag)) {
			// {form scope ...} and {form detached ...} print no Bootstrap markup; keep nette/forms semantics.
			return CoreFormNode::create($tag);
		}

		$node = new self;
		$node->position = $tag->position;
		$node->name = $tag->parser->parseUnquotedStringOrExpression();
		$tag->parser->stream->tryConsume(',');
		$node->attributes = $tag->parser->parseArguments();

		if ($node->name instanceof StringNode && in_array($node->name->value, self::INLINE_PARTS, TRUE)) {
			return $node;
		}

		if ($tag->htmlElement && strtolower($tag->htmlElement->name) === 'form') {
			throw new CompileException("Cannot render {{$tag->name}} inside an existing <form> element.", $tag->position);
		}

		return self::createPaired($tag, $node);
	}



	/**
	 * `scope`/`detached` is a mode only when a form name follows; `{form scope}` alone names a form "scope".
	 */
	private static function isCoreMode(Tag $tag): bool
	{
		$stream = $tag->parser->stream;
		if (!in_array($stream->peek()->text, self::CORE_MODES, TRUE)) {
			return FALSE;
		}

		$offset = 1;
		while (($next = $stream->tryPeek($offset)) && $next->is(Token::Php_Whitespace)) {
			$offset++;
		}
		return $next !== NULL && !$next->isEnd() && $next->text !== ',';
	}



	/**
	 * @return \Generator<int, ?list<string>, array{AreaNode, ?Tag}, static>
	 */
	private static function createPaired(Tag $tag, self $node): \Generator
	{
		$tag->outputMode = $tag::OutputKeepIndentation;
		$tag->node = $node;

		[$node->content, $endTag] = yield;
		$node->endLine = $endTag?->position;
		if ($endTag && $node->name instanceof StringNode) {
			$endTag->parser->stream->tryConsume($node->name->value);
		}

		return $node;
	}



	public function print(PrintContext $context): string
	{
		if ($this->content === NULL) {
			return $context->format(
				'$this->global->forms->getScope()->render(%node, %node) %line;',
				$this->name,
				$this->attributes,
				$this->position,
			);
		}

		return $context->format(
			'$this->global->forms->begin($form = Kdyby\BootstrapFormRenderer\Latte\Runtime::resolveForm(%node, $this->global), global: $this->global) %line;'
			. 'echo Kdyby\BootstrapFormRenderer\Latte\Runtime::renderBegin($form, %node, $this->global) %1.line;'
			. ' %3.node '
			. 'echo $this->global->forms->renderFormEnd() %4.line;'
			. '$this->global->forms->end();' . "\n\n",
			$this->name,
			$this->position,
			$this->attributes,
			$this->content,
			$this->endLine,
		);
	}



	public function &getIterator(): \Generator
	{
		yield $this->name;
		yield $this->attributes;
		if ($this->content) {
			yield $this->content;
		}
	}

}
