<?php

class CManager_Annotation_Object extends CManager_Annotation_Abstract {
	/**
	 * @var null|CManager_Annotation_Method[]
	 */
	protected $methods = null;

	/**
	 * @var null|CManager_Annotation_Property[]
	 */
	protected $properties = null;

	/**
	 * @param Object|ReflectionObject $object
	 */
	public function __construct($object) {
		if ($object instanceof ReflectionObject) {
			$this->reflection = $object;
		} else if (is_object($object)) {
			$this->reflection = new ReflectionObject($object);
		} else {
			throw new CManager_Annotation_Exception("First argument passed must be object");
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
	 * @return CManager_Annotation_Object
	 */
	protected function parseMethodsAnnotations() {
		$this->methods = array();
		foreach ($this->getReflection()->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
			$this->methods[$method->getName()] = new CManager_Annotation_Method($method);
		}

		return $this;
	}

	/**
	 * Parse annotations for public properties
	 *
	 * @return CManager_Annotation_Object
	 */
	protected function parsePropertiesAnnotations() {
		$this->properties = array();
		foreach ($this->getReflection()->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
			$this->properties[$property->getName()] = new CManager_Annotation_Property($property);
		}

		return $this;
	}
}
