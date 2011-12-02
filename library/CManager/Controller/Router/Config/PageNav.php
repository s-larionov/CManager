<?php

/**
 * @property string $name
 * @property string $value
 */
class CManager_Controller_Router_Config_PageNav extends CManager_Controller_Router_Config_Abstract {
	protected $_name = 'nav';
	protected $_attributes = array(
		'name' => array(
			'namespace' => 'string',
			'required' => true
		),
		'value' => array(
			'namespace' => 'string',
			'required' => false
		)
	);

	/**
	 * @return array
	 */
	public function getAttributes() {
		$attributes = parent::getAttributes();

		// загружаем неописанные аттрибуты, если указано загружать все.
		foreach($this->getAdapter()->getListAttributes($this->getElement()) as $field) {
			if (array_key_exists($field, $attributes)) {
				continue;
			}
			$attributes[$field] = 'string';
		}

		return $attributes;
	}


}