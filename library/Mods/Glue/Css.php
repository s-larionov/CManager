<?php

class Mods_Glue_Css extends Mods_Glue_Abstract {
	/**
	 * @return Zend_Config
	 */
	public function getConfig() {
		$config = @CManager_Registry::getConfig()->glue->css;
		if (!($config instanceof Zend_Config)) {
			$config = new Zend_Config(array(), true);
		}
		return $config;
	}

	/**
	 * @param string|string[] $config
	 * @return Mods_Glue_File_Abstract
	 */
	protected function _createFile($config) {
		return new Mods_Glue_File_Css($config);
	}
}