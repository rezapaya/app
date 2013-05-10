<?php

/**
 * MockProxy class to encapsulate the runkit functionality that allows us to override static constructors
 *
 * It manages the construction of objects and function mapping from a generic MockProxy wrapper to the specific Mock object we are using
 *
 * _mockClassName is used by __call to figure out what the original class is because get_class() and get_called_class() dont have it
 * $instances is an array of class name -> mock instance mappings
 *   Title => Mock_Title (String=>object)
 *   Mock_Title_2e631b09 => Mock_Title (String=>object)
 */

class WikiaMockProxy {

	public $_mockClassName = null;
	public $_constructorArguments = null;
	public static $instances = array();
	public static $redefined_functions = array();
	public static $redefined_global_functions = array();
	public static $instance = null;  // temporary holder for instance reference during construction

	// proxy takes the name of the class and the mock object classname and the mock object instance
	// We store a reference by both original class name "Foo" and mocked class name "Mock_Foo_1234asdf" for convenience
	static public function proxy($className, $mockClassName, $mockInstance) {
		self::$instances[$className] = $mockInstance;
		self::$instances[$mockClassName] = $mockInstance;
	}

	// save the old function as _saved_functionName
	// restore in WikiaBaseTest::tearDown
	static public function redefineGlobalFunction($functionName, $returnValue, $params) {
		if(isset(self::$redefined_global_functions[$functionName])) {
			echo "Function $functionName already redefined, skipping\n";
			return;
		}
		self::$redefined_global_functions[$functionName] = $returnValue;
		$params = '';//implode(",",$params);
		runkit_function_rename($functionName, "_saved_".$functionName);
		runkit_function_add($functionName, $params, 'return WikiaMockProxy::$redefined_global_functions["'.$functionName.'"];');
	}

	// save the old function as _saved_functionName
	// restore in WikiaBaseTest::tearDown
	static public function redefineStaticConstructor($className, $functionName) {
		self::$redefined_functions[$className]["_saved_".$functionName] = $functionName;
		runkit_method_rename("$className", "$functionName", "_saved_".$functionName);  // save the original method
		runkit_method_add("$className", "$functionName", '', 'return WikiaMockProxy::$instances["'.$className.'"];', RUNKIT_ACC_PUBLIC | RUNKIT_ACC_STATIC );
	}

	// Because overload is called _immediately_ before the __construct function
	// we can use a static instance to hold the instance of whatever class we are overloading
	// We have to do this because overload returns a string with the class name and not an object (grr)
	static public function overload($className) {

		if (array_key_exists($className, self::$instances)) {
			self::$instance = self::$instances[ $className ];
			$instanceClassName = get_class(self::$instance);
			if ( startsWith($instanceClassName,'Mock_') ) {
				$instanceClassName = self::buildMockClass($instanceClassName);
			} else {
				$instanceClassName = 'WikiaMockProxy';
			}
			return $instanceClassName;
		} else {
			return $className;
		}

	}

	// Forget about all the overloaded instances
	// Restore all the redefined static constructor methods to their original methods
	static public function cleanup() {
		WikiaMockProxy::$instances = array();
		foreach(WikiaMockProxy::$redefined_functions as $className => $function_map) {
			foreach ($function_map as $savedName => $originalName) {
				runkit_method_remove($className, $originalName);  // remove the redefined instance
				runkit_method_rename($className, $savedName, $originalName); // restore the original
			}
			unset (WikiaMockProxy::$redefined_functions[$className]);
		}
		foreach(WikiaMockProxy::$redefined_global_functions as $originalName=>$retValue) {
			runkit_function_remove($originalName);  // remove the redefined instance
			runkit_function_rename("_saved_" . $originalName, $originalName); // restore the original
			unset (WikiaMockProxy::$redefined_global_functions[$originalName]);
		}
	}

	static protected function buildMockClass( $className ) {
		$mockClassName = "{$className}_WikiaMockProxy";
		if ( class_exists( $mockClassName ) ) {
			return $mockClassName;
		}
		$code = <<<EOFF
class {$mockClassName} extends {$className} {
public function __construct() {
	\$instance = WikiaMockProxy::\$instance;
	\$reflC = new ReflectionClass(\$instance);
	\$reflP = \$reflC->getProperty('__phpunit_invocationMocker');
	\$reflP->setAccessible(true);
	\$value = \$reflP->getValue(\$instance);
	\$reflP->setValue(\$this,\$value);
}
}
EOFF;
		eval($code);
		return $mockClassName;
	}

	// If something calls new on a MockProxy object, we return our mock object instance
	// The instance var is stored by overload() and will be lost on the next use of this class
	// We store our original class name so that __call can find it
	public function __construct() {
		$this->_mockClassName = get_class(self::$instance);
		$this->_constructorArguments = func_get_args();
	}

	// PHP thinks that it is dealing with a MockProxy object, which has no useful functions
	// So we have to map all function calls back to the original class/method
	public function __call($name, $arguments) {
		if (method_exists($this->_mockClassName, $name)) {
			$mockObject = self::$instances[$this->_mockClassName];
			return call_user_func_array(array($mockObject, $name), $arguments);
		} else {
			return null;
		}
	}

	public function __get( $name ) {
		$mockObject = self::$instances[$this->_mockClassName];
		return $mockObject->$name;
	}

	public function __set( $name, $value ) {
		$mockObject = self::$instances[$this->_mockClassName];
		$mockObject->$name = $value;
		return $value;
	}

	public function __isset( $name ) {
		$mockObject = self::$instances[$this->_mockClassName];
		return isset($mockObject->$name);
	}

	public function __unset( $name ) {
		$mockObject = self::$instances[$this->_mockClassName];
		unset($mockObject->$name);
	}

}
