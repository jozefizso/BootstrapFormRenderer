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
use Latte;
use Latte\CompileException;
use Latte\MacroNode;
use Latte\PhpWriter;
use Nette;
use Nette\Bridges\FormsLatte\Runtime as FormsLatteRuntime;
use Nette\Forms\Form;


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
 * Self-closing form:
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
 * - {@see \Nette\Bridges\FormsLatte\FormMacros} (core form macros)
 * - {@see \Kdyby\BootstrapFormRenderer\BootstrapRenderer} (Bootstrap rendering implementation)
 *
 * @author Filip Procházka <filip@prochazka.su>
 */
class FormMacros extends Latte\Macros\MacroSet
{

	/**
	 * @param \Latte\Compiler $compiler
	 * @return \Latte\Macros\MacroSet|void
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
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @return string
	 * @throws \Latte\CompileException
	 */
	public function macroFormBegin(MacroNode $node, PhpWriter $writer)
	{
		if ($node->prefix) {
			throw new CompileException('Did you mean <form n:name=...> ?');
		}
		$word = $node->tokenizer->fetchWord();
		if ($word === FALSE || $word === NULL) {
			throw new CompileException("Missing form name in {{$node->name}}.");
		}
		$inlineParts = array('errors', 'body', 'controls', 'buttons');
		if ($node->htmlNode && strtolower($node->htmlNode->name) === 'form' && !in_array($word, $inlineParts, TRUE)) {
			throw new CompileException("Cannot render {{$node->name}} inside an existing <form> element.");
		}
		$node->tokenizer->reset();
		$node->empty = in_array($word, $inlineParts, TRUE);

		return $writer->write('$form = $_form = ' . ($node->empty ? '' : '$this->global->formsStack[] = ') . get_called_class() . '::renderFormPart(%node.word, %node.array, get_defined_vars(), isset($this->global->uiControl) ? $this->global->uiControl : NULL)');
	}



	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 */
	public function macroFormEnd(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack))');
	}



	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @throws \Latte\CompileException
	 */
	public function macroPair(MacroNode $node, PhpWriter $writer)
	{
		$name = $node->tokenizer->fetchWord();
		if ($name === FALSE || $name === NULL) {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		$node->tokenizer->reset();
		return $writer->write('$_form->render($_form[%node.word], %node.array)');
	}



	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @throws \Latte\CompileException
	 */
	public function macroGroup(MacroNode $node, PhpWriter $writer)
	{
		$name = $node->tokenizer->fetchWord();
		if ($name === FALSE || $name === NULL) {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		$node->tokenizer->reset();
		return $writer->write('$_form->render(is_object(%node.word) ? %node.word : $_form->getGroup(%node.word))');
	}



	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @throws \Latte\CompileException
	 */
	public function macroContainer(MacroNode $node, PhpWriter $writer)
	{
		$name = $node->tokenizer->fetchWord();
		if ($name === FALSE || $name === NULL) {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		$node->tokenizer->reset();
		return $writer->write('$_form->render($_form[%node.word], %node.array)');
	}



	/**
	 * Resolves a named form through the uiControl Latte provider that Nette 3 application templates
	 * register, falling back to a $_control template variable for templates rendered without one.
	 *
	 * @param string|Form $mode
	 * @param array $args
	 * @param array $scope
	 * @param \Nette\ComponentModel\IContainer|null $uiControl
	 * @throws \Nette\InvalidStateException
	 * @return \Nette\Forms\Form
	 */
	public static function renderFormPart($mode, array $args, array $scope, $uiControl = NULL)
	{
		$control = $uiControl ?: (isset($scope['_control']) ? $scope['_control'] : NULL);

		if ($mode instanceof Form) {
			self::renderFormBegin($mode, $args);
			return $mode;

		} elseif (is_string($mode) && $control && ($form = $control->getComponent($mode, FALSE)) instanceof Form) {
			self::renderFormBegin($form, $args);
			return $form;

		} elseif (isset($scope['_form']) && $scope['_form'] instanceof Form) {
			$scope['_form']->render($mode, $args);

		} else {
			throw new Nette\InvalidStateException('No instanceof Nette\Forms\Form found in local scope. Ensure the template has a uiControl provider or a $_control variable.');
		}

		return $scope['_form'];
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
			echo FormsLatteRuntime::renderFormBegin($form, $args);
		}
	}




}
