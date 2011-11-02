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
	 * @return boolean
	 */
	public function hasConditionalCommentName();

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
	public function getFilename();
}

abstract class Mods_Glue_File_Abstract implements Mods_Glue_File_Interface {
	/**
	 * @var string[]
	 */
	protected $_attributes = array();

	/**
	 * @var string
	 */
	protected $_root = null;

	/**
	 * @var string
	 */
	protected $_filename = null;

	/**
	 * @param string $filename
	 * @param array $attributes
	 */
	public function __construct($filename, array $attributes = array()) {
		$this->_filename = ltrim($filename, '/');
		$this->setAttributes($attributes);
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

	public function getUrl() {
		return "/{$this->_filename}";
	}
}

