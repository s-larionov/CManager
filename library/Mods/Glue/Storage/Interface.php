<?php

interface Mods_Glue_Storage_Interface {
	public function __construct(array $config);
	public function putFile(Mods_Glue_File_Abstract $file);
}