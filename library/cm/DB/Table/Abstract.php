<?php

abstract class cm_DB_Table_Abstract extends Zend_Db_Table_Abstract {
	/**
	 * @var string
	 */
	protected $_connectionAlias = null;

	public function __construct($config = array()) {
		if ($this->_connectionAlias) {
			$config['db'] = cm_DB_Manager::connection($this->_connectionAlias)->getAdapter();
		} else if ((is_array($config) || $config instanceof ArrayAccess) && !isset($config['db'])) {
			$config['db'] = cm_DB_Manager::connection('default')->getAdapter();
		}

		parent::__construct($config);

		if ($this->getRowClass() == 'Zend_Db_Table_Row') {
			$this->setRowClass('cm_DB_Table_Row');
		}
		if ($this->getRowsetClass() == 'Zend_Db_Table_Rowset') {
			$this->setRowsetClass('cm_DB_Table_Rowset');
		}
	}

	/**
	 * @param string $alias
	 */
	public function switchAdapter($alias = null) {
		$this->setOptions(array(
			Zend_Db_Table_Abstract::ADAPTER => cm_DB_Manager::connection($alias)->getAdapter()
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
	 * @param string $alias
	 * @return mixed|Zend_Db_Adapter_Abstract
	 */
	public function getAdapterByAlias($alias) {
		return cm_DB_Manager::connection($alias)->getAdapter();
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