<?php

interface Mods_Glue_Storage_Interface {
	/**
	 * @param array $config
	 */
	public function __construct(array $config);

	/**
	 * @param Mods_Glue_GroupFiles $fileGroup
	 * @return Mods_Glue_Storage_Interface
	 */
	public function put(Mods_Glue_GroupFiles $fileGroup);

	/**
	 * @param string $filename
	 * @return string
	 */
	public function get($filename);

	/**
	 * @param string $filename
	 * @return int
	 */
	public function getMTime($filename);
}
