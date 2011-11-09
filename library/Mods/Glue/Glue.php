<?php

class Mods_Glue_Glue {
	/**
	 * @var Mods_Glue_GroupFiles[]
	 */
	protected $_grpups = null;
	/**
	 * @var Mods_Glue_File_Abstract[]
	 */
	protected $_files = array();

	/**
	 * @var Mods_Glue_Storage_Interface
	 */
	protected $_storage = null;

	/**
	 * @var array
	 */
	protected $_config = array();

	/**
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		$this->_config = $config;
	}

	/**
	 * @return Mods_Glue_File_Abstract[]
	 */
	public function getFiles() {
		return $this->_files;
	}

	/**
	 * @param Mods_Glue_File_Abstract $file
	 * @return void
	 */
	public function addFile(Mods_Glue_File_Abstract $file) {
		$this->_files[] = $file;
	}

	/**
	 * @return Mods_Glue_GroupFiles[]
	 */
	public function getGroups() {
		if ($this->_grpups === null) {
			$this->_grpups = array();
			foreach ($this->getFiles() as $file) {
				if (!array_key_exists($file->getGroupName(), $this->_grpups)) {
					$this->_grpups[$file->getGroupName()] = new Mods_Glue_GroupFiles($this->getConfig());
				}
				$this->_grpups[$file->getGroupName()]->addFile($file);
			}
		}
		return $this->_grpups;
	}

	/**
	 * @param string $alias
	 * @param mixed $default
	 * @return mixed
	 */
	public function getConfig($alias = null, $default = null) {
		if ($alias === null) {
			return $this->_config;
		}
		if (array_key_exists($alias, $this->getConfig())) {
			return $this->_config[$alias];
		}
		return $default;
	}

	public function compile() {
		$storage = $this->getStorage();
		foreach($this->getGroups() as $group) {
			if ($group->getMTime() > $storage->getMTime($group)) {
				try {
					$this->getStorage()->put($group);
				} catch (Mods_Glue_Storage_Exception $e) {
					continue;
				}
			}
			$group->isCompiled(true);
		}
	}

	public function render() {
		$out = '';
		foreach($this->_grpups as $group) {
			$out .= $group->render();
		}
		return $out;
	}

	/**
	 * @return Mods_Glue_Storage_Interface
	 */
	public function getStorage() {
		return $this->_storage;
	}

	/**
	 * @param Mods_Glue_Storage_Interface $storage
	 */
	public function setStorage(Mods_Glue_Storage_Interface $storage) {
		$this->_storage = $storage;
	}

	/**
	 * @static
	 * @param string $class
	 * @param array $config
	 * @return Mods_Glue_File_Abstract
	 * @throws Mods_Glue_Exception
	 */
	public static function newFile($class, $config) {
		if (!$class) {
			throw new Mods_Glue_Exception("Class for file not defined");
		}
		try {
			return CManager_Helper_Object::newInstance($class, 'Mods_Glue_File_Abstract', array($config));
		} catch (CManager_Exception $e) {
			try {
				return CManager_Helper_Object::newInstance("Mods_Glue_File_{$class}", 'Mods_Glue_File_Abstract', array($config));
			} catch (CManager_Exception $e) {
				throw new Mods_Glue_Exception("Class '{$class}' not found");
			}
		}
	}
}
