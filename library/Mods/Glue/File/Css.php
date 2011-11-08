<?php

class Mods_Glue_File_Css extends Mods_Glue_File_Abstract {
	/**
	 * @return string
	 */
	protected function _render() {
		return '<link href="'. $this->getFilename() .'" rel="stylesheet" '
			.'type="'. $this->getMimeType() .'" media="'. $this->getConfig('media', 'all') .'" />';
	}

	/**
	 * @return string
	 */
	public function getGroupName() {
		return parent::getGroupName() . $this->getConfig('media', 'all');
	}

	/**
	 * @return string
	 */
	public function getMimeType() {
		return (string) $this->getConfig('type', 'text/css');
	}
}
