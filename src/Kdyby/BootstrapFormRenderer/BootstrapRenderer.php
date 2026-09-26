<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

declare(strict_types=1);

namespace Kdyby\BootstrapFormRenderer;

use Latte\Engine;
use Nette;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\UIExtension;
use Kdyby\BootstrapFormRenderer\Latte\FormsExtension as BootstrapFormsExtension;
use Nette\Forms\Controls;
use Nette\Utils\Html;


/**
 * Created with twitter bootstrap in mind.
 *
 * <code>
 * $form->setRenderer(new Kdyby\BootstrapFormRenderer\BootstrapRenderer);
 * </code>
 *
 * @author Pavel Ptacek
 * @author Filip Procházka
 */
class BootstrapRenderer implements Nette\Forms\FormRenderer
{

	public static $checkboxListClasses = array(
		'Nextras\Forms\Controls\MultiOptionList',
		'Nette\Forms\Controls\CheckboxList',
		'Kdyby\Forms\Controls\CheckboxList',
	);

	/**
	 * Controls how validation errors are displayed:
	 * - TRUE (default): Control errors appear inline next to inputs; only form-level errors show in alerts
	 * - FALSE: All errors (form-level + control errors) appear in alert boxes; no inline errors
	 * @var bool
	 */
	public $errorsAtInputs = TRUE;

	/**
	 * Groups that should be rendered first
	 */
	public $priorGroups = array();

	/**
	 * @var \Nette\Forms\Form
	 */
	private $form;

	/**
	 * @var \Nette\Bridges\ApplicationLatte\Template
	 */
	private $template;

	/** @var \Nette\Application\UI\Control|null */
	private $templateControl;
	/** @var bool */
	private $templateInjected;



	/**
	 * @param \Nette\Bridges\ApplicationLatte\Template $template
	 */
	public function __construct(?Template $template = NULL)
	{
		$this->template = $template;
		$this->templateInjected = $template !== NULL;
	}



	/**
	 * Render the templates
	 *
	 * @param string|\Nette\Forms\Container|\Nette\Forms\ControlGroup|\Nette\Forms\Control|null $mode
	 *   NULL for the whole form, 'begin', 'end', 'errors', 'body', 'controls', 'buttons', or a form part
	 */
	public function render(Nette\Forms\Form $form, string|object|null $mode = NULL, ?array $args = NULL): string
	{
		/** @var \Nette\Application\UI\Presenter|null $presenter */
		$presenter = $form->lookup(Nette\Application\UI\Presenter::class, FALSE);
		// A form owned by a child control must resolve sibling forms through that control.
		/** @var \Nette\Application\UI\Control|null $control */
		$control = $presenter ? $form->lookup(Nette\Application\UI\Control::class, FALSE) : NULL;

		// Keep application Latte configuration for custom group and control templates.
		if ($this->template === NULL || (!$this->templateInjected && $this->templateControl !== $control)) {
			$template = NULL;
			if ($control) {
				try {
					// The clone shares the control's engine, which carries its uiControl/uiPresenter/uiNonce providers.
					$template = clone $control->getTemplate();

				} catch (Nette\InvalidStateException $e) {
					// Only a missing template factory selects the fallback; other template setup errors must surface.
					if ($e->getMessage() !== 'Service TemplateFactory has not been set.') {
						throw $e;
					}
				}
			}

			$this->template = $template ?: new DefaultTemplate($this->createLatteEngine($control));
			$this->templateControl = $control;
		}

		BootstrapFormsExtension::install($this->template->getLatte());

		// Provide conventional Nette template variables for included user templates.
		if ($control) {
			$this->template->control = $control;
			$this->template->presenter = $presenter;
		}

		if ($this->form !== $form) {
			$this->form = $form;

			// controls placeholders & classes
			foreach ($this->form->getControls() as $formControl) {
				$this->prepareControl($formControl);
			}

			$formEl = $form->getElementPrototype();
			if (!($classes = self::getClasses($formEl)) || stripos($classes, 'form-') === FALSE) {
				$formEl->addClass('form-horizontal');
			}

		} elseif ($mode === 'begin') {
			foreach ($this->form->getControls() as $formControl) {
				$formControl->setOption('rendered', FALSE);
			}
		}


		$this->template->mode = NULL;

		$this->template->setFile(__DIR__ . '/@form.latte');
		$this->template->form = $this->form;
		$this->template->renderer = $this;

		if ($mode === NULL) {
			if ($args) {
				$this->form->getElementPrototype()->addAttributes($args);
			}
			return (string) $this->template;

		} elseif ($mode === 'begin') {
			return $this->renderBegin((array) $args);

		} elseif ($mode === 'end') {
			return $this->renderEnd();

		} else {
			$attrs = array('input' => array(), 'label' => array());
			foreach ((array) $args as $key => $val) {
				if (stripos((string) $key, 'input-') === 0) {
					$attrs['input'][substr($key, 6)] = $val;

				} elseif (stripos((string) $key, 'label-') === 0) {
					$attrs['label'][substr($key, 6)] = $val;
				}
			}

			// @parts.latte opens {bootstrapFormContext $form} so {input} resolves without printing <form>.
			$this->template->setFile(__DIR__ . '/@parts.latte');
			$this->template->mode = $mode;
			$this->template->attrs = $attrs;
			return (string) $this->template;
		}
	}



