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
	 * @param string $navigationName
	 * @param array $config
	 */
	public function __construct($navigationName, array $config) {
		$this->_isRequired($config, array('name', 'title', 'url'));
		foreach($config as $name => $value) {
			$this->set($name, $value);
		}
		$this->_navigationName = (string) $navigationName;
	}

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
						if (!isset($title['value'])) {
							return;
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
		$this->_data[$name] = $value;
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
	 * @param string $name
	 * @param Mods_Navigation_Item $item
	 * @return Mods_Navigation_Item
	 */
	public function addSubItem($name, Mods_Navigation_Item $item) {
		$this->_subItems[] = $item;
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
	 * @return string
	 */
	public function toXml() {
		$attributes = $this->_data;
		unset($attributes['title']);
		if (isset($this->_data['current']) && !$this->_data['current']) {
			unset($this->_data['current']);
		}
		return CManager_Helper_Xml::parse('navigation', array(
			'titles' => $this->_data['title'],
			'items' => $this->getSubItems()
		), $attributes);
	}
}