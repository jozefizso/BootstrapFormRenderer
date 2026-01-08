<?php

/**
 * Test: Kdyby\BootstrapFormRenderer\BootstrapRenderer.
 *
 * @testCase KdybyTests\BootstrapFormRenderer\BootstrapRendererTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\BootstrapFormRenderer
 */

namespace KdybyTests\FormRenderer;

use Kdyby;
use Kdyby\BootstrapFormRenderer;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Kdyby\BootstrapFormRenderer\DI\RendererExtension;
use Nette;
use Nette\Application\UI\Form;
use Nette\Caching\Storages\PhpFileStorage;
use Nette\Configurator;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class BootstrapRendererTest extends TestCase
{

	/**
	 * @var \Nette\DI\Container
	 */
	protected $container;



	public function setUp()
	{
		$config = new Configurator();
		$config->setTempDirectory(TEMP_DIR);
		$config->addParameters(array('container' => array('class' => 'SystemContainer_' . md5(TEMP_DIR))));
		RendererExtension::register($config);
		$this->container = $config->createContainer();
	}



	/**
	 * @return \Nette\Application\UI\Form
	 */
	private function dataCreateRichForm()
	{
		$form = new Form();
		$form->addError("General failure!");

		$grouped = $form->addContainer('grouped');
		$grouped->currentGroup = $form->addGroup('Skupina', FALSE);
		$grouped->addText('name', 'Jméno')->getLabelPrototype()->addClass('test');
		$grouped->addText('email', 'Email')->setType('email');
		$grouped->addSelect('sex', 'Pohlaví', array(1 => 'Muž', 2 => 'Žena'));
		$grouped->addCheckbox('mailing', 'Zasílat novinky');
		$grouped->addButton('add', 'Přidat');

		$grouped->addSubmit('poke', 'Šťouchnout');
		$grouped->addSubmit('poke2', 'Ještě Šťouchnout')->setAttribute('class', 'btn-success');

		$other = $form->addContainer('other');
		$other->currentGroup = $form->addGroup('Other', FALSE);
		$other->addRadioList('sexy', 'Sexy', array(1 => 'Ano', 2 => 'Ne'));
		$other->addPassword('heslo', 'Heslo')->addError('chybka!');
		$other->addSubmit('pass', "Nastavit heslo")->setAttribute('class', 'btn-warning');

		$form->addUpload('photo', 'Fotka');
		$form->addSubmit('up', 'Nahrát fotku');
		$form->addTextArea('desc', 'Popis');
		$form->addProtection('nemam');
		$form->addSubmit('submit', 'Uložit')->setAttribute('class', 'btn-primary');
		$form->addSubmit('delete', 'Smazat');

		return $form;
	}



	/**
	 * @return array
	 */
	public function dataRenderingBasics()
	{
		return array_map(function ($f) { return array(basename($f)); }, glob(__DIR__ . '/basic/input/*.latte'));
	}



	/**
	 * @dataProvider dataRenderingBasics
	 *
	 * @param string $latteFile
	 */
	public function testRenderingBasics($latteFile)
	{
		$form = $this->dataCreateRichForm();
		$this->assertFormTemplateOutput(__DIR__ . '/basic/input/' . $latteFile, __DIR__ . '/basic/output/' . basename($latteFile, '.latte') . '.html', $form);
	}



	/**
	 * @return array
	 */
	public function dataRenderingComponents()
	{
		return array_map(function ($f) { return array(basename($f)); }, glob(__DIR__ . '/components/input/*.latte'));
	}



	/**
	 * @dataProvider dataRenderingComponents
	 *
	 * @param string $latteFile
	 */
	public function testRenderingComponents($latteFile)
	{
		// create form
		$form = $this->dataCreateRichForm();
		$this->assertFormTemplateOutput(__DIR__ . '/components/input/' . $latteFile, __DIR__ . '/components/output/' . basename($latteFile, '.latte') . '.html', $form);
	}



	/**
	 * @return \Nette\Application\UI\Form
	 */
	private function dataCreateForm()
	{
		$form = new Form;
		$form->addText('name', 'Name');
		$form->addCheckbox('check', 'Indeed');
		$form->addUpload('image', 'Image');
		$form->addRadioList('sex', 'Sex', array(1 => 'Man', 'Woman'));
		$form->addSelect('day', 'Day', array(1 => 'Monday', 'Tuesday'));
		$form->addTextArea('desc', 'Description');
		$form->addText('req', 'Required')->setRequired('This field is required');
		$form->addSubmit('send', 'Odeslat');

		$form->addCheckboxList('checks', 'Regions', array(
			1 => 'Region North',
			2 => 'Region South',
			3 => 'Region West',
		))->setOption('display', 'stacked');
		$form->addCheckboxList('checksInline', 'Regions Inline', array(
			1 => 'Region North',
			2 => 'Region South',
			3 => 'Region West',
		))->setOption('display', 'inline');

		$someGroup = $form->addGroup('Some Group', FALSE)
			->setOption('id', 'nemam')
			->setOption('class', 'beauty')
			->setOption('data-custom', '{"this":"should work too"}');
		$someGroup->add($form->addText('groupedName', 'Name'));

		// the div here and fieldset in template is intentional
		$containerGroup = $form->addGroup('Group with container', FALSE)
			->setOption('container', Html::el('div')->id('mam')->class('yes')->data('magic', 'is real'));
		$containerGroup->add($form->addText('containerGroupedName', 'Name'));

		return $form;
	}



	/**
	 * @return array
	 */
	public function dataRenderingIndividual()
	{
		return array_map(function ($f) { return array(basename($f)); }, glob(__DIR__ . '/individual/input/*.latte'));
	}



	/**
	 * @dataProvider dataRenderingIndividual
	 * @param string $latteFile
	 */
	public function testRenderingIndividual($latteFile)
	{
		$form = $this->dataCreateForm();
		$this->assertFormTemplateOutput(__DIR__ . '/individual/input/' . $latteFile, __DIR__ . '/individual/output/' . basename($latteFile, '.latte') . '.html', $form);
	}



	/**
	 * @return \Nette\Application\UI\Form
	 */
	private function dataCreateMinimalForm()
	{
		$form = new Form;
		$form->addText('name', 'Name');
		$form->addSubmit('send', 'Submit');

		return $form;
	}


	/**
	 * @return array
	 */
	public function dataFormStyling()
	{
		return array_map(function ($f) { return array(basename($f)); }, glob(__DIR__ . '/form-styling/input/*.latte'));
	}



	/**
	 * @dataProvider dataFormStyling
	 * @param string $latteFile
	 */
	public function testFormStyling($latteFile)
	{
		$form = $this->dataCreateMinimalForm();
		$this->assertFormTemplateOutput(__DIR__ . '/form-styling/input/' . $latteFile, __DIR__ . '/form-styling/output/' . basename($latteFile, '.latte') . '.html', $form);
	}



	/**
	 * @return array
	 */
	public function dataRenderingCheckboxList()
	{
		return array_map(function ($f) { return array(basename($f)); }, glob(__DIR__ . '/checkboxlist/input/*.latte'));
	}



	/**
	 * @dataProvider dataRenderingCheckboxList
	 * @param string $latteFile
	 */
	public function testRenderingCheckboxList($latteFile)
	{
		$form = $this->dataCreateForm();
		$this->assertFormTemplateOutput(__DIR__ . '/checkboxlist/input/' . $latteFile, __DIR__ . '/checkboxlist/output/' . basename($latteFile, '.latte') . '.html', $form);
	}



	/**
	 * @return \Nette\Application\UI\Form
	 */
	private function dataCreateInputAddonsForm()
	{
		$form = new Form;
		$form->addText('prepend', 'Prepend')->setOption('input-prepend', '@');
		$form->addText('append', 'Append')->setOption('input-append', '.00');
		$form->addText('both', 'Both')->setOption('input-prepend', '$')->setOption('input-append', '.00');
		$form->addSubmit('send', 'Submit');

		return $form;
	}


	/**
	 * @return array
	 */
	public function dataRenderingInputAddons()
	{
		return array_map(function ($f) { return array(basename($f)); }, glob(__DIR__ . '/input-addons/input/*.latte'));
	}



	/**
	 * @dataProvider dataRenderingInputAddons
	 * @param string $latteFile
	 */
	public function testRenderingInputAddons($latteFile)
	{
		$form = $this->dataCreateInputAddonsForm();
		$this->assertFormTemplateOutput(__DIR__ . '/input-addons/input/' . $latteFile, __DIR__ . '/input-addons/output/' . basename($latteFile, '.latte') . '.html', $form);
	}



	public function testMultipleFormsInTemplate()
	{
		$control = new Nette\ComponentModel\Container();

		$control->addComponent($a = new Form, 'a');
		$a->addText('nemam', 'Nemam');
		$a->setRenderer(new BootstrapRenderer());

		$control->addComponent($b = new Form, 'b');
		$b->addText('mam', 'Mam');
		$b->setRenderer(new BootstrapRenderer($this->createTemplate()));

		$this->assertTemplateOutput(array(
			'control' => $control, '_control' => $control
		), __DIR__ . '/edge/input/multipleFormsInTemplate.latte',
			__DIR__ . '/edge/output/multipleFormsInTemplate.html');

		$this->assertTemplateOutput(array(
				'control' => $control, '_control' => $control
			), __DIR__ . '/edge/input/multipleFormsInTemplate_parts.latte',
			__DIR__ . '/edge/output/multipleFormsInTemplate_parts.html');
	}



	/**
	 * @param bool $withTranslator
	 * @return \Nette\Application\UI\Form
	 */
	private function dataCreatePlaceholderForm($withTranslator = FALSE)
	{
		$form = new Form;
		if ($withTranslator) {
			$form->setTranslator(new DummyTranslator());
		}

		$form->addText('email', 'Email')
			->setType('email')
			->setOption('placeholder', 'Enter your email');
		$form->addText('name', 'Name')
			->setOption('placeholder', 'Enter your name');
		$form->addTextArea('message', 'Message')
			->setOption('placeholder', 'Type your message here');
		$form->addSubmit('send', 'Submit');

		return $form;
	}



	/**
	 * @return array
	 */
	public function dataRenderingPlaceholder()
	{
		return array_map(function ($f) { return array(basename($f)); }, glob(__DIR__ . '/placeholder/input/*.latte'));
	}



	/**
	 * @dataProvider dataRenderingPlaceholder
	 * @param string $latteFile
	 */
	public function testRenderingPlaceholder($latteFile)
	{
		$form = $this->dataCreatePlaceholderForm();
		$this->assertFormTemplateOutput(__DIR__ . '/placeholder/input/' . $latteFile, __DIR__ . '/placeholder/output/' . basename($latteFile, '.latte') . '.html', $form);
	}



	/**
	 * @return array
	 */
	public function dataRenderingPlaceholderWithTranslator()
	{
		return array_map(function ($f) { return array(basename($f)); }, glob(__DIR__ . '/placeholder-translated/input/*.latte'));
	}



	/**
	 * @dataProvider dataRenderingPlaceholderWithTranslator
	 * @param string $latteFile
	 */
	public function testRenderingPlaceholderWithTranslator($latteFile)
	{
		$form = $this->dataCreatePlaceholderForm(TRUE);
		$this->assertFormTemplateOutput(__DIR__ . '/placeholder-translated/input/' . $latteFile, __DIR__ . '/placeholder-translated/output/' . basename($latteFile, '.latte') . '.html', $form);
	}



	/**
	 * @return \Nette\Application\UI\Form
	 */
	private function dataCreateGroupOrderingForm()
	{
		$form = new Form;

		// Create two visible groups with distinct controls
		$groupA = $form->addGroup('Group A', FALSE);
		$groupA->add($form->addText('fieldA', 'Field A'));

		$groupB = $form->addGroup('Group B', FALSE);
		$groupB->add($form->addText('fieldB', 'Field B'));

		$form->addSubmit('send', 'Submit');

		return $form;
	}



	/**
	 * @return array
	 */
	public function dataRenderingGroupOrdering()
	{
		return array_map(function ($f) { return array(basename($f)); }, glob(__DIR__ . '/group-ordering/input/*.latte'));
	}



	/**
	 * @dataProvider dataRenderingGroupOrdering
	 * @param string $latteFile
	 */
	public function testRenderingGroupOrdering($latteFile)
	{
		$form = $this->dataCreateGroupOrderingForm();

		// Set up the renderer with priorGroups before setting it on the form
		$renderer = new BootstrapRenderer($this->createTemplate());
		$renderer->priorGroups = array('Group B');
		$form->setRenderer($renderer);

		// Reset rendered flags
		foreach ($form->getControls() as $control) {
			$control->setOption('rendered', FALSE);
		}

		if (property_exists($form, 'httpRequest')) {
			$form->httpRequest = new Nette\Http\Request(new Nette\Http\UrlScript('http://www.kdyby.org'));
		}

		$control = new ControlMock();
		$control['foo'] = $form;

		$this->assertTemplateOutput(array('form' => $form, '_form' => $form, 'control' => $control, '_control' => $control), __DIR__ . '/group-ordering/input/' . $latteFile, __DIR__ . '/group-ordering/output/' . basename($latteFile, '.latte') . '.html');
	}



	/**
	 * @return \Nette\Application\UI\Form
	 */
	private function dataCreateContactFormWithValidation($withTranslator = FALSE)
	{
		$form = new Form;
		if ($withTranslator) {
			$form->setTranslator(new TranslationTestTranslator());
		}

		// Real-world contact form fields with validation (Nette 2.2 compatible)
		$form->addText('name', 'Full Name')
			->setRequired('Please enter your name')
			->addRule($form::MIN_LENGTH, 'Name must be at least %d characters', 3);

		$form->addText('email', 'Email Address')
			->setType('email')
			->setRequired('Please enter your email')
			->addRule($form::EMAIL, 'Please enter a valid email address');

		$form->addText('phone', 'Phone Number')
			->addRule($form::PATTERN, 'Phone must be in format XXX-XXX-XXXX', '[0-9]{3}-[0-9]{3}-[0-9]{4}');

		$form->addText('age', 'Age')
			->addRule($form::INTEGER, 'Age must be a number')
			->addRule($form::RANGE, 'Age must be between %d and %d', array(18, 100));

		$form->addTextArea('message', 'Message')
			->setRequired('Please enter your message')
			->addRule($form::MIN_LENGTH, 'Message must be at least %d characters', 10);

		$form->addSubmit('send', 'Send Message');

		return $form;
	}

	/**
	 * Test that validation errors with a translator are not double-translated
	 */
	public function testValidationErrorsWithTranslatorNoDoubleTranslation()
	{
		$form = $this->dataCreateContactFormWithValidation(TRUE);

		// Simulate form submission with validation errors
		$form->setValues(array(
			'name' => 'Jo', // Too short - will trigger MIN_LENGTH rule
			'email' => 'invalid-email', // Invalid email - will trigger EMAIL rule
			'phone' => '123456', // Invalid pattern
			'age' => '150', // Out of range
			'message' => 'Short', // Too short
		));

		// Validate to trigger errors
		$form->validate();

		// Check that errors were added
		Assert::true(count($form['name']->getErrors()) > 0);
		Assert::true(count($form['email']->getErrors()) > 0);

		// Get the actual error messages
		$nameErrors = $form['name']->getErrors();
		$emailErrors = $form['email']->getErrors();

		// The error should be translated ONCE by Rules::formatMessage()
		// NOT double-translated by the renderer
		// If double-translated, we'd see [CHÝBA PREKLAD] markers or duplicated Slovak text
		Assert::same('Meno musí mať aspoň 3 znaky', reset($nameErrors));
		Assert::same('Zadajte prosím platnú e-mailovú adresu', reset($emailErrors));
	}

	/**
	 * Test that Html errors are passed through without translation
	 */
	public function testHtmlErrorsWithTranslator()
	{
		$form = new Form;
		$form->setTranslator(new TranslationTestTranslator());

		$form->addText('username', 'Username');

		// Add an Html error manually (this simulates custom validation)
		$htmlError = Html::el('strong')->setText('Username is already taken');
		$form['username']->addError($htmlError);

		// Get the error back
		$errors = $form['username']->getErrors();
		$error = reset($errors);

		// The Html object should be returned as-is, not translated
		Assert::type('Nette\Utils\Html', $error);
		Assert::same('<strong>Username is already taken</strong>', $error->render());
	}

	/**
	 * @return array
	 */
	public function dataRenderingTranslation()
	{
		return array_map(function ($f) { return array(basename($f)); }, glob(__DIR__ . '/translation/input/*.latte'));
	}

	/**
	 * Test rendering forms with translator - ensures no double translation
	 *
	 * @dataProvider dataRenderingTranslation
	 * @param string $latteFile
	 */
	public function testRenderingTranslation($latteFile)
	{
		$form = $this->dataCreateContactFormWithValidation(TRUE);

		// Trigger validation errors
		$form->setValues(array(
			'name' => 'Jo',
			'email' => 'invalid',
			'phone' => '123',
			'age' => '150',
			'message' => 'x',
		));
		$form->validate();

		$this->assertFormTemplateOutput(__DIR__ . '/translation/input/' . $latteFile, __DIR__ . '/translation/output/' . basename($latteFile, '.latte') . '.html', $form);
	}

	/**
	 * @return array
	 */
	public function dataRenderingErrorsAtInputs()
	{
		return array_map(function ($f) { return array(basename($f)); }, glob(__DIR__ . '/errors-at-inputs/input/*.latte'));
	}



	/**
	 * Test errorsAtInputs property behavior in different states:
	 * - true-*: errorsAtInputs = TRUE (default) - only form-level errors in alerts, control errors inline
	 * - false-*: errorsAtInputs = FALSE - all errors in alerts, no inline errors
	 *
	 * @dataProvider dataRenderingErrorsAtInputs
	 * @param string $latteFile
	 */
	public function testRenderingErrorsAtInputs($latteFile)
	{
		$form = $this->dataCreateRichForm();

		// Determine which state to test based on filename prefix
		$isErrorsAtInputsFalse = strpos($latteFile, 'false-') === 0;

		if ($isErrorsAtInputsFalse) {
			// Test errorsAtInputs = FALSE: all errors in alerts, no inline errors
			$renderer = new BootstrapRenderer($this->createTemplate());
			$renderer->errorsAtInputs = FALSE;
			$form->setRenderer($renderer);

			foreach ($form->getControls() as $control) {
				$control->setOption('rendered', FALSE);
			}

			if (property_exists($form, 'httpRequest')) {
				$form->httpRequest = new Nette\Http\Request(new Nette\Http\UrlScript('http://www.kdyby.org'));
			}
			foreach ($form->getComponents(TRUE, 'Nette\Forms\Controls\CsrfProtection') as $control) {
				/** @var \Nette\Forms\Controls\CsrfProtection $control */
				$control->session = new Nette\Http\Session($form->httpRequest, new Nette\Http\Response);
				$control->session->setHandler(new ArraySessionStorage($control->session));
				$control->session->start();
			}

			$controlMock = new ControlMock();
			$controlMock['foo'] = $form;

			$this->assertTemplateOutput(array('form' => $form, '_form' => $form, 'control' => $controlMock, '_control' => $controlMock), __DIR__ . '/errors-at-inputs/input/' . $latteFile, __DIR__ . '/errors-at-inputs/output/' . basename($latteFile, '.latte') . '.html');

			foreach ($form->getComponents(TRUE, 'Nette\Forms\Controls\CsrfProtection') as $control) {
				/** @var \Nette\Forms\Controls\CsrfProtection $control */
				$control->session->close();
			}
		} else {
			// Test errorsAtInputs = TRUE (default): form errors in alerts, control errors inline
			$this->assertFormTemplateOutput(__DIR__ . '/errors-at-inputs/input/' . $latteFile, __DIR__ . '/errors-at-inputs/output/' . basename($latteFile, '.latte') . '.html', $form);
		}
	}



	/**
	 * @param $latteFile
	 * @param $expectedOutput
	 * @param \Nette\Application\UI\Form $form
	 * @throws \Exception
	 */
	private function assertFormTemplateOutput($latteFile, $expectedOutput, Form $form)
	{
		$form->setRenderer(new BootstrapRenderer($this->createTemplate()));
		foreach ($form->getControls() as $control) {
			$control->setOption('rendered', FALSE);
		}

		if (property_exists($form, 'httpRequest')) {
			$form->httpRequest = new Nette\Http\Request(new Nette\Http\UrlScript('http://www.kdyby.org'));
		}
		foreach ($form->getComponents(TRUE, 'Nette\Forms\Controls\CsrfProtection') as $control) {
			/** @var \Nette\Forms\Controls\CsrfProtection $control */
			$control->session = new Nette\Http\Session($form->httpRequest, new Nette\Http\Response);
			$control->session->setHandler(new ArraySessionStorage($control->session));
			$control->session->start();
		}

		$control = new ControlMock();
		$control['foo'] = $form;

		$this->assertTemplateOutput(array('form' => $form, '_form' => $form, 'control' => $control, '_control' => $control), $latteFile, $expectedOutput);

		foreach ($form->getComponents(TRUE, 'Nette\Forms\Controls\CsrfProtection') as $control) {
			/** @var \Nette\Forms\Controls\CsrfProtection $control */
			$control->session->close();
		}
	}



	/**
	 * @param array $params
	 * @param string $latteFile
	 * @param string $expectedOutput
	 * @throws \Exception
	 */
	private function assertTemplateOutput(array $params, $latteFile, $expectedOutput)
	{
		$template = $this->createTemplate()->setFile($latteFile)->setParameters($params);

		// render template
		ob_start();
		try {
			$template->render();
		} catch (\Exception $e) {
			ob_end_clean();
			throw $e;
		}

		$strip = function ($s) {
			return Strings::replace($s, '#(</textarea|</pre|</script|^).*?(?=<textarea|<pre|<script|\z)#si', function ($m) {
				return trim(preg_replace('#[ \t\r\n]{2,}#', "\n", str_replace('><', '>  <', $m[0])));
			});
		};

		$output = $strip(Strings::normalize(ob_get_clean()));
		$expected = $strip(Strings::normalize(file_get_contents($expectedOutput)));
		Assert::match($expected, $output);
	}



	/**
	 * @return Nette\Templating\FileTemplate
	 */
	private function createTemplate()
	{
		$template = $this->container->{$this->container->getMethodName('nette.template')}();
		/** @var \Nette\Templating\FileTemplate $template */
		$template->setCacheStorage(new PhpFileStorage($this->container->expand('%tempDir%/cache'), $this->container->getService('nette.cacheJournal')));
		return $template;
	}

}


