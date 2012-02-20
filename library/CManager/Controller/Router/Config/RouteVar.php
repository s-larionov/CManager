<?php

/**
 * @property string $name
 * @property string $rule
 * @property string|null $explode
 * @property string|null $default
 * @property string|null $pattern
 * @property string|null $namespace
 */
class CManager_Controller_Router_Config_RouteVar extends CManager_Controller_Router_Config_Abstract {
	protected $_name = 'var';

	protected $_attributes = array(
		'name' => array(
			'namespace' => 'string',
			'required' => true
		),
		'rule' => array(
			'namespace' => 'string',
			'required' => true
		),
		'explode' => array(
			'namespace' => 'string',
			'required' => false
		),
		'default' => array(
			'namespace' => 'string',
			'required' => false
		),
		'pattern' => array(
			'namespace' => 'string',
			'required' => false
		),
		'namespace' => array(
			'namespace' => 'string',
			'required' => false
		)
	);

	protected function _set($field, $value) {
		if ($field == 'default' && is_string($value) && defined($value)) {
			$value = constant($value);
		}
		parent::_set($field, $value);
	}


}
