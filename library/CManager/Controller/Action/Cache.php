<?php

abstract class CManager_Controller_Action_Cache extends CManager_Controller_Action_Abstract {
	/**
	 * @var boolean
	 */
	protected $_cacheEnabled = false;

	/**
	 * @var string|null
	 */
	protected $_cacheKey = null;

	/**
	 * Time to live for cache in seconds
	 *
	 * @var int|null
	 */
	protected $_cacheTtl = null;

	/**
	 * Hash for additional invalidate cache
	 *
	 * @var string|null
	 */
	protected $_cacheValidateHash = null;

	/**
	 * @var string
	 */
	protected $_cacheScope = CManager_Cache_Manager::SCOPE_DEFAULT;

	/**
	 * @var boolean
	 */
	protected $_loadedFromCache = false;

	/**
	 * If found data in cache throw special exception
	 *
	 * @return void
	 */
	public function tryLoadFromCache() {
		if ($this->isCacheEnabled() && ($content = $this->getCacheStorage()->load($this->getCacheKey(), null, $this->getCacheValidateHash()))) {
			$this->isLoadedFromCache(true);
			parent::sendContent($content);
		}
	}

	/**
	 * @param string $content
	 * @return void
	 */
	public function sendContent($content = null) {
		if ($this->isCacheEnabled() && !$this->isLoadedFromCache()) {
			$this->getCacheStorage()->save($this->getCacheKey(), $content, $this->getCacheTtl(), $this->getCacheValidateHash());
		}
		parent::sendContent($content);
	}

	/**
	 * @return CManager_Cache_Storage_Abstract
	 */
	protected function getCacheStorage() {
		return CManager_Cache_Manager::getConnectionByScope($this->getCacheScope());
	}

	/**
	 * @return string
	 */
	public function getCacheScope() {
		return $this->_cacheScope;
	}

	/**
	 * @param string $cacheScope
	 * @return CManager_Controller_Action_Cache
	 */
	public function setCacheScope($cacheScope) {
		$this->_cacheScope = (string) $cacheScope;
		return $this;
	}

	/**
	 * @param boolean|null $flag
	 * @return boolean
	 */
	public function isCacheEnabled($flag = null) {
		if ($flag !== null) {
			$this->_cacheEnabled = (bool) $flag;
		}
		return $this->_cacheEnabled;
	}

	/**
	 * @param boolean|null $flag
	 * @return boolean
	 */
	public function isLoadedFromCache($flag = null) {
		if ($flag !== null) {
			$this->_loadedFromCache = (bool) $flag;
		}
		return $this->_loadedFromCache;
	}

	/**
	 * @return string
	 */
	final public function getCacheKey() {
		if ($this->_cacheKey === null) {
			$this->_cacheKey = (string) $this->_getCacheKey();
		}
		return $this->_cacheKey;
	}

	/**
	 * @return string
	 */
	protected function _getCacheKey() {
		return "[*][{$this->getRequest()->getRequestUri()}][{$this->getTag()->name}]";
	}

	/**
	 * @return int
	 */
	final public function getCacheTtl() {
		if ($this->_cacheTtl === null) {
			$this->_cacheTtl = (int) $this->_getCacheTtl();
		}
		return $this->_cacheTtl;
	}

	/**
	 * @return int
	 */
	protected function _getCacheTtl() {
		return 0;
	}

	/**
	 * @return string
	 */
	final public function getCacheValidateHash() {
		if ($this->_cacheValidateHash === null) {
			$this->_cacheValidateHash = (string) $this->_getCacheValidateHash();
		}
		return $this->_cacheValidateHash;
	}

	/**
	 * @return string|null
	 */
	protected function _getCacheValidateHash() {
		return '';
	}
}
