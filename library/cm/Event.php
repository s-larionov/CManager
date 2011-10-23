<?php

class cm_Event {
	const LOAD		= 'load';
	const CREATE	= 'create';
	const MODIFY	= 'modify';
	const SAVE		= 'save';
	const UPDATE	= 'update';
	const DELETE	= 'delete';
	
	private $_type;
	private $_target;
	
	public function __construct($type) {
		$this->_type = $type;
	}
	
	public function setTarget($taget) {
		$this->_target = $taget;
	}
	
	public function getTarget() {
		return $this->_target;
	}
	
	public function getType() {
		return $this->_type;
	}
}