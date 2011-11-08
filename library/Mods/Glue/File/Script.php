<?php

class Mods_Glue_File_Script extends Mods_Glue_File_Abstract {
	/**
	 * @return string
	 */
	protected function _render() {
		return '<script type="'. $this->getMimeType() .'" src="'. $this->getFilename() .'"></script>';
	}

	/**
	 * @return string
	 */
	public function getMimeType() {
		return (string) $this->getConfig('type', 'text/javascript');
	}
}
