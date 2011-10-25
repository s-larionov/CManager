<?php

class CManager_Db_Table_Rowset extends Zend_Db_Table_Rowset_Abstract {
	public function toXML($name = null, $keyAsName = false) {
		if (empty($name)) {
			$name = $this->getTable()->info('name');
		}

		$str = '';
		foreach ($this as $row) {
			$str .= $row->toXML($keyAsName);
		}
		return '<'. $name .'>'. $str .'</'. $name .'>';
	}
}