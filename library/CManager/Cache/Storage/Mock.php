<?php

class CManager_Cache_Storage_Mock extends CManager_Cache_Storage_Abstract {
	/**
	 * Получить распакованное содержимое ключу (array('content' => ..., 'properties' => array(...))
	 *
	 * @param string $key
	 * @return array
	 */
	protected function _get($key) {
		return $this->_unpackContent('');
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function delete($key) {
		return false;
	}

	/**
	 * Сохранить данные в кеш по определенному ключу.
	 *
	 * @param string       $key
	 * @param mixed        $data
	 * @param int|null     $ttl
	 * @param string|null  $validateHash	Дополнительная инвалидация кеша. Строка уникальная для внешних факторов, влияющих
	 *										на актуальность данных по этому ключу.
	 * @return mixed
	 */
	public function save($key, $data, $ttl = null, $validateHash = null) {
		return false;
	}
}