/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ControlMock extends Nette\Application\UI\Control
{

}

/**
 * Třída existuje, aby se vůbec neukládala session, tam kde není potřeba.
 * Například v API, nebo v Cronu se různě sahá na session, i když se reálně mezi requesty nepřenáší.
 *
 * @internal
 */
class ArraySessionStorage implements \SessionHandlerInterface
{

	/**
	 * @var array
	 */
	private $session;



	public function __construct(Nette\Http\Session $session = NULL)
	{
		$session->setOptions(array('cookie_disabled' => TRUE));
	}



	public function open($savePath, $sessionName)
	{
		$this->session = array();
		return true;
	}



	public function close()
	{
		$this->session = array();
		return true;
	}



	public function read($id)
	{
		return isset($this->session[$id]) ? $this->session[$id] : '';
	}



	public function write($id, $data)
	{
		$this->session[$id] = $data;
		return true;
	}



	public function destroy($id)
	{
		unset($this->session[$id]);
		return true;
	}



	public function gc($maxlifetime)
	{
		return true;
	}

}

/**
 * Simple translator for testing placeholder translation
 * Translates English form labels and placeholders to Slovak
 * @author Claude Code
 */
class DummyTranslator implements Nette\Localization\ITranslator
{

	/**
	 * Translation table for English to Slovak
	 * @var array
	 */
	private $translations = array(
		'Email' => 'E-mail',
		'Name' => 'Meno',
		'Message' => 'Správa',
		'Submit' => 'Odoslať',
		'Enter your email' => 'Zadajte váš e-mail',
		'Enter your name' => 'Zadajte vaše meno',
		'Type your message here' => 'Napíšte svoju správu',
	);

