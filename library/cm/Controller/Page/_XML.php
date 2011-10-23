<?php

class cm_Controller_Page_XML extends cm_Controller_Page_Abstract {
	/**
	 * @var SimpleXMLElement
	 */
	protected $_page = null;

	/**
	 * @param SimpleXMLElement $page
	 */
	public function __construct(SimpleXMLElement $page) {
		$this->_page = $page;
		try {
			if (!$this->_page->attributes()->current) {
				$this->_page->addAttribute('current', 'current');
			}
		} catch (cm_Exception $e) {}
	}

	/**
	 * @return SimpleXMLElement
	 */
	public function getStructure() {
		return $this->_page;
	}

	/**
	 * @param string $name
	 * @param string $namespace
	 * @param string $mode
	 * @param array|Zend_Config $params
	 * @param SimpleXMLElement $xml
	 * @return cm_Controller_Tag_XML
	 */
	public function createTag($name, $namespace, $mode, $params = null, $xml = null) {
		return new cm_Controller_Tag_XML($name, $namespace, $mode, $params, $xml);
	}

	/**
	 * @param SimpleXMLElement $params
	 * @return array
	 */
	protected function _convertXmlParamsToArray(SimpleXMLElement $params) {
		$ret = array();

		foreach($params->param as $param) {
			$key = (string) $param->attributes()->name;

			if ($param->param->count()) {
				$val = $this->_convertXmlParamsToArray($param);
			} else {
				$val = (string) $param;
			}

			if (empty($key) && empty($val)) {
				// не нужен такой param :)
			} elseif (!$key) {
				$ret[] = $val;
			} else {
				$ret[$key] = $val;
			}
		}

		return $ret;
	}

	/**
	 * @return void
	 */
	protected function _addTags() {
		// выбираем тэги

		$code = (string)$this->getStructure()->attributes()->error_code;
		$xpath = !$this->getRequest()->hasExtraPath() || $code ?
			'tag | ancestor::*/tag[@pass and not(starts-with(@pass, "local-"))]' :
			'tag[@pass] | ancestor::*/tag[@pass and not(starts-with(@pass, "local-"))]';

		$tags = $this->getStructure()->xpath($xpath);

		// выбираем исключения
		$exclusions = $this->getStructure()->xpath('tag_exclusion | ancestor::*/tag_exclusion[@pass]');
		foreach ($exclusions as &$exclusion) {
			$exclusion = (string)$exclusion->attributes()->name;
		}

		foreach ($tags as $tag) {
			$tagName = (string) $tag->attributes()->name;
			$tagPass = (string) $tag->attributes()->pass;
			if (strpos($tagPass, 'local-') === 0) {
				$tagPass = substr($tagPass, 6);
			}

			// если тэг существует в масиве исключений,
			// идем дальше
			$isCurrent = (boolean)$tag->xpath('ancestor::page[1]/@current') && !$this->getRequest()->hasExtraPath();
			if ((in_array($tagName, $exclusions) && !$isCurrent) || ($tagPass == 'overpass' && $isCurrent)) {
				continue;
			}

			// обрабатываем параметры
			//$params = new cm_Config_XML($tag->asXML(), true);
			$params = $this->_convertXmlParamsToArray($tag);

			// определяем режим работы
			switch ((string)$tag->attributes()->mode) {
				case 'background':
					$mode = cm_Controller_Tag::MODE_BACKGROUND;
				break;
				case 'action':
					$mode = cm_Controller_Tag::MODE_ACTION;
				break;
				case 'normal':
				default:
					$mode = cm_Controller_Tag::MODE_NORMAL;
				break;
			}

			// создаем объект
			$this->addTag(
				$this->createTag(
					(string) $tagName,
					(string) $tag->attributes()->namespace,
					$mode,
					$params,
					$tag
				)
			);
		}

	}

	/**
	 * @return string
	 */
	protected function _getLayout() {
		if (!$layout = (string) $this->_page->attributes()->layout) {
			$layout = $this->_page->xpath('ancestor::*[@layout][1]');
			return (string) $layout[0]->attributes()->layout;
		}
		return $layout;
	}

	/**
	 * @return string
	 */
	protected function _getTitle() {
		if ($this->_page->attributes()->title == 'no') {
			$page = $this->_page->xpath("ancestor::page[not(@title) or @title = 'yes'][1]");
			if (!count($page)) {
				return '';
			}
			$title = (string)$page[0]->children()->title;
		} else {
			$title = (string)$this->_page->children()->title;
		}

		return $title;
	}

	/**
	 * @return bool|string
	 */
	public function getRedirectUrl() {
		if ($this->getRequest()->hasExtraPath()) {
			return false;
		}

		$url = (string) $this->_page->attributes()->redirect;
		if (!$url) {
			return false;
		}

		if (strpos($url, '/') !== 0) {
			$url = rtrim($this->getRequest()->getRawRequestUri(false), '/') .'/'. $url;
		}

		return $url;
	}
}