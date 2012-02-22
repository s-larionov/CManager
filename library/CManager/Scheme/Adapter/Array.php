<?php

class CManager_Scheme_Adapter_Array extends CManager_Scheme_Adapter_Abstract {
	/**
	 * @var array
	 */
	protected $array;

	public function __construct(array $array) {
		$this->array = $array;
	}

	/**
	 * @param string $name
	 * @return CManager_Scheme_Adapter_Abstract[]|CManager_Scheme_Adapter_Abstract|string|null
	 */
	public function get($name) {
		if (array_key_exists($name, $this->array)) {
			if (is_array($this->array[$name])) {
				if (CManager_Helper_Array::isNumberedArray($this->array[$name])) {
					$subItem = array();
					foreach($this->array[$name] as $key => $item) {
						$subItem[$key] = new self($item);
					}
					return $subItem;
				} else {
					return new self($this->array[$name]);
				}
			}
			return $this->array[$name];
		}
		return null;
	}
}
