<?php

class Mods_Breadcrumbs extends CManager_Controller_Action_Abstract {
	public function run() {
		try {
			$breadcrumbs = (array) CManager_Registry::get('breadcrumbs');
			$xml = CManager_Helper_Xml::parse('breadcrumbs', $breadcrumbs, array(), array(
				'rowElementName'	=> 'page',
				'keysAsElements'	=> false,
				'elementAttributes'	=> array('href', 'name')
			));
//			echo nl2br(htmlspecialchars($xml));
			$this->sendContent(CManager_Dom_Document::xslTransformSource($this->getParam('xsl', ''), $xml));
		} catch (CManager_Registry_Exception $e) { }
	}
}