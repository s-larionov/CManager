<?php

class CManager_Scheme_Adapter_Json extends CManager_Scheme_Adapter_Array {
	public function __construct($json) {
		$this->array = @json_decode($json, true);
		if (!is_array($this->array)) {
			throw new CManager_Scheme_Adapter_Exception('Invalid JSON data');
		}
	}
}
