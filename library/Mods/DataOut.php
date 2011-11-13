<?php

class Mods_DataOut extends CManager_Controller_Action_Abstract {
	public function run() {
		if ($this->hasParam('data')) {
			$this->sendContent($this->getParam('data'));
		}
	}
}
