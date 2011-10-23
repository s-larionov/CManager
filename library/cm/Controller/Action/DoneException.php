<?php

// @todo: не проверял еще

class cm_Controller_Action_DoneException extends Exception {
	private $_content;

	public function __construct($content = null) {
		$this->_content = $content;
	}

	public function __toString() {
		return (string) $this->_content;
	}
}
