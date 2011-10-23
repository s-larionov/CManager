<?php

class cm_DOM_Element extends DOMElement {
	/**
	 * @param string
	 * @param string
	 */
	public function __construct($name, $namespaceURI = null) {
		parent::__construct($name, null, $namespaceURI);
	}

	/**
	 * @param string $name
	 * @param string $namespaceURI
	 * @return cm_DOM_Document
	 */
	public function cmCreateElement($name, $namespaceURI = null) {
		return null !== $namespaceURI
			? $this->ownerDocument->createElementNS($namespaceURI, $name)
			: $this->ownerDocument->createElement($name);
	}

	/**
	 * @param string
	 * @param string
	 * @return cmDOMElement
	 */
	public function appendElement($name, $content = null) {
		return $this->appendElementNS($name, null, $content);
	}

	/**
	 * @param string $name
	 * @param string $namespaceURI
	 * @param string $content
	 * @return cm_DOM_Element
	 */
	public function appendElementNS($name, $namespaceURI, $content = null) {
		$element = $this->appendChild(new $this->ownerDocument->extendedClasses['DOMElement']($name, $namespaceURI));
		if ($content !== null) {
			$element->appendChild($this->ownerDocument->createTextNode($content));
		}
		return $element;
	}

	/**
	 * @param string $query
	 * @param array $namespaces
	 * @param boolean $asArray
	 * @return array|DOMNodeList
	 */
	public function xpathEval($query, $namespaces = array(), $asArray = true) {
		return $this->ownerDocument->xpathEval($query, $namespaces, $this, $asArray);
	}
    
	/**
	 * @param string $query
	 * @param array $namespaces
	 * @param boolean $asArray
	 * @return array|DOMNodeList
	 */
	public function xpathQuery($query, $namespaces = array(), $asArray = true) {
		return $this->ownerDocument->xpathQuery($query, $namespaces, $this, $asArray);
	}

	/**
	 * @return string
	 */
	public function saveXML() {
		return $this->ownerDocument->saveXML($this);
	}
}
