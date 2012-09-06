<?php

abstract class CManager_Db_Table_Abstract extends Zend_Db_Table_Abstract {
	/**
	 * @var string
	 */
	protected $_scopeAlias = null;

	public function __construct($config = array()) {
		if ($this->_scopeAlias) {
			$config['db'] = CManager_Db_Manager::getConnectionByScope($this->_scopeAlias)->getAdapter();
		} else if ((is_array($config) || $config instanceof ArrayAccess) && !isset($config['db'])) {
			$config['db'] = CManager_Db_Manager::getConnectionByScope('default')->getAdapter();
		}

		parent::__construct($config);

		if ($this->getRowClass() == 'Zend_Db_Table_Row') {
			$this->setRowClass('CManager_Db_Table_Row');
		}
		if ($this->getRowsetClass() == 'Zend_Db_Table_Rowset') {
			$this->setRowsetClass('CManager_Db_Table_Rowset');
		}
	}

	/**
	 * @param string $alias
	 */
	public function switchAdapter($alias = null) {
		$this->setOptions(array(
			Zend_Db_Table_Abstract::ADAPTER => CManager_Db_Manager::getConnectionByScope($alias)->getAdapter()
		));
	}

	/**
	 * @param string $expression
	 * @return Zend_Db_Expr
	 */
	public function newExpr($expression) {
		return new Zend_Db_Expr($expression);
	}

	/**
	 * @param string $scope
	 * @return mixed|Zend_Db_Adapter_Abstract
	 */
	public function getAdapterByScope($scope) {
		return CManager_Db_Manager::getConnectionByScope($alias)->getAdapter();
	}

	public function insert($data, $check = true) {
		$data = $check? $this->_filterInvalidFields($data): $data;
		return parent::insert($data);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function _filterInvalidFields($data) {
		$cols = $this->info('cols');
		foreach ($data as $name => $value) {
			if (!in_array($name, $cols)) {
				unset($data[$name]);
			}
		}
		return $data;
	}
}
