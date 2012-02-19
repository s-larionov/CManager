<?php

class CManager_Annotation_Property extends CManager_Annotation_Abstract {
	/**
	 * @param string|Object|ReflectionProperty $classOrObject
	 * @param string $name
	 * @see ReflectionProperty
	 */
	public function __construct($classOrObject = null, $name = null) {
		if ($classOrObject instanceof ReflectionProperty) {
			$this->reflection = $classOrObject;
		} else {
			$this->reflection = new ReflectionProperty($classOrObject, $name);
		}
		$this->parseAnnotations();
	}
}
