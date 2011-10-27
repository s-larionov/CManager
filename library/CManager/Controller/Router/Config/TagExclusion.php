<?php

class CManager_Controller_Router_Config_TagExclusion extends CManager_Controller_Router_Config_Abstract {
	protected $_name = 'tag_exclusion';
	protected $_attributes = array(
		'name' => array(
			'namespace' => 'string',
			'required' => true
		),
		'pass' => array(
			'namespace' => 'enum(pass)',
			'required' => false
		)
	);
}