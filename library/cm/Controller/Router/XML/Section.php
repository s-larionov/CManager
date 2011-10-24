<?php

class cm_Controller_Router_XML_Section {

	/**
	 * Contains array of configuration data
	 *
	 * @var array
	 */
	protected $_data;

	/**
	 * @var SimpleXMLElement
	 */
	protected $_xml;

	/**
	 * Load file error string.
	 * Is null if there was no error while file loading
	 *
	 * @var string
	*/
	protected $_loadFileErrorStr = null;


	/**
	 * @param string $xml
	 */
	public function __construct($xml) {
		if (empty($xml)) {
			throw new cm_Controller_Router_XML_Exception('Filename is not set');
		}

		set_error_handler(array($this, '_loadFileErrorHandler')); // Warnings and errors are suppressed
		if (strstr($xml, '<?xml')) {
			$this->_xml = simplexml_load_string($xml);
		} else {
			$this->_xml = simplexml_load_file($xml);
		}
		restore_error_handler();

		// Check if there was a error while loading file
		if ($this->_loadFileErrorStr !== null) {
			throw new cm_Controller_Router_XML_Exception($this->_loadFileErrorStr);
		}

		$this->_data = (array) $this->_processSection($this->_xml);
	}

	/**
	 * @param SimpleXMLElement $section
	 * @return array|string
	 */
	protected function _processSection(SimpleXMLElement $section) {
		$config = array();

		$attributes	= $section->attributes();

		if (count($attributes) > 0) {
			foreach ($attributes as $name => $attribute) {
				if ($name === 'extends') {
					continue;
				}
				$config[$name] = (string) $attribute;
			}
		}

		if (empty($config) && count($section->children()) == 0) {
			return trim((string) $section);
		}

		foreach($section->children() as $subSectionName => $subSection) {
			if ($extends = $subSection->attributes()->extends) {
				$subSection = $this->_extendSection($subSection, $this->_getSection($subSection, $extends));
			}
			if (isset($config[$subSectionName])) {
				if (!is_array($config[$subSectionName]) || !array_key_exists(0, $config[$subSectionName])) {
					$config[$subSectionName] = array($config[$subSectionName]);
				}
				$config[$subSectionName][] = $this->_processSection($subSection);
			} else {
				$config[$subSectionName] = $this->_processSection($subSection);
			}
		}
		return $config;
	}

	/**
	 * @param SimpleXMLElement $section
	 * @param string $extendXPath
	 * @return SimpleXMLElement
	 * @throws cm_Controller_Router_XML_Exception
	 */
	protected function _getSection(SimpleXMLElement $section, $extendXPath) {
		$extendSection = $section->xpath($extendXPath . '[1]');
		if (count($extendSection) > 0 && $extendSection[0] instanceof SimpleXMLElement) {
			return $extendSection[0];
		}
		throw new cm_Controller_Router_XML_Exception("Section {$extendXPath} not found");
	}

	/**
	 * @param SimpleXMLElement $section
	 * @param SimpleXMLElement $extendSection
	 * @return SimpleXMLElement
	 */
	protected function _extendSection(SimpleXMLElement $section, SimpleXMLElement $extendSection) {
		$result = new SimpleXMLElement("<{$section->getName()}/>");

		if ($extendSection->attributes()->extends) {
			$extendSection = $this->_extendSection(
				$extendSection,
				$this->_getSection($extendSection, $extendSection->attributes()->extends)
			);
		}

		// наследуем атрибуты, сначала переписываем те, что приоритетней
		foreach($section->attributes() as $name => $attribute) {
			if ($name !== 'extends') {
				$result->addAttribute($name, (string) $attribute);
			}
		}
		foreach($extendSection->attributes() as $name => $attribute) {
			if ($result->attributes()->$name === null) {
				$result->addAttribute($name, (string) $attribute);
			}
		}

		foreach($section as $name => $element) {
			$extendElement = $extendSection->$name;
			if (count($extendElement) == 0) {
				$this->_appendSimpleXMLChild($result, $element);
			} elseif (count($extendElement) == 1) {
				$this->_appendSimpleXMLChild($result, $this->_extendSection($element, $extendElement[0]));
			} else {
				if (count($result->$name) == 0) {
					foreach($extendElement as $item) {
						$this->_appendSimpleXMLChild($result, $item);
					}
				}
				$this->_appendSimpleXMLChild($result, $element);
			}
		}

		return $result;
	}

	/**
	 * @param SimpleXMLElement $xml
	 * @param SimpleXMLElement $element
	 */
	protected function _appendSimpleXMLChild(SimpleXMLElement $xml, SimpleXMLElement $element) {
		$newElement = $xml->addChild($element->getName());

		foreach($element->attributes() as $name => $attribute) {
			$newElement ->addAttribute($name, (string) $attribute);
		}

		foreach($element as $child) {
			$this->_appendSimpleXMLChild($newElement, $child);
		}
	}

	/**
	 * Handle any errors from simplexml_load_file or parse_ini_file
	 *
	 * @param integer $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param integer $errline
	 */
	protected function _loadFileErrorHandler($errno, $errstr, $errfile, $errline) {
		if ($this->_loadFileErrorStr === null) {
			$this->_loadFileErrorStr = $errstr;
		} else {
			$this->_loadFileErrorStr .= (PHP_EOL . $errstr);
		}
	}

	/**
	 * Returns a string or an associative and possibly multidimensional array from
	 * a SimpleXMLElement.
	 *
	 * @param  SimpleXMLElement $xml Convert a SimpleXMLElement into an array
	 * @return array|string
	 */
	protected function _toArray(SimpleXMLElement $xml) {
		$config		= array();
		$attributes	= $xml->attributes();

		// Search for parent node values
		if (count($attributes) > 0) {
			foreach ($attributes as $name => $attribute) {
				if ($name === 'extends') {
					continue;
				}

				$attribute = (string) $attribute;

				if (array_key_exists($attribute, $config)) {
					if (!is_array($config[$name])) {
						$config[$name] = array($config[$name]);
					}

					$config[$name][] = $attribute;
				} else {
					$config[$name] = $attribute;
				}
			}
		}

		if (count($xml->children()) > 0) {
			foreach($xml as $elementName => $element) {
				if (count($element->children()) > 0) {
					$element = $this->_toArray($element);
				} else if (count($element->attributes()) > 0) {
					if (count($element->attributes()) == 1 && $element->attributes()->value) {
						$element = (string) $element->attributes()->value;
					} else {
						$element = $this->_toArray($element);
					}
				} else {
					$element = (string) $element;
				}

				if (isset($config[$elementName])) {
					if (!is_array($config[$elementName]) || !isset($config[$elementName][0])) {
						$config[$elementName] = array($config[$elementName]);
					}
					$config[$elementName][] = $element;
				} else {
					$config[$elementName] = $element;
				}
			}
		} else if (!isset($config['value']) && ((string) $xml !== '')) {
			$config['value'] = (string) $xml;
		}

		return $config;
	}

	/**
	 * @return array|string
	 */
	public function toArray() {
		return $this->_data;
	}
}
