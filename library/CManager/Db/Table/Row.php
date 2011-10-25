<?php

class CManager_Db_Table_Row extends Zend_Db_Table_Row_Abstract {
	public function toXML($keyAsName = false) {
		$str = '';
		foreach ($this->_data as $field => $value) {
			$value = is_numeric($value) || empty($value) ? $value : '<![CDATA['. $value .']]>';
			if ($keyAsName) {
				$str .= "<{$field}>{$value}</{$field}>";
			} else {
				$str .= '<field alias="'. $field .'">'. $value .'</field>';
			}
		}
		return '<row>'. $str .'</row>';
	}


	/**
	 * @return int
	 * @throws Zend_Db_Table_Row_Exception
	 */
	public function delete() {
		//A read-only row cannot be deleted.
		if ($this->_readOnly) {
			throw new Zend_Db_Table_Row_Exception('This row has been marked read-only');
		}

		$where = $this->_getWhereQuery();

		/**
		 * Execute pre-DELETE logic
		 */
		$this->_delete();

		/**
		 * Execute cascading deletes against dependent tables
		 */
		$depTables = $this->_getTable()->getDependentTables();
		if (!empty($depTables)) {
			$adapter	= $this->_getTable()->getAdapter();
			$primaryKey	= $this->_getPrimaryKey();
			foreach ($depTables as $tableClass) {
				if (is_object($tableClass) && $tableClass instanceof CManager_Db_Table_Abstract) {
					$table = $tableClass;
				} else {
					if (!class_exists($tableClass)) {
						throw new Zend_Db_Table_Row_Exception("Class {$tableClass} doesn't exists");
					}
					$table = new $tableClass(array('db' => $adapter));
				}
				$table->_cascadeDelete($this->getTableClass(), $primaryKey);
			}
		}

		/**
		 * Execute the DELETE (this may throw an exception)
		 */
		$result = $this->_getTable()->delete($where);

		/**
		 * Execute post-DELETE logic
		 */
		$this->_postDelete();

		/**
		 * Reset all fields to null to indicate that the row is not there
		 */
		$this->_data = array_combine(
			array_keys($this->_data),
			array_fill(0, count($this->_data), null)
		);

		return $result;
	}
}