	/**
	 * Opening <form> tag; forms 3.3 no longer renders it without a Latte runtime.
	 * GET forms drop the action query, whose parameters renderEnd() emits as hidden fields.
	 */
	private function renderBegin(array $attrs): string
	{
		$el = $this->form->getElementPrototype();
		$el->action = (string) $el->action;
		$el = clone $el;
		if ($this->form->isMethod('get')) {
			$el->action = preg_replace('~\?[^#]*~', '', (string) $el->action, 1);
		}

		return $el->addAttributes($attrs)->startTag();
	}



	/**
	 * Unrendered hidden fields, the action query of GET forms, and the closing </form> tag.
	 */
	private function renderEnd(): string
	{
		$s = '';
		if ($this->form->isMethod('get')) {
			$query = (string) parse_url((string) $this->form->getElementPrototype()->action, PHP_URL_QUERY);
			foreach (preg_split('#[;&]#', $query, -1, PREG_SPLIT_NO_EMPTY) as $param) {
				$parts = explode('=', $param, 2);
				$name = urldecode($parts[0]);
				$prefix = explode('[', $name, 2)[0];
				if (!isset($this->form[$prefix])) {
					$s .= Html::el('input', ['type' => 'hidden', 'name' => $name, 'value' => urldecode($parts[1] ?? '')]);
				}
			}
		}

		foreach ($this->form->getControls() as $control) {
			if ($control->getOption('type') === 'hidden' && !$control->getOption('rendered')) {
				$s .= $control->getControl();
			}
		}

		return $s . $this->form->getElementPrototype()->endTag() . "\n";
	}



	private function createLatteEngine(?Nette\Application\UI\Control $control): Engine
	{
		// Custom templates keep the application tags ({link}, {control}, n:href) and the control's providers,
		// which {form name} uses for the lookup.
		$engine = new Engine();
		if ($control) {
			$engine->addExtension(new UIExtension($control));
		}
		return $engine;
	}



	/**
	 * @param \Nette\Forms\Controls\BaseControl $control
	 */
	private function prepareControl(Controls\BaseControl $control): void
	{
		$control->setOption('rendered', FALSE);

		if ($control->isRequired()) {
			$control->getLabelPrototype()->addClass('required');
			$control->setOption('required', TRUE);
		}

		$el = $control->getControlPrototype();
		if ($placeholder = $control->getOption('placeholder')) {
			$el->placeholder($placeholder);
		}

		if ($control->controlPrototype->type === 'email'
			&& $control->getOption('input-prepend') === NULL
		) {
			$control->setOption('input-prepend', '@');
		}

		if ($control instanceof Nette\Forms\SubmitterControl) {
			$el->addClass('btn');

		} else {
			if ($control instanceof Controls\Button) {
				$el->addClass('btn');
			}

			$label = $control->labelPrototype;
			if ($control instanceof Controls\Checkbox) {
				$label->addClass('checkbox');

			} elseif (!$control instanceof Controls\RadioList && !self::isCheckboxList($control)) {
				$label->addClass('control-label');
			}

			$control->setOption('pairContainer', $pair = Html::el('div'));
			$pair->id = $control->htmlId . '-pair';
			$pair->addClass('control-group');
			if ($control->getOption('required')) {
				$pair->addClass('required');
			}
			if ($control->errors) {
				$pair->addClass('error');
			}

			if ($prepend = $control->getOption('input-prepend')) {
				$prepend = Html::el('span', array('class' => 'add-on'))
					->{$prepend instanceof Html ? 'add' : 'setText'}($prepend);
				$control->setOption('input-prepend', $prepend);
			}

			if ($append = $control->getOption('input-append')) {
				$append = Html::el('span', array('class' => 'add-on'))
					->{$append instanceof Html ? 'add' : 'setText'}($append);
				$control->setOption('input-append', $append);
			}
		}
	}



