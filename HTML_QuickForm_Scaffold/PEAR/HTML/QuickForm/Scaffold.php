<?php
// Config options
if (!defined('SCAFFOLD_BASE_DIR')) {
	define('SCAFFOLD_BASE_DIR', '');
}
if (!defined('TEMPLATES_DIR')) {
	define('TEMPLATES_DIR', SCAFFOLD_BASE_DIR.'/templates');
}

class HTML_QuickForm_Scaffold {

	private $output = '';
	private $outputFile = '';
	private $className = '';
	private $formObject;

	public function __construct($className=null, $formObject=null, $outputFile=null) {
		if ($className !== null) {
			$this->className = $className;
		}
		if ($formObject !== null) {
			$this->formObject = $formObject;
		}
		if ($outputFile !== null) {
			$this->outputFile = $outputFile;
		}
	}

	public function build () {
		$formVariableNameFirstChar = $this->className[0];
		$formVariableNameFirstChar = strtolower($formVariableNameFirstChar);
		$formVariableName = $this->className;
		$formVariableName[0] = $formVariableNameFirstChar;

		// If an object, (probably with business specific data,
		// and logic within a build() function), was set, then use it
		if ($this->formObject === null) {
			$this->formObject = new $this->className();
		}
		$formObject =& $this->formObject;

		// Template placeholders
		$elementTemplate = file_get_contents(TEMPLATES_DIR.'/basic_element.tpl');
		$elementTemplate = str_replace('%%formName%%', $formVariableName, $elementTemplate);
		$elementErrorTemplate = file_get_contents(TEMPLATES_DIR.'/basic_element_error.tpl');
		$elementErrorTemplate = str_replace('%%formName%%', $formVariableName, $elementErrorTemplate);
		$formElement = file_get_contents(TEMPLATES_DIR.'/form_element.tpl');
		$formElement = str_replace('%%formName%%', $formVariableName, $formElement);
		$buttons = str_replace('%%id%%', 'buttons', $elementTemplate);
		$elementBlock = file_get_contents(TEMPLATES_DIR.'/element.tpl');

		$hiddenElements = array();
		$elements = array();

		// Dynamic element placeholders
		foreach($formObject->form->_elements as $quickFormElements) {
			$elementId = $quickFormElements->_attributes['name'];
			$elementLabel = $quickFormElements->_attributes['label'];
			switch ($quickFormElements->_attributes['type']) {
				case 'hidden':
					$hiddenElements[] = str_replace('%%id%%', $elementId, $elementTemplate);
					break;
				case 'button':
					continue;
				default:
					$element = "\n".str_replace('%%id%%', $elementId, $elementTemplate)."\n";
					$elementError = "\n".str_replace('%%id%%', $elementId, $elementErrorTemplate)."\n";
					
					$elements[] = str_replace('%%label%%', $elementLabel, str_replace('%%error%%', $elementError, str_replace('%%element%%', $element, $elementBlock)));
					break;
			}
		}
		$hiddenElements = implode("\n", $hiddenElements);
		$elements = implode("\n", $elements);

		// Generate the main form output
		$formOut = file_get_contents(TEMPLATES_DIR.'/form.tpl');
		$formOut = str_replace('%%formElement%%', $formElement);
		$formOut = str_replace('%%hiddenElements%%', $hiddenElements);
		$formOut = str_replace('%%elements%%', $elements);
		$formOut = str_replace('%%buttons%%', $buttons);
		
		$this->output = $formOut;

	}

	public function saveFile() {
		file_put_contents($this->outputFile, $this->output);
	}

	public function getOutput() {
		return $this->output;
	}

	public function getOutputFile() {
		return $this->outputFile;
	}
	public function setOutputFile($filename) {
		$this->outputFile = $filename;
	}
}
?>
