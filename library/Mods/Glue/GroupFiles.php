<?php

class Mods_Glue_GroupFiles {
	/**
	 * @var array
	 */
	protected $_config = array();

	/**
	 * @var Mods_Glue_File_Abstract[]
	 */
	protected $_files = array();

	/**
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		$this->_config = $config;
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

	/**
	 * @return int
	 */
	public function getMTime() {
		$time = 0;
		foreach ($this->getFiles() as $file) {
			$time = max($time, $file->getMTime());
		}
		return $time;
	}

	/**
	 * @return string
	 */
	public function render() {
		$out = '';
		foreach($this->getFiles() as $file) {
			$out .= $file->render();
		}
		return $out;
	}

	/**
	 * @return string
	 */
	public function getFilename() {
		$filenames = array();
		foreach ($this->getFiles() as $file) {
			$filenames[] = $file->getFilename();
		}
		sort($filenames);
		return md5(implode(',', $filenames));
	}

	/**
	 * @return string
	 */
	public function getContent() {
		$content = array();
		foreach ($this->getFiles() as $file) {
			$content[] = $file->getContent();
		}
		return implode("\n\n", $content);
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
	 * @param string $filename
	 * @param int $mtime
	 * @return string
	 */
	protected function _generateUrl($filename, $mtime) {
		return str_replace(
			array('(:filename)', '(:mtime)'),
			array((string) $filename, (string) $mtime),
			$this->getUrlTemplate()
		);
	}

	/**
	 * @return string
	 */
	public function getUrlTemplate() {
		return $this->_urlTemplate;
	}

}