<?php

/**
 * @method ReflectionProperty getReflection
 */
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

	public function hasDefaultValue() {
		return ($this->getDefaultValue() !== null);
	}

	/**
	 * Получаем дефолтное значение для этого св-ва (через Reflection)
	 * @todo: придумать как это реализовать более аккуратно
	 *
	 * @return mixed
	 */
	public function getDefaultValue() {
		$defaultValues = $this->getReflection()->getDeclaringClass()->getDefaultProperties();
		return $defaultValues[$this->getReflection()->getName()];
	}
}
