<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\BootstrapFormRenderer\Latte;
use Kdyby;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette;
use Nette\Forms\Form;
use Nette\Latte;
use Nette\Latte\CompileException;
use Nette\Latte\MacroNode;
use Nette\Latte\PhpWriter;


/**
 * Standard macros:
 * <code>
 * {form name} as {$form->render('begin')}
 * {form errors} as {$form->render('errors')}
 * {form body} as {$form->render('body')}
 * {form controls} as {$form->render('controls')}
 * {form buttons} as {$form->render('buttons')}
 * {/form} as {$form->render('end')}
 * </code>
 *
 * Self-closing form (Latte 2.1 semantics):
 *
 * <code>
 * {form name /} as {form name}{/form} (begin + hidden fields + end; no body)
 * </code>
 *
 * Old macros `input` & `label` are working the same.
 * <code>
 * {input name}
 * {label name /} or {label name}... {/label}
 * </code>
 *
 * Individual rendering:
 * <code>
 * {pair name} as {$form->render($form['name'])}
 * {group name} as {$form->render($form->getGroup('name'))}
 * {container name} as {$form->render($form['name'])}
 * </code>
 *
 * Related:
 * - {@see \Nette\Latte\Macros\FormMacros} (Latte 2.1 core form macros)
 * - {@see \Kdyby\BootstrapFormRenderer\BootstrapRenderer} (Bootstrap rendering implementation)
 *
 * @author Filip Procházka <filip@prochazka.su>
 */
class FormMacros extends Latte\Macros\MacroSet
{

	/**
	 * @param \Nette\Latte\Compiler $compiler
	 * @return \Nette\Latte\Macros\MacroSet|void
	 */
	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('form', array($me, 'macroFormBegin'), array($me, 'macroFormEnd'));
		$me->addMacro('pair', array($me, 'macroPair'));
		$me->addMacro('group', array($me, 'macroGroup'));
		$me->addMacro('container', array($me, 'macroContainer'));
		return $me;
	}






	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 * @return string
	 * @throws \Nette\Latte\CompileException
	 */
	public function macroFormBegin(MacroNode $node, PhpWriter $writer)
	{
		if ($node->htmlNode && strtolower($node->htmlNode->name) === 'form') {
			throw new CompileException('Did you mean <form n:name=...> ?');
		}
		$word = $node->tokenizer->fetchWord();
		if ($word === FALSE) {
			throw new CompileException("Missing form name in {{$node->name}}.");
		}
		$node->tokenizer->reset();
		$node->isEmpty = in_array($word, array('errors', 'body', 'controls', 'buttons'));

		return $writer->write('$form = $__form = $_form = ' . get_called_class() . '::renderFormPart(%node.word, %node.array, get_defined_vars())');
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 */
	public function macroFormEnd(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('Nette\Latte\Macros\FormMacros::renderFormEnd($__form)');
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 * @throws \Nette\Latte\CompileException
	 */
	public function macroPair(MacroNode $node, PhpWriter $writer)
	{
		$name = $node->tokenizer->fetchWord();
		if ($name === FALSE) {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		$node->tokenizer->reset();
		return $writer->write('$__form->render($__form[%node.word], %node.array)');
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 * @throws \Nette\Latte\CompileException
	 */
	public function macroGroup(MacroNode $node, PhpWriter $writer)
	{
		$name = $node->tokenizer->fetchWord();
		if ($name === FALSE) {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		$node->tokenizer->reset();
		return $writer->write('$__form->render(is_object(%node.word) ? %node.word : $__form->getGroup(%node.word))');
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 * @throws \Nette\Latte\CompileException
	 */
	public function macroContainer(MacroNode $node, PhpWriter $writer)
	{
		$name = $node->tokenizer->fetchWord();
		if ($name === FALSE) {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		$node->tokenizer->reset();
		return $writer->write('$__form->render($__form[%node.word], %node.array)');
	}



	/**
	 * @param string $mode
	 * @param array $args
	 * @param array $scope
	 * @throws \Nette\InvalidStateException
	 * @return \Nette\Forms\Form
	 */
	public static function renderFormPart($mode, array $args, array $scope)
	{
		if ($mode instanceof Form) {
			self::renderFormBegin($mode, $args);
			return $mode;

		} elseif (($control = self::scopeVar($scope, 'control')) && ($form = $control->getComponent($mode, FALSE)) instanceof Form) {
			self::renderFormBegin($form, $args);
			return $form;

		} elseif (($form = self::scopeVar($scope, 'form')) instanceof Form) {
			$form->render($mode, $args);

		} else {
			throw new Nette\InvalidStateException('No instanceof Nette\Forms\Form found in local scope.');
		}

		return $form;
	}



	/**
	 * @param Form $form
	 * @param array $args
	 */
	private static function renderFormBegin(Form $form, array $args)
	{
		if ($form->getRenderer() instanceof BootstrapRenderer) {
			$form->render('begin', $args);

		} else {
			Nette\Latte\Macros\FormMacros::renderFormBegin($form, $args);
		}
	}



	/**
	 * @param array $scope
	 * @param string $var
	 * @return mixed|NULL
	 */
	private static function scopeVar(array $scope, $var)
	{
		return isset($scope['__' . $var])
			? $scope['__' . $var]
			: (isset($scope['_' . $var])
				? $scope['_' . $var]
				: (isset($scope[$var]) ? $scope[$var] : NULL));
	}

}
