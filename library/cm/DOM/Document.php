<?php

class cm_DOM_Document extends DOMDocument {
	/**
	 * @var array
	 */
	public $extendedClasses = array(
		'DOMElement' => 'cm_DOM_Element'
	);

	public function __construct() {
		parent::__construct('1.0', 'utf-8');
		foreach ($this->extendedClasses as $orig => $ext) {
			$this->registerNodeClass($orig, $ext);
		}
	}

	/**
	 * @param string $rootName
	 * @param string $namespaceURI
	 * @return cm_DOM_Element
	 */
	public function setRoot($rootName = 'root', $namespaceURI = null) {
		$className = $this->extendedClasses['DOMElement'];
		return $this->appendChild(new $className($rootName, $namespaceURI));
	}

	/**
	 * @param string
	 * @return cm_DOM_Document
	 * @throws cm_DOM_Exception
	 */
	public function loadXML($xmlString) {
		if (!isset($this) || get_class($this) !== 'cm_DOM_Document') {
			// если метод вызван как статический
			$doc = new cm_DOM_Document;
			$doc->loadXML($xmlString);

			if (count($error = libxml_get_errors())) {

				throw new cm_DOM_Exception($error, $xmlString);
			}

			return $doc;
		}

		parent::loadXML($xmlString);

		if (count($error = libxml_get_errors())) {
			throw new cm_DOM_Exception($error, $xmlString);
		}

		return $this;
	}

	/**
	 * @param string
	 * @param array
	 * @param cm_DOM_Element
	 * @param boolean
	 * @return cm_DOM_Document
	 */
	public function schemaValidateSource($source) {
		parent::schemaValidateSource($source);
		self::checkSchemaValidationErrors();
	}

	/**
	 * @param string $query
	 * @param array $namespaces
	 * @param cm_DOM_Element $context
	 * @param bool $asArray
	 * @return DOMNodeList|cm_DOM_Element[]
	 */
	public function xpathQuery($query, $namespaces = array(), $context = null, $asArray = true) {
		if ($context === null) {
			$context = $this;
		}
		$xpath = new DOMXPath($this);
		foreach ($namespaces as $prefix => $uri) {
			$xpath->registerNamespace($prefix, $uri);
		}
		$result = $xpath->evaluate($query, $context);

		if (count($error = libxml_get_errors())) {

			throw new cm_DOM_XPathException($error, $query);
		}

		return get_class($result) == 'DOMNodeList' && $asArray ? $this->nodeListToArray($result) : $result;
	}

	/**
	 * @param string $query
	 * @param array $namespaces
	 * @param cm_DOM_Element $context
	 * @param bool $asArray
	 * @return DOMNodeList|cm_DOM_Element[]
	 */
	public function xpathEval($query, $namespaces = array(), $context = null, $asArray = true) {
		return $this->xpathQuery($query, $namespaces, $context, $asArray);
	}

	/**
	 * @param string|array $xslFiles
	 * @param array $options
	 * @param array $includes
	 * @param bool $xmlContent
	 * @param bool $showDTD
	 * @return string
	 * @throws cm_DOM_Exception
	 */
	public function xslTransform($xslFiles, $options = array(), $includes = array(), $xmlContent = false, $showDTD = false) {
		$transformed = false;

		foreach ((array) $xslFiles as $xslFile) {
			if (!file_exists($xslFile)) {

				throw new cm_DOM_Exception("Файл {$xslFile} не найден.");
			}

			$xslFile = str_replace("\\", '/', $xslFile);

			$xslDoc = new cm_DOM_Document();
			/*
			* следующие две строчки необходимы, что бы в xsl работали такие конструкции:
			* <xsl:template match="&entity;">....
			*/
			$xslDoc->resolveExternals = true;
			$xslDoc->substituteEntities = true;

			$xslDoc->load($xslFile);
			$xslDoc->documentURI = $xslFile;

			foreach ($includes as $_include) {
				$include = $xslDoc->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:include');
				$_include = str_replace(array("\\", ' '), array('/', '%20'), $_include);

				if (strpos($_include, ':/') === 1) {
					$_include = 'file:///'. $_include;
				}

				$include->setAttribute('href', $_include);
				$xslDoc->firstChild->insertBefore($include, $xslDoc->firstChild->firstChild->nextSibling);
			}

			$proc = new XSLTProcessor();

			$proc->registerPHPFunctions();

			$proc->importStyleSheet($xslDoc);

			foreach ((array)$options as $name => $value) {
				$proc->setParameter('', $name, $value);
			}

			if (count($error = libxml_get_errors())) {
				throw new cm_DOM_XSLTException($error);
			}

			if ($xmlContent !== false) {
				$xmlDoc = cm_DOM_Document::loadXML($xmlContent);
				$result = $proc->transformToXML($xmlDoc);
			} else {
				$result = $proc->transformToXML($this);
			}

			if (count($error = libxml_get_errors())) {

				throw new cm_DOM_XSLTException($error);
			}

			$xmlContent = trim($showDTD === false ? preg_replace("/(<!DOCTYPE[^>]*>)/i", "", $result) : $result);
			$transformed = true;
		}

		return !$transformed ? $transformed : $xmlContent;
	}

	/**
	 * @static
	 * @param array|string $xslFiles
	 * @param bool $xmlContent
	 * @param array $options
	 * @param array $includes
	 * @param bool $showDTD
	 * @return string
	 */
	public static function xslTransformSource($xslFiles, $xmlContent = false, $options = array(), $includes = array(), $showDTD = false) {
		return cm_DOM_Document::xslTransform($xslFiles, $options, $includes, $xmlContent, $showDTD);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$str = $this->saveXML();
		return substr($str, strpos($str, '>', 2)+1);
	}

	/**
	 * @param DOMNodeList
	 * @return array
	 */
	private function nodeListToArray(DOMNodeList $list) {
		$array = array();
		foreach ($list as $item) {
			$array[] = $item;
		}
		return $array;
	}

	/**
	 * @static
	 * @param bool $xmlString
	 * @throws cm_DOM_Exception
	 */
	private static function checkErrors($xmlString = false) {
		if (count($error = libxml_get_errors())) {
			throw new cm_DOM_Exception($error, $xmlString);
		}
	}

	/**
	 * @static
	 * @param bool $query
	 * @throws cm_DOM_XPathException
	 */
	private static function checkXPathErrors($query = false) {
		if (count($error = libxml_get_errors())) {
			throw new cm_DOM_XPathException($error, $query);
		}
	}

	/**
	 * @static
	 * @throws cm_DOM_XSLTException
	 */
	private static function checkXSLTErrors() {
		if (count($error = libxml_get_errors())) {
			throw new cm_DOM_XSLTException($error);
		}
	}

	/**
	 * @static
	 * @throws cm_DOM_SchemaValidationException
	 */
	private static function checkSchemaValidationErrors() {
		if (count($error = libxml_get_errors())) {
			throw new cm_DOM_SchemaValidationException($error);
		}
	}
}
