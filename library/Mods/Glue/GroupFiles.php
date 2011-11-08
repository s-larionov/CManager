<?php

class Mods_Glue_GroupFiles {
	const DEFAULT_TEMPLATE_URL		= '/(:filename)?(:mtime)';
	const DEFAULT_TEMPLATE_FILENAME	= '(:filename)';

	/**
	 * @var array
	 */
	protected $_config = array();

	/**
	 * @var boolean
	 */
	protected $_isCompiled = false;

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
		if ($this->isCompiled()) {
			return Mods_Glue_Glue::newFile(
				$this->getConfig('class'),
				array_merge(
					// todo: переделать
					$this->_files[0]->getConfig(),
					array('file' => $this->_generateUrl($this->getFilename()))
				))
				->render();
		} else {
			foreach($this->getFiles() as $file) {
				$out .= $file->render();
			}
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
		return str_replace(
			'(:filename)',
			md5(implode(',', $filenames)),
			$this->getTemplateFilename()
		);
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
	protected function _generateUrl($filename) {
		return str_replace(
			array('(:filename)', '(:mtime)'),
			array((string) $filename, (string) $this->getMTime()),
			$this->getTemplateUrl()
		);
	}

	/**
	 * @return string
	 */
	public function getTemplateUrl() {
		return $this->getConfig('url', self::DEFAULT_TEMPLATE_URL);
	}

	/**
	 * @return string
	 */
	public function getTemplateFilename() {
		return $this->getConfig('filename', self::DEFAULT_TEMPLATE_FILENAME);
	}

	/**
	 * @param boolean $flag
	 * @return boolean
	 */
	public function isCompiled($flag = null) {
		if ($flag !== null) {
			$this->_isCompiled = (bool) $flag;
		}
		return $this->_isCompiled;
	}

}