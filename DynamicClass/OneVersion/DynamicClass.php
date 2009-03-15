<?php
/**
 * Source file for DynamicClass
 * @package OneVersion
 * @package php-hacks
 * @filesource
 */

// We're using 5.3, might as well take advantage of namespaces
namespace OneVersion;

/**
 * Class that allows child classes to be dynamically extended
 * with new methods, by assigning lambda functions to an internal
 * hash of methods, which can then be called as any other method
 * via the magic __call() and __callStatic() methods. Also allows
 * property extending via __get() and __set().
 * 
 * PHP version 5.3 and above
 *
 * LICENSE:
 *
 * Copyright (c) 2009 Wesley Aaron Mason
 * All rights reserved.
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation; either version 2.1 of the License, or (at your
 * option) any later version.
 * 
 * This library is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library; if not, write to the Free Software Foundation,
 * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package OneVersion
 * @subpackage php-hacks
 * @author Wesley Aaron Mason <wes@1stvamp.org>
 * @copyright 2009 Wesley Aaron Mason
 * @license http://www.opensource.org/licenses/lgpl-2.1.php
 * @version 1.0
 * @link http://1stvamp.org/dynamicclass
 */
class DynamicClass {
	/**
	 * Hash of dynamic public methods
	 * Each index should be prefixed using the name of
	 * the current class followed by paamayim nekudotayim (::).
	 *
	 * @var array
	 * @protected
	 * @static
	 */
	protected static $publicMethods = array();

	/**
	 * Hash of dynamic public static methods
	 * Each index should be prefixed using the name of
	 * the current class followed by paamayim nekudotayim (::).
	 *
	 * @var array
	 * @protected
	 * @static
	 */
	protected static $staticMethods = array();

	/**
	 * Hash of dynamic properties
	 * Each index should be prefixed using the name of
	 * the current class followed by paamayim nekudotayim (::).
	 *
	 * @var array
	 * @protected
	 * @static
	 */
	protected static $properties = array();

	/**
	 * Hash of mock extended classes
	 * Each index is the name of the class being extended,
	 * and each value is an array containing an instance of the
	 * parent class extended from.and a 
	 *
	 * @var array
	 * @protected
	 * @static
	 */
	protected static $mockExtensions = array();

	/**
	 * Adds a lambda function or closure to the internal public methods
	 * hash and allows you to call it as if it were a real method defined
	 * in the original class definition.
	 *
	 * @static
	 * @see $staticMethods
	 * @see $publicMethods
	 * @param string $name Name of the method as you would call it, e.g. $object->thisMethodName() would be "thisMethodName"
	 * @param Method $lambda Lambda/Closure (PHP internal "Method" object) to add
	 */
	public static function addMethod($name, $lambda) {
		if (array_key_exists(
			get_called_class() . '::' .
			$name, self::$staticMethods)) {

			unset(self::$staticMethods[get_called_class() . '::' . $name]);
		}
		self::$publicMethods[get_called_class() . '::' . $name] = $lambda;
	}

	/**
	 * Adds a lambda function or closure to the internal public static methods
	 * hash and allows you to call it as if it were a real static method defined
	 * in the original class definition.
	 *
	 * @static
	 * @see $staticMethods
	 * @see $publicMethods
	 * @param string $name Name of the method as you would call it, e.g. Class::thisMethodName() would be "thisMethodName"
	 * @param Method $lambda Lambda/Closure (PHP internal "Method" object) to add
	 */
	public static function addStaticMethod($name, $lambda) {
		if (array_key_exists(
			get_called_class() . '::' . $name,
			self::$publicMethods)) {

			unset(self::$publicMethods[get_called_class() . '::' . $name]);
		}
		self::$staticMethods[get_called_class() . '::' . $name] = $lambda;
	}

	/**
	 * Removes a lambda function matching $name from both hashes
	 *
	 * @static
	 * @see $staticMethods
	 * @see $publicMethods
	 * @param string $name Name of the lambda to remove, essentially the hash index
	 * @return bool True if the method was found and removed, false if not found
	 */
	public static function removeMethod($name) {
		if (array_key_exists(
			get_called_class() . '::' . $name,
			self::$staticMethods)) {

			unset(self::$staticMethods[get_called_class() . '::' . $name]);
			return true;
		} else if (array_key_exists(
			get_called_class() . '::' . $name,
			self::$publicMethods)) {

			unset(self::$publicMethods[get_called_class() . '::' . $name]);
			return true;
		}
		return false;
	}

	/**
	 * Add a new index to the internal hash of virtual properties, with
	 * an optional default value.
	 *
	 * @static
	 * @see $properties
	 * @param string $name Name of the property
	 * @param mixed $defaultValue Default value of the property if not set, defaults to null as any class property
	 */
	public static function addProperty($name, $defaultValue=null) {
		self::$properties[get_called_class() . '::' . $name] = $defaultValue;
	}

	/**
	 * Remove an index from the internal hash of virtual properties.
	 *
	 * @static
	 * @see $properties
	 * @param string $name Index to remove
	 */
	public static function removeProperty($name) {
		unset(self::$properties[get_called_class() . '::' . $name]);
	}

