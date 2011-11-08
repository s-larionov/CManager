<?php

class Mods_Glue_Storage_Mock implements  Mods_Glue_Storage_Interface {
	/**
	 * @param array $config
	 */
	public function __construct(array $config) {}

	/**
	 * @param Mods_Glue_GroupFiles $fileGroup
	 * @return Mods_Glue_Storage_Filesystem
	 */
	public function put(Mods_Glue_GroupFiles $fileGroup) {
		throw new Mods_Glue_Storage_Exception("Mock");
	}

	/**
	 * @param Mods_Glue_GroupFiles $fileGroup
	 * @return int
	 */
	public function getMTime(Mods_Glue_GroupFiles $fileGroup) {
		return $this->getMTimeByFilename($fileGroup->getFilename());
	}

	/**
	 * @param string $filename
	 * @return int
	 */
	public function getMTimeByFilename($filename) {
		return time();
	}

	/**
	 * @param Mods_Glue_GroupFiles $fileGroup
	 * @return string
	 */
	public function get(Mods_Glue_GroupFiles $fileGroup) {
		return $this->getByFilename($fileGroup->getContent());
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	public function getByFilename($filename) {
		return '';
	}
}
