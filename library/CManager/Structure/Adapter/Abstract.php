<?php

abstract class CManager_Structure_Adapter_Abstract {
	/**
	 * @abstract
	 * @param mixed $element
	 * @param string $attributeName
	 * @return string|null
	 */
	abstract public function getAttribute($element, $attributeName);

	/**
	 * @abstract
	 * @param mixed $element
	 * @param string $childName
	 * @return array
	 */
	abstract public function getChild($element, $childName);
}