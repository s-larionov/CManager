<?php

class Mods_Content extends CManager_Controller_Action_Cache {
	protected $_cacheEnabled = true;

	/**
	 * @return int|null
	 */
	protected function _getCacheValidateHash() {
		$filename = $this->getFilename();
		return $filename? filemtime($filename): null;
	}

	/**
	 * @return void
	 */
	public function run() {
		$this->tryLoadFromCache();

		$filename = $this->getFilename();
		if ($filename === null) {
			throw new CManager_Exception("File '{$filename}' doesen't exists");
		}

		$this->sendContent(trim($this->_getContent($filename)));
	}

	/**
	 * @return null|string
	 */
	public function getFilename() {
		$dirs = CManager_Registry::getConfig()->get('content');
		if (!$dirs) {
			$dirs = './content';
		}
		return CManager_Helper_File::getFullPath($this->getParam('file'), $dirs, false);
	}

	/**
	 * @param string $file
	 * @return string
	 */
	protected function _getContent($file) {
		$ext = preg_replace("~^.*\\.([^\\.]+)$~", '$1', $file);
		switch($ext) {
			case 'php':
			case 'inc':
				ob_start();
				include $file;
				return ob_get_clean();
				break;
			case 'xhtml':
			case 'html':
			case 'txt':
				return file_get_contents($file);
				break;
		}
		return '';
	}
}