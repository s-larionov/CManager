<?php

class CManager_Collection implements CManager_Collection_Interface {
	protected $_collectionData;

	protected $_iteratedCollectionData;
	protected $_intervalOffset = 0;
	protected $_intervalLimit = 0;
	protected $_current;
	protected $_key;
	protected $_xmlName;
	protected $gettedData = array();

	public function __construct($data = array()) {
		$this->_setCollectionData($data);
	}

	public function setInterval($offset, $limit) {
		$this->_intervalOffset = $offset;
		$this->_intervalLimit = $limit;
		$this->_iteratedCollectionData = null;
	}

	protected function _setCollectionData($data) {
		$this->_collectionData = $data;
	}

	protected function _setIteratedCollectionData() {
		if (!is_null($this->_iteratedCollectionData)) {
			return;
		}

		if (!$this->_intervalLimit) {
			$this->_iteratedCollectionData = $this->_collectionData;
			return;
		}

		if ($this->_intervalOffset < 0 || $this->_intervalOffset >= count($this->_collectionData)){
			$this->_iteratedCollectionData = array();
			return;
		}

		$to_splice_array = $this->_collectionData;
		$this->_iteratedCollectionData = array_splice($to_splice_array, $this->offset, $this->limit);

		if (!$this->_iteratedCollectionData) {
			$this->_iteratedCollectionData = array();
		}
	}

	/* Iterator methods */

	public function next() {
		$this->_setIteratedCollectionData();

		$this->_current = next($this->_iteratedCollectionData);
		$this->_key = key($this->_iteratedCollectionData);
	}

	public function rewind() {
		$this->_setIteratedCollectionData();

		$this->_current = reset($this->_iteratedCollectionData);
		$this->_key = key($this->_iteratedCollectionData);
	}

	public function current() {
		return $this->_current;
	}

	public function key() {
		return $this->_key;
	}

	public function valid() {
		return $this->_current !== false;
	}

	/* Countable methods */

	public function count() {
		return count($this->_collectionData);
	}

	/* ArrayAccess methods */

	public function offsetSet($offset, $value) {
		if (is_array($value)) {
			$class = strpos(strtolower(get_class($this)), 'CManager_config') === 0 ? 'CManager_Config' : get_class($this);
			$value = new $class($value);
		}
		$this->_collectionData[$offset] = $value;
	}

	public function offsetGet($offset, $throw = false) {
		if (!isset($this->_collectionData[$offset])) {
			if ($throw) {
				throw new CManager_Collection_Exception("Undefined offset $offset.");
			}
			return null;
		}
		if (is_array($this->_collectionData[$offset]) && !is_object($this->_collectionData[$offset])) {
			$class = strpos(strtolower(get_class($this)), 'CManager_config') === 0 ? 'CManager_Config' : get_class($this);
			$this->_collectionData[$offset] = new $class($this->_collectionData[$offset]);
		}
		return $this->_collectionData[$offset];
	}

	public function offsetUnset($offset) {
		if (!isset($this->_collectionData[$offset])) {
			return false;
		}
		unset($this->_collectionData[$offset]);
		return true;
	}

	public function offsetExists($offset) {
		return isset($this->_collectionData[$offset]) || array_key_exists($offset, $this->_collectionData);;
	}

	/**/

	public function push($value) {
		$this->_collectionData[] = $value;
	}

	public function exists($offset) {
		return $this->offsetExists($offset);
	}

	private function __camelize($string, $separ = '_') {
		$part = explode($separ, $string);
		if (count($part) === 1) {return $string;}
		if (strpos($string, $separ) === 0) {$part[0] = ucwords($part[0]);}
		for ($i=1;$i<count($part);$i++) {$part[$i] = ucwords($part[$i]);}
		return implode('', $part);
	}

	public function __get($property) {
		if (!isset($this->_collectionData[$property])) {
			return false;
		}
		$method = $this->__camelize('get_'. $property);
		if (method_exists($this, $method)) {
			if (!isset($this->gettedData[$property])) {
				$this->gettedData[$property] = $this->$method();
			}
			return $this->gettedData[$property];
		}
		return $this->_collectionData[$property];
	}

	public function __set($property, $value) {
		if (!isset($this->_collectionData[$property])) {
			return false;
		}
		$method = $this->__camelize('get_'. $property);
		if (method_exists($this, $method)) {
			$this->_collectionData[$property] = $this->$method($value);
		}
		$this->_collectionData[$property] = $value;
		return true;
	}

	public function hasKeys() {
		return array_keys($this->_collectionData)
						!== range(0, count($this->_collectionData) - 1);
	}

	public function setLabel($value) {
		$this->_xmlName = $value;
	}

	public function getLabel() {
		return $this->_xmlName;
	}

	public function toXML() {
		return $this->toArrayXML('element');
	}

	function toArrayXML($root = 'element') {
		$str = '<'. $root . ($this->getLabel() !== null ? ' name="'. $this->getLabel() .'"' : '') .'>';

		if (!empty($this->_collectionData)) {
			$hasKeys = $this->hasKeys();
			foreach ($this->_collectionData as $key => $item) {
				if (is_array($item)) {
					$item = new CManager_Collection($item);
					if ($hasKeys) {
						$item->setLabel($key);
					}
				}

				if (!$item && !is_string($item)) {
					$str .= '<attr'. ($hasKeys? ' name="'. $key .'"' : '') .'/>';
				}
				else if (!is_object($item)) {
					$str .= '<attr'. ($hasKeys? ' name="'. $key .'"' : '') .'>';
					$str .= '<![CDATA['. (string) $item .']]>';
					$str .= '</attr>';
				}
				elseif (method_exists($item, 'toArrayXML')) {
					$str .= $item->toArrayXML('attr');
				}

			}
		}

		$str .= '</'. $root .'>';

		return $str;
	}

	public function toArray() {
		return $this->_collectionData;
	}

	public function __toString() {
		return $this->toXML();
	}
}