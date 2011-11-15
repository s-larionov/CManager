<?php

/**
 * @property string $layout
 *
 * @property CManager_Controller_Router_Config_Permission[]	$permission
 * @property CManager_Controller_Router_Config_Tag[]		$tag
 * @property CManager_Controller_Router_Config_Page[]		$page
 */
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
			'namespace' => 'CManager_Controller_Router_Config_Permission',
			'required' => false,
			'single' => false
		),
		'tag' => array(
			'namespace' => 'CManager_Controller_Router_Config_Tag',
			'required' => false,
			'single' => false
		),
		'page' => array(
			'namespace' => 'CManager_Controller_Router_Config_Page',
			'required' => true,
			'single' => false
		)
	);
}