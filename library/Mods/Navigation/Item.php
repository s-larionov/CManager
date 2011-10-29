<?php

class Mods_Navigation_Item extends CManager_EventEmitter {
	/**
	 * @var string
	 */
	protected $_name = '';

	/**
	 * @var string
	 */
	protected $_title = '';

	/**
	 * @var string
	 */
	protected $_url = '';

	/**
	 * @var Mods_Navigation_Item[]
	 */
	protected $_items = array();

	/**
	 * @param string $navigationNname
	 * @param string $title
	 * @param string $url
	 */
	public function __construct($navigationNname, $title, $url) {
		$this->_name	= (string) $navigationNname;
		$this->_title	= (string) $title;
		$this->_url		= (string) $url;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->_url;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->_title;
	}

	/**
	 * @param string $name
	 * @param Mods_Navigation_Item $item
	 * @return Mods_Navigation_Item
	 */
	public function addSubItem($name, Mods_Navigation_Item $item) {
		$this->_items[$name] = $item;
		return $this;
	}

	/**
	 * @param string $name
	 * @return Mods_Navigation_Item|null
	 */
	public function getSubItem($name) {
		if (array_key_exists($name, $this->_items)) {
			return $this->_items[$name];
		}
		return null;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		$array = array(
			'name'	=> $this->getName(),
			'title'	=> $this->getTitle(),
			'url'	=> $this->getUrl(),
			'items'	=> array()
		);

		foreach($this->_items as $name => $item) {
			$array['items'][$name] = $item->toArray();
		}

		return $array;
	}

	/**
	 * @return string
	 */
	public function toXml() {
		$subXml = '';
		foreach($this->_items as $name => $item) {
			$subXml .= $item->toXml();
		}

		if ($subXml != '') {
			$xml = '<navigation'
//					. ' name="' . $this->getName() . '"'
					. ' title="' . $this->getTitle() . '"'
					. ' url="' . $this->getUrl() . '">' . "\n"
					. $subXml
					. "</navigation>\n";
		} else {
			$xml = '<navigation'
//					. ' name="' . $this->getName() . '"'
					. ' title="' . $this->getTitle() . '"'
					. ' url="' . $this->getUrl() . '"/>' . "\n";
		}
		return $xml;
	}
}