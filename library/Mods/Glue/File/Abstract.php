<?php

abstract class Mods_Glue_File_Abstract {
	/**
	 * @var string
	 */
	protected $_filename = null;

	/**
	 * @var array
	 */
	protected $_config = array();

	/**
	 * @var CManager_Filter_Interface[]
	 */
	protected $_filters = null;

	/**
	 * @var int
	 */
	protected $_modifyTime = null;

	/**
	 * @param array|string $filename
	 * @param array $config
	 */
	public function __construct($filename, array $config = array()) {
		if (is_array($filename)) {
			$config = $filename;
			if (!isset($config['file'])) {
				throw new Mods_Glue_File_Exception("Filename is empty");
			}
			$this->_filename = $config['file'];
		} else {
			$this->_filename = (string) $filename;
		}
		if (!file_exists($this->getFullFilename())) {
			throw new Mods_Glue_File_Exception("File '{$this->getFullFilename()}' not exists.");
		}

		if (is_array($config)) {
			$this->_config = $config;
		}
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
	 * @return string|null
	 */
	public function getConditionalComment() {
		return $this->getConfig('if', null);
	}

	/**
	 * @return int
	 */
	public function getMTime() {
		if ($this->_modifyTime === null) {
			$this->_modifyTime = filemtime($this->getFullFilename());
		}
		return $this->_modifyTime;
	}

	/**
	 * @param CManager_Filter_Interface $filter
	 * @return Mods_Glue_File_Abstract
	 */
	public function addFilter(CManager_Filter_Interface $filter) {
		$this->_filters[] = $filter;
		return $this;
	}

	/**
	 * @return CManager_Filter_Interface[]
	 */
	public function getFilters() {
		return $this->_filters;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		$content = file_get_contents($this->getFullFilename());
		foreach ($this->getFilters() as $filter) {
			$content = $filter->filter($content);
		}
		return $content;
	}

	/**
	 * @return string
	 */
	public function getFullFilename() {
		return '/'. trim($this->getConfig('documentRoot', $_SERVER['DOCUMENT_ROOT']), '/') .'/'. ltrim($this->_filename, '/');
	}

	/**
	 * @return string
	 */
	public function getFilename() {
		return $this->_filename;
	}

	/**
	 * @return string
	 */
	public function getGroupName() {
		return $this->getConditionalComment() . $this->getMimeType();
	}

	/**
	 * @return string
	 */
	public function render() {
		$out = $this->_render();
		if (($conditionalComment = $this->getConditionalComment()) !== null) {
			$out = "<!--[if {$conditionalComment}]>{$out}<![endif]-->";
		}
		return $out;
	}

	/**
	 * @return array
	 */
	public function __sleep() {
		return array('_filters', '_config', '_filename', '_modifyTime');
	}

	/**
	 * @return string
	 */
	abstract public function getMimeType();

	/**
	 * @return string
	 */
	abstract protected function _render();
}
