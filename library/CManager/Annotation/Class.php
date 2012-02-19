<?php

class CManager_Annotation_Class extends CManager_Annotation_Abstract {
	/**
	 * @var null|CManager_Annotation_Method[]
	 */
	protected $methods = null;

	/**
	 * @var null|CManager_Annotation_Property[]
	 */
	protected $properties = null;

	/**
	 * @param Object|ReflectionClass $object
	 */
	public function __construct($class) {
		if ($class instanceof ReflectionClass) {
			$this->reflection = $class;
		} else if (class_exists($class)) {
			$this->reflection = new ReflectionClass($class);
		} else {
			throw new CManager_Annotation_Exception("First argument passed must be class name");
		}

		$this->parseAnnotations();
	}

	/**
	 * @return CManager_Annotation_Method[]
	 */
	public function getMethodsAnnotations() {
		if ($this->methods === null) {
			$this->parseMethodsAnnotations();
		}
		return $this->methods;
	}

	/**
	 * @return CManager_Annotation_Property[]
	 */
	public function getPropertiesAnnotations() {
		if ($this->properties === null) {
			$this->parsePropertiesAnnotations();
		}
		return $this->properties;
	}

	/**
	 * Parse annotations for public methods
	 *
	 * @return CManager_Annotation_Class
	 */
	protected function parseMethodsAnnotations() {
		$this->methods = array();
		foreach ($this->getReflection()->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {

			$this->methods[$method] = new CManager_Annotation_Method($method);
		}

		return $this;
	}

	/**
	 * Parse annotations for public properties
	 *
	 * @return CManager_Annotation_Class
	 */
	protected function parsePropertiesAnnotations() {
		$this->properties = array();
		foreach ($this->getReflection()->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
			$this->properties[$property->getName()] = new CManager_Annotation_Property($property);
		}

		return $this;
	}
}