	/**
	 * Magic method that intercepts calls to unknown methods, we
	 * use this to intercept calls to our dynamically added methods
	 * and then call the lambda function from the internal hash
	 * instead user call_user_func_array() to pass the arguments
	 * away as actual arguments. We also add a reference to the
	 * current instance as the first argument, as a workaround
	 * replacement for '$this' in an object context.
	 *
	 * @see $publicMethods
	 * @param string $name Name of the method that was called
	 * @param array $arguments Array containing any arguments (if there were any) passed to the method
	 */
	public function __call($name, $arguments) {
		// Make the first argument to the lambda a reference
		// to the current instance, as a replacement for $this,
		// don't bother providing 'self' and 'parent' replacements
		// as you can use get_class($this) and get_parent_class($this)
		// as a workaround
		if (!empty($arguments)) {
			$arguments = array_merge(array(&$this), $arguments);
		} else {
			$arguments = array(&$this);
		}
		if (array_key_exists(
			get_called_class() . '::' . $name,
			self::$publicMethods)) {

			return call_user_func_array(
				self::$publicMethods[get_called_class() . '::' . $name],
				$arguments
			);
		}
		if (array_key_exists(
			get_called_class() . '::' . $name,
			self::$staticMethods)) {

			return call_user_func_array(
				self::$staticMethods[get_called_class() . '::' . $name],
				$arguments
			);
		}
		// Error
		$trace = debug_backtrace();
		trigger_error(
			'Undefined method via __call(): ' . $name,
			E_USER_ERROR
		);
	}

	/**
	 * Magic method that intercepts calls to unknown static methods, we
	 * use this to intercept calls to our dynamically added static methods
	 * and then call the lambda function from the internal hash instead
	 * user call_user_func_array() to pass the arguments away as actual arguments.
	 * We also add in the current class and parent class as the first 2
	 * arguments as replacement for 'self' and 'parent'.
	 *
	 * @see $staticMethods
	 * @param string $name Name of the method that was called
	 * @param array $arguments Array containing any arguments (if there were any) passed to the method
	 */
	public static function __callStatic($name, $arguments) {
		// Make the first 2 arguments to the lambda, the class name
		// and parent class name, as a replacement for 'self' and 'parent'
		if (!empty($arguments)) {
			array_unshift($arguments, get_called_class(), get_parent_class(get_called_class()));
		} else {
			$arguments = array(get_called_class(), get_parent_class(get_called_class()));
		}

		if (array_key_exists(
			get_called_class() . '::' . $name,
			self::$staticMethods)) {

			return call_user_func_array(
				self::$staticMethods[get_called_class() . '::' . $name],
				$arguments
			);
		}
		if (array_key_exists(
			get_called_class() . '::' . $name,
			self::$staticMethods)) {

			return call_user_func_array(
				self::$staticMethods[get_called_class() . '::' . $name],
				$arguments
			);
		}
		// Error
		$trace = debug_backtrace();
		foreach ($trace as $node) {
			if (isset($node['file'])) {
				$file = $node['file'];
				$line = $node['line'];
				break;
			}
		}
		trigger_error(
			'Undefined method via __callStatic(): ' . $name . ' (' . $file . ':' . $line . ')',
			E_USER_ERROR
		);
	}

	/**
	 * Magic method that intercepts accessor requests to non-existant
	 * properties, used so we can provide read access to virtual properties
	 * in the properties hash.
	 *
	 * @see $properties
	 * @param string $name Name of the property being accessed
	 * @return mixed Value stored in properties hash
	 */
	public function __get($name) {
		if (array_key_exists(
			get_called_class() . '::' . $name,
			self::$properties)) {

			return self::$properties[get_called_class() . '::' . $name];
		}
		// Error
		$this->_notice('Undefined property via __get(): ' . $name);
	}

	/**
	 * Magic method that intercepts mutator requests to non-existant
	 * properties, used so we can provide write access to virtual properties
	 * in the properties hash.
	 *
	 * @see $properties
	 * @param string $name Name of the property being set
	 * @param string $value Value of the property to set
	 */
	public function __set($name, $value) {
		if (array_key_exists(
			get_called_class() . '::' . $name,
			self::$properties)) {

			self::$properties[get_called_class() . '::' . $name] = $value;
			return self::$properties[get_called_class() . '::' . $name];
		}
		// Error
		$this->_notice('Undefined property via __set(): ' . $name);
	}

	/**
	 *
	 */
	public static function mockExtends($instance) {
		if (!is_object($instance)) {
			if (!is_string($instance)) {
				$this->_notice(get_called_class() . '::mockExtends() accepts an instance or a string containing the class name to extend from _only_');
			}
			$instance = new $instance;
		}
	}
	
	protected function _notice($message) {
		$trace = debug_backtrace();
		trigger_error(
			$message .
			' (in ' . $trace[2]['file'] .
			' on line ' . $trace[2]['line'] . ')',
			E_USER_NOTICE
		);
	}

}
