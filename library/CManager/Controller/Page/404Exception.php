<?php

class CManager_Controller_Page_404Exception extends CManager_Exception {
	public function __construct() {
		parent::__construct('404 Not found');
	}
}