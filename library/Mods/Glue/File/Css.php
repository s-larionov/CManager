<?php

class Mods_Glue_File_Css extends Mods_Glue_File_Abstract {
	/**
	 * @return string
	 */
	public function toHtml() {
		return '<link rel="stylesheet" type="text/css" href="' . $this->getFrontendFilename() . '" media="all">"';
	}
}