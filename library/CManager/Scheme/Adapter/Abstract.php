<?php

abstract class CManager_Scheme_Adapter_Abstract {
	/**
	 * @abstract
	 * @param string $name
	 * @return CManager_Scheme_Adapter_Abstract[]|CManager_Scheme_Adapter_Abstract|string|null
	 */
	abstract public function get($name);
}
