<?php

class Mods_Navigation_ItemTitle {
	/**
	 * @var string
	 */
	protected $_title = '';
	/**
	 * @var string
	 */
	protected $_mode = 'default';

	/**
	 * @param string $title
	 * @param string $mode
	 */
	public function __construct($title, $mode = 'default') {
		$this->_title	= (string) $title;
		$this->_mode	= (string) $mode;
	}

	/**
	 * @return string
	 */
	public function toXml() {
		return '<title mode="' . htmlspecialchars($this->_mode) . '"><![CDATA[' . $this->_title . ']]></title>';
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getTitle();
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return (string) $this->_title;
	}

	/**
	 * @return string
	 */
	public function getMode() {
		return (string) $this->_mode;
	}
}