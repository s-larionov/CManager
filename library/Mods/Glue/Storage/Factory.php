<?php

class Mods_Glue_Storage_Factory {
	public static function factory(array $config) {
		if (!array_key_exists('adapter', $config)) {
			throw new Mods_Glue_Exception("Storage adapter not configured");
		}
		if (!array_key_exists('config', $config) || !is_array($config['config'])) {
			$config['config'] = array();
		}

		try {
			return CManager_Helper_Object::newInstance($config['adapter'], 'Mods_Glue_Storage_Interface', array($config['config']));
		} catch (CManager_Exception $e) {
			try {
				var_dump('Mods_Glue_Storage_' . $config['adapter']);
				var_dump(CManager_Helper_Object::newInstance('Mods_Glue_Storage_' . $config['adapter'], 'Mods_Glue_Storage_Interface', array($config['config'])));
			} catch (CManager_Exception $e) {
				throw new Mods_Glue_Storage_Exception("Adapter {$config['adapter']} not found");
			}
		}
	}
}