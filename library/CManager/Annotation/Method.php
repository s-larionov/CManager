<?php

class CManager_Annotation_Method extends CManager_Annotation_Abstract {
	/**
	 * @param string|ReflectionMethod $classOrMethod
	 * @param string $name
	 * @see ReflectionMethod
	 */
	public function __construct($classOrMethod, $name = null) {
		if ($classOrMethod instanceof ReflectionMethod) {
			$this->reflection = $classOrMethod;
		} else {
			$this->reflection = new ReflectionMethod($classOrMethod, $name);
		}
		$this->parseAnnotations();
	}
}
