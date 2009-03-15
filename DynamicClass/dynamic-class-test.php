<?php
// Testify

// @todo Unit tests

require 'OneVersion/DynamicClass.php';

class MyClass extends OneVersion\DynamicClass {
	public $name = 'Dis is my class';

	public function __construct() {
		echo 'Instantiating "' . __CLASS__ . '"' . PHP_EOL;
	}
}

class MyOtherClass extends OneVersion\DynamicClass {
	public function __construct() {
		echo 'Instantiating "' . __CLASS__ . '"' . PHP_EOL;
	}
}

MyClass::addMethod(
	'test',
	function($this, $text=null) {
		if ($text === null) {
			echo 'default test' . PHP_EOL;
		} else {
			echo $text . ' test' . PHP_EOL;
		}
	}
);

MyOtherClass::addMethod(
	'test',
	function($this, $text=null) {
		if ($text === null) {
			echo 'default test2' . PHP_EOL;
		} else {
			echo $text . ' test2' . PHP_EOL;
		}
	}
);

MyOtherClass::addStaticMethod(
	'staticTest',
	function($self, $parent) {
		echo 'This was called statically from ' . $self . ' which extends ' . $parent . '!' . PHP_EOL;
	}
);

$object = new MyClass();
// Should output:
// Instantiating "MyClass"

echo '1: ';
$object->test();
// Should output:
// default test

echo '2: ';
$object->test('monkeys');
// Should output:
// monkeys test

echo '3: ';
$object->test('chimps');
// Should output:
// chimps test

echo '4: ';
$object2 = new MyOtherClass();
// Should output:
// Instantiating "MyOtherClass"

echo '5: ';
$object2->test();
// Should output:
// default test2

echo '6: ';
$object2->test('chimps');
// Should output:
// chimps test2

echo '7: ';
$object2->test('conan');
// Should output:
// conan test2

echo '8: ';
$object->test('conan');
// Should output:
// conan test

echo '9: ';
MyOtherClass::staticTest();
// Should output:
// This was called statically from MyOtherClass which extends OneVersion\DynamicClass!
?>
