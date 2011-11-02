<?php

interface Mods_Glue_File_Interface {
	/**
	 * @param string $filename
	 * @param string[] $attributes
	 */
	public function __construct($filename, array $attributes = array());

	/**
	 * @abstract
	 * @return string|null
	 */
	public function getConditionalCommentName();

	/**
	 * @abstract
	 * @param string $attributeName
	 * @return string|null
	 */
	public function getAttribute($attributeName);

	/**
	 * @abstract
	 * @param string $name
	 * @param string $value
	 * @return Mods_Glue_File_Interface
	 */
	public function setAttribute($name, $value);

	/**
	 * @abstract
	 * @return string
	 */
	public function toHtml();

	/**
	 * @abstract
	 * @return string
	 */
	public function getFrontendFilename();
}

abstract class Mods_Glue_File_Abstract extends CManager_File implements Mods_Glue_File_Interface {
	/**
	 * @var string[]
	 */
	protected $_attributes = array();

	/**
	 * @var string
	 */
	protected $_root = null;

	public function __construct($filename, array $attributes = array()) {
		parent::__construct(ltrim($filename, '/'));
		$this->setAttributes($attributes);
	}

	/**
	 * @param string $root
	 */
	public function setRoot($root) {
		$this->_root = rtrim((string) $root, '/') . '/';
	}

	/**
	 * @return string
	 * @throws Mods_Glue_Exception
	 */
	public function getRoot() {
		if ($this->_root === null) {
			$config = CManager_Registry::getConfig();
			if ($config->root) {
				$this->setRoot($config->root);
			} else if (defined('APPLICATION_ROOT')) {
				$this->setRoot(APPLICATION_ROOT);
			} else if (isset($_SERVER['DOCUMENT_ROOT'])) {
				$this->setRoot($_SERVER['DOCUMENT_ROOT']);
			} else {
				throw new Mods_Glue_Exception("Document root is not defined");
			}
		}
		return $this->_root;
	}

	/**
	 * @param string $name
	 * @return string|null
	 */
	public function getAttribute($name) {
		if (array_key_exists($name, $this->_attributes)) {
			return $this->_attributes[$name];
		}
		return null;
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @return Mods_Glue_File_Abstract
	 */
	public function setAttribute($name, $value) {
		$this->_attributes[(string) $name] = (string) $value;
		return $this;
	}

	/**
	 * @param string[] $attributes
	 * @return Mods_Glue_File_Abstract
	 */
	public function setAttributes(array $attributes) {
		foreach($attributes as $name => $value) {
			$this->setAttribute($name, $value);
		}
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getConditionalCommentName() {
		return $this->getAttribute('if');
	}

	public function getFilename() {
		return "{$this->getRoot()}/{$this->_filename}";
	}

	public function getFrontendFilename() {
		return "/{$this->_filename}";
	}
}

