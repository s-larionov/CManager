<?php

class CManager_Controller_Router_Config_PageTitle extends CManager_Controller_Router_Config_Abstract {
	protected $_name = 'title';
	protected $_attributes = array(
		'mode' => array(
			'namespace' => 'string',
			'required' => false,
			'default' => 'default'
		),
		'value' => array(
			'namespace' => 'string',
			'required' => false
		)
	);
}