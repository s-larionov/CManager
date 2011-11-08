<?php

class Mods_Glue_Script extends Mods_Glue_Abstract {
	/**
	 * @return Zend_Config
	 */
	public function getConfig() {
		$config = @CManager_Registry::getConfig()->glue->script;
		if (!($config instanceof Zend_Config)) {
			$config = new Zend_Config(array(), true);
		}
		return $config;
	}
}