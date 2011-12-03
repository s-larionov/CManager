<?php

abstract class CManager_Structure_Adapter_Abstract {
	/**
	 * @abstract
	 * @param string $attributeName
	 * @return string|null
	 */
	abstract public function getAttribute($attributeName);

	/**
	 * @abstract
	 * @return string[]
	 */
	abstract public function getListAttributes();

	/**
	 * @abstract
	 * @param string $childName
	 * @return CManager_Structure_Adapter_Abstract[]|CManager_Structure_Adapter_Abstract|null
	 */
	abstract public function getChild($childName);
}