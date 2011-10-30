<?php

class Mods_Navigation_Item {
	/**
	 * @var string
	 */
	protected $_navigationName = '';

	/**
	 * @var array
	 */
	protected $_data = array();

	/**
	 * @var Mods_Navigation_Item[]
	 */
	protected $_subItems = array();

	/**
	 * @var Mods_Navigation_Item
	 */
	protected $_parent = null;

	/**
	 * @param string $navigationName
	 * @param array $config
	 */
	public function __construct($navigationName, array $config) {
		$this->_isRequired($config, array('name', 'title', 'url'));
		$this->_navigationName = (string) $navigationName;
		foreach($config as $name => $value) {
			$this->set($name, $value);
		}
	}

	/**
	 * @param array $array
	 * @param array $fields
	 * @throws Mods_Navigation_Item_Exception
	 */
	protected function _isRequired(array $array, array $fields) {
		foreach($fields as $field) {
			if (!array_key_exists($field, $array)) {
				throw new Mods_Navigation_Item_Exception("Field {$field} is required");
			}
		}
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return Mods_Navigation_Item
	 */
	public function set($name, $value) {
		// преобразование типов входных параметров
		switch (true) {
			case in_array($name, array('current', 'here')):
				$value = (boolean) $value;
				break;
			case $name == 'title':
				if (is_array($value)){
					if (!array_key_exists(0, $value)) {
						$value = array($value);
					}
				} else {
					$value = (string) $value;
				}
				if (is_string($value)) {
					$value = array(
						new Mods_Navigation_ItemTitle($value)
					);
				} else {
					foreach($value as &$title) {
						if (!array_key_exists('value', $title)) {
							continue;
						}
						if (isset($title['mode'])) {
							$title = new Mods_Navigation_ItemTitle($title['value'], $title['mode']);
						} else {
							$title = new Mods_Navigation_ItemTitle($title['value']);
						}
					}
				}
				break;
			default:
				$value = (string) $value;
		}

		if ($name == 'current') {
			$this->here = $value;
		} else if (($name == 'here') && $value === true && ($parent = $this->getParent())) {
			$this->getParent()->here = $value;
		}

		$this->_data[$name] = $value;
		if ($this->_navigationName == 'right') {
//			var_dump($name);
//			var_dump($value);
		}
		return $this;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) {
		$this->set($name, $value);
	}

	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($name, $default = null) {
		if (array_key_exists($name, $this->_data)) {
			return $this->_data[$name];
		}
		return $default;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		return $this->get($name);
	}

	/**
	 * @param Mods_Navigation_Item $item
	 * @param string $before
	 * @return Mods_Navigation_Item
	 */
	public function addSubItem(Mods_Navigation_Item $item, $before = '') {
		$item->setParent($this);
		if ($before != '') {
			foreach($this->_subItems as $i => &$subItem) {
				if ($subItem->name == $before) {
					return $this->insertSubItem($item, $i);
				}
			}
		}
		$this->_subItems[] = $item;
		return $this;
	}

	/**
	 * @param Mods_Navigation_Item $item
	 * @param int $index
	 * @return Mods_Navigation_Item
	 */
	public function insertSubItem(Mods_Navigation_Item $item, $index) {
		$item->setParent($this);
		if ($index >= 0 && array_key_exists($index, $this->_subItems)) {
			array_splice($this->_subItems, $index, 0, array($item));
			return $this;
		}
		$this->addSubItem($item);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getNavigationName() {
		return $this->_navigationName;
	}

	/**
	 * @return Mods_Navigation_Item[]
	 */
	public function getSubItems() {
		return $this->_subItems;
	}

	/**
	 * @param string $name
	 * @return Mods_Navigation_Item|null
	 */
	public function getSubItem($name) {
		foreach($this->_subItems as $item) {
			if ($item->name == $name) {
				return $item;
			}
		}
		return null;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		$array = $this->_data;

		$array['items'] = array();
		foreach($this->_subItems as $i => $item) {
			$array['items'][$i] = $item->toArray();
		}

		return $array;
	}

	/**
	 * @return Mods_Navigation_Item|null
	 */
	public function getParent() {
		return $this->_parent;
	}

	/**
	 * @param Mods_Navigation_Item $parent
	 * @return Mods_Navigation_Item
	 */
	public function setParent(Mods_Navigation_Item $parent) {
		$this->_parent = $parent;
		if ($this->isHere() === true) {
			$this->_parent->here = true;
		}
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isHere() {
		if ($this->current === true || $this->here === true) {
			return true;
		}
		foreach($this->_subItems as $item) {
			if ($item->isHere()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return string
	 */
	public function toXml() {
		$attributes = $this->_data;
		unset($attributes['title']);

		if (isset($attributes['current']) && !$attributes['current']) {
			unset($attributes['current']);
		}
		if (isset($attributes['here']) && !$attributes['here']) {
			unset($attributes['here']);
		}

		if (!isset($this->_data['title'])) {
			var_dump($this->_data);
		}
		return CManager_Helper_Xml::parse('navigation', array(
			'titles' => $this->_data['title'],
			'items' => $this->getSubItems()
		), $attributes);
	}
}