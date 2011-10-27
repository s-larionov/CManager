<?php

class CManager_Controller_Router_Config_Structure extends CManager_Controller_Router_Config_Abstract {
	protected $_name = 'router';
	protected $_attributes = array(
		'layout' => array(
			'namespace' => 'string',
			'required' => true
		)
	);
	protected $_children = array(
		'permission' => array(
			'namespace' => 'Permission',
			'required' => false,
			'single' => false
		),
		'tag' => array(
			'namespace' => 'Tag',
			'required' => false,
			'single' => false
		),
		'page' => array(
			'namespace' => 'Page',
			'required' => true,
			'single' => false
		)
	);
}