	/**
	 * @return array
	 */
	public function findErrors(): array
	{
		// When errorsAtInputs = TRUE (default), show only form-level errors in alerts
		// Control errors will be shown inline next to the inputs
		// When errorsAtInputs = FALSE, show all errors (form + control) in alerts
		$formErrors = $this->errorsAtInputs
			? $this->form->getOwnErrors()
			: $this->form->getErrors();

		if (!$formErrors) {
			return array();
		}

		return $formErrors;
	}



	/**
	 * @throws \RuntimeException
	 * @return object[]
	 */
	public function findGroups(): array
	{
		$formGroups = $visitedGroups = array();
		foreach ($this->priorGroups as $i => $group) {
			if (!$group instanceof Nette\Forms\ControlGroup) {
				if (!$group = $this->form->getGroup($group)) {
					$groupName = (string)$this->priorGroups[$i];
					throw new \RuntimeException("Form has no group $groupName.");
				}
			}

			$visitedGroups[] = $group;
			if ($group = $this->processGroup($group)) {
				$formGroups[] = $group;
			}
		}

		foreach ($this->form->getGroups() as $group) {
			if (!in_array($group, $visitedGroups, TRUE) && ($group = $this->processGroup($group))) {
				$formGroups[] = $group;
			}
		}

		return $formGroups;
	}



	/**
	 * @param \Nette\Forms\Container $container
	 * @param boolean $buttons
	 * @return \Iterator
	 */
	public function findControls(?Nette\Forms\Container $container = NULL, ?bool $buttons = NULL): \Iterator
	{
		$container = $container ? : $this->form;
		return new \CallbackFilterIterator(new \IteratorIterator($container->getControls()), function ($control) use ($buttons) {
			$isButton = $control instanceof Controls\Button || $control instanceof Nette\Forms\SubmitterControl;
			return !$control->getOption('rendered')
				&& !$control instanceof Controls\HiddenField
				&& (($buttons === TRUE && $isButton) || ($buttons === FALSE && !$isButton) || $buttons === NULL);
		});
	}



