#!/usr/local/bin/php5
<?php
// Error checking
if (php_sapi_name() != 'cli') {
	die("This utility should only be run from the command line.\n");
}
if ($argc < 2 || $argc > 4) {
	die("Usage: scaffold form_class_file_path [[build_script_path output_path]|output_path]\n");
}

// Load the Form class
require_once $argv[1];

$paths = explode('/', $argv[1]);
$className = str_replace('.php', '', str_replace('.inc', '', $paths[count($paths)-1]));
$formObject = new $className();

$formVariableNameFirstChar = $className[0];
$formVariableNameFirstChar = strtolower($formVariableNameFirstChar);
$formVariableName = $className;
$formVariableName[0] = $formVariableNameFirstChar;

// Config options
define('SCAFFOLD_BASE_DIR', '');
define('TEMPLATES_DIR', SCAFFOLD_BASE_DIR.'/templates');

// If a build script was passed, call it
if ($argc > 3) {
	require_once $argv[2];
}

// Template placeholders
$formElement = "<? \$this->$formVariableName->displayFormElement(); ?>\n";
$buttons = "<? \$this->$formVariableName->displayElement('buttons'); ?>\n";

$hiddenElements = '';
$elements = array();

// Dynamic element placeholders
foreach($formObject->form->_elements as $quickFormElements) {
	switch ($quickFormElements->_attributes['type']) {
		case 'hidden':
			$elementId = $quickFormElements->_attributes['name'];
			$hiddenElements .= "\n<? \$this->$formVariableName->displayElement('$elementId');\n";
			break;
		case 'button':
			continue;
		default:
			ob_start();
			include TEMPLATES_DIR.'/element.php';
			$elements[] = ob_get_contents();
			ob_end_clean();
			break;
	}
}
$elements = implode("\n", $elements);

// Generate the main form output
ob_start();
require_once TEMPLATES_DIR.'/form.php';
$formOut = ob_get_contents();
ob_end_clean();

// Save to file or output to stdout
if ($argc === 2) {
	echo $formOut;
} else if ($argc === 3) {
	$fileOut = $argv[2];
} else if ($argc === 4) {
	$fileOut = $argv[3];
}

if (isset($fileOut)) {
	file_put_contents($fileOut, $formOut);
	echo "Output written to $fileOut\n";
}
?>
