<?php

class Mods_Content extends CManager_Controller_Action_Abstract {

	/**
	 * @return void
	 */
	public function run() {
		$dirs = CManager_Registry::getConfig()->get('content');

		if (!$dirs) {
			$dirs = './content';
		}

		$file = CManager_File::getFullPath($this->getParam('file'), $dirs, false);

		if ($file === null) {
			return;
		}

		$this->sendContent(trim($this->_getContent($file)));
	}

	/**
	 * @param string $file
	 * @return string
	 */
	protected function _getContent($file) {
		$ext = preg_replace("~^.*\\.([^\\.]+)$~", '$1', $file);
		switch($ext) {
			case 'php': case 'inc':
				ob_start();
				include $file;
				return ob_get_clean();
			break;
			case 'xhtml': case 'html': case 'txt':
				return file_get_contents($file);
			break;
		}
		return '';
	}
}