	/**
	 * Translates the given string to Slovak
	 *
	 * @param string $message
	 * @param int $count
	 * @return string
	 */
	public function translate($message, $count = NULL)
	{
		if (isset($this->translations[$message])) {
			return $this->translations[$message];
		}

		// return modified string to indicate missing translation
		// this helps to identify double translations in code
		return "MISSING_TRANSLATION: $message";
	}

}

/**
 * Translator for testing that errors are not double-translated
 * Translates English form messages to Slovak
 * @author Claude Code
 */
class TranslationTestTranslator implements Nette\Localization\ITranslator
{

	/**
	 * Translation table for English to Slovak
	 * @var array
	 */
	private $translations = array(
		// Field labels
		'Full Name' => 'Celé meno',
		'Email Address' => 'E-mailová adresa',
		'Phone Number' => 'Telefónne číslo',
		'Age' => 'Vek',
		'Message' => 'Správa',
		'Send Message' => 'Odoslať správu',
		'Username' => 'Používateľské meno',

		// Error messages
		'Please enter your name' => 'Zadajte prosím vaše meno',
		'Name must be at least %d characters' => 'Meno musí mať aspoň %d znaky',
		'Please enter your email' => 'Zadajte prosím váš e-mail',
		'Please enter a valid email address' => 'Zadajte prosím platnú e-mailovú adresu',
		'Phone must be in format XXX-XXX-XXXX' => 'Telefón musí byť vo formáte XXX-XXX-XXXX',
		'Age must be a number' => 'Vek musí byť číslo',
		'Age must be between %d and %d' => 'Vek musí byť medzi %d a %d',
		'Please enter your message' => 'Zadajte prosím vašu správu',
		'Message must be at least %d characters' => 'Správa musí mať aspoň %d znakov',
	);

	/**
	 * Translates the given string to Slovak
	 *
	 * @param string $message
	 * @param int $count
	 * @return string
	 */
	public function translate($message, $count = NULL)
	{
		// For Html instances, return as-is (translator shouldn't be called on these)
		if ($message instanceof Html) {
			return $message;
		}

		if (isset($this->translations[$message])) {
			return $this->translations[$message];
		}

		// Return with marker to detect missing translations or double translation attempts
		return 'MISSING_TRANSLATION: ' . $message;
	}

}


run(new BootstrapRendererTest());