	/**
	 * @internal
	 * @param \Nette\Forms\ControlGroup $group
	 * @return \stdClass|null
	 */
	public function processGroup(Nette\Forms\ControlGroup $group): ?\stdClass
	{
		if (!$group->getOption('visual') || !$group->getControls()) {
			return NULL;
		}

		$groupLabel = $group->getOption('label');
		$groupDescription = $group->getOption('description');

		// If we have translator, translate!
		if ($translator = $this->form->getTranslator()) {
			if (!$groupLabel instanceof Html) {
				$groupLabel = $translator->translate($groupLabel);
			}
			if (!$groupDescription instanceof Html) {
				$groupDescription = $translator->translate($groupDescription);
			}
		}

		$controls = array_filter($group->getControls(), function (Controls\BaseControl $control) {
			return !$control->getOption('rendered')
				&& !$control instanceof Controls\HiddenField;
		});

		if (!$controls) {
			return NULL; // do not render empty groups
		}

		$groupAttrs = ($group->getOption('container') ?? Html::el())->setName('');
		/** @var Html $groupAttrs */
		$groupAttrs->attrs += array_diff_key($group->getOptions(), array_fill_keys(array(
			'container', 'label', 'description', 'visual', 'template', // these are not attributes
		), NULL));

		// fake group
		return (object)(array(
			'controls' => $controls,
			'label' => $groupLabel,
			'description' => $groupDescription,
			'attrs' => $groupAttrs,
		) + $group->getOptions());
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @return string
	 */
	public static function getControlName(Controls\BaseControl $control): string
	{
		return $control->lookupPath('Nette\Forms\Form');
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @return \Nette\Utils\Html
	 */
	public static function getControlDescription(Controls\BaseControl $control): Html
	{
		if (!$desc = $control->getOption('description')) {
			return Html::el();
		}

		// If we have translator, translate!
		if (!$desc instanceof Html && ($translator = $control->getForm()->getTranslator())) {
			$desc = $translator->translate($desc);
		}

		// create element
		return Html::el('p', array('class' => 'help-block'))
			->{$desc instanceof Html ? 'add' : 'setText'}($desc);
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @return \Nette\Utils\Html
	 */
	public function getControlError(Controls\BaseControl $control): Html
	{
		if (!($errors = $control->getErrors()) || !$this->errorsAtInputs) {
			return Html::el();
		}
		$error = reset($errors);

		// create element
		return Html::el('p', array('class' => 'help-inline'))
			->{$error instanceof Html ? 'add' : 'setText'}($error);
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @return string|null
	 */
	public static function getControlTemplate(Controls\BaseControl $control): ?string
	{
		return $control->getOption('template');
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Control $control
	 * @return bool
	 */
	public static function isButton(Nette\Forms\Control $control): bool
	{
		return $control instanceof Controls\Button;
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Control $control
	 * @return bool
	 */
	public static function isSubmitButton(?Nette\Forms\Control $control = NULL): bool
	{
		return $control instanceof Nette\Forms\SubmitterControl;
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Control $control
	 * @return bool
	 */
	public static function isCheckbox(Nette\Forms\Control $control): bool
	{
		return $control instanceof Controls\Checkbox;
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Control $control
	 * @return bool
	 */
	public static function isRadioList(Nette\Forms\Control $control): bool
	{
		return $control instanceof Controls\RadioList;
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Control $control
	 * @return bool
	 */
	public static function isCheckboxList(Nette\Forms\Control $control): bool
	{
		foreach (static::$checkboxListClasses as $class) {
			if (class_exists($class, FALSE) && $control instanceof $class) {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Controls\RadioList $control
	 * @return object[]
	 */
	public static function getRadioListItems(Controls\RadioList $control): array
	{
		$items = array();
		foreach ($control->items as $key => $value) {
			$el = $control->getControlPart($key);
			if ($el->getName() === 'input') {
				$items[$key] = $radio = (object) array(
					'input' => $el,
					'label' => $cap = $control->getLabelPart($key),
					'caption' => $cap->getText(),
				);

			} else {
				$items[$key] = $radio = (object) array(
					'input' => $el[0],
					'label' => $el[1],
					'caption' => $el[1]->getText(),
				);
			}

			$radio->label->addClass('radio');
			$radio->html = clone $radio->label;
			$radio->html->insert(0, $radio->input);
		}

		return $items;
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @throws \Nette\InvalidArgumentException
	 * @return object[]
	 */
	public static function getCheckboxListItems(Controls\BaseControl $control): array
	{
		$items = array();
		foreach ($control->items as $key => $value) {
			$el = $control->getControlPart($key);
			$items[$key] = $check = (object) array(
				'input'   => $el,
				'label'   => $cap = $control->getLabelPart($key),
				'caption' => $cap->getText(),
			);
			$check->html = clone $check->label;
			$check->html->addClass('checkbox');
			$display = $control->getOption('display') ?? 'inline';
			if ($display == 'inline') {
				$check->html->addClass($display);
			}
			$check->html->insert(0, $check->input);
		}

		return $items;
	}



	/**
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @return \Nette\Utils\Html
	 */
	public static function getLabelBody(Controls\BaseControl $control): Html|string|null
	{
		$label = $control->getLabel();
		return $label;
	}



	/**
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @param string $class
	 * @return bool
	 */
	public static function controlHasClass(Controls\BaseControl $control, string $class): bool
	{
		$classes = explode(' ', self::getClasses($control->controlPrototype));
		return in_array($class, $classes, TRUE);
	}



	/**
	 * @param \Nette\Utils\Html $_this
	 * @param array $attrs
	 * @return \Nette\Utils\Html
	 */
	public static function mergeAttrs(?Html $_this, array $attrs): Html
	{
		if ($_this === NULL) {
			return Html::el();
		}

		$_this->attrs = array_merge_recursive($_this->attrs, $attrs);
		return $_this;
	}



	/**
	 * @param \Nette\Utils\Html $el
	 * @return string
	 */
	private static function getClasses(Html $el): string
	{
		if (is_array($el->class)) {
			$classes = array_filter(array_merge(array_keys($el->class), $el->class), 'is_string');
			return implode(' ', $classes);
		}
		return (string) $el->class;
	}

}
