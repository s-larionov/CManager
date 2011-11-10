<?php

abstract class CManager_Controller_Action_Abstract extends CManager_Controller_Abstract {

	/**
	 * @var CManager_Controller_Tag
	 */
	protected $_tag = null;

	/**
	 * @var CManager_Controller_Router_Abstract
	 */
	protected $_router = null;

	/**
	 * @param CManager_Controller_Tag $tag
	 * @param CManager_Controller_Request_Abstract $request
	 * @param CManager_Controller_Response_Abstract $response
	 */
	public function __construct(CManager_Controller_Tag $tag, $request = null, $response = null) {
		parent::__construct($request, $response);
		$this->_tag = $tag;
	}

	/**
	 *	Запускает функционал текущего модуля (action-контроллера).
	 *
	 *	@return string
	 */
	abstract public function run();

	/**
	 *	Возвращает роутер системы.
	 *
	 *	@return CManager_Controller_Router_Abstract
	 */
	protected function getRouter() {
		if ($this->_router === null) {
			$this->_router = CManager_Registry::getFrontController()->getRouter();
		}
		return $this->_router;
	}

	/**
	 *	Возвращает объект текущей страницы.
	 *
	 *	@return CManager_Controller_Page
	 */
	protected function getPage() {
		return $this->getRouter()->getPage();
	}

	/**
	 *	Возвращает значение входящего параметра по имени.
	 *
	 *  @param string  $name     имя параметра
	 *  @param mixed   $default  значение по умолчанию
	 *	@return mixed
	 */
	protected function getParam($name, $default = null) {
		return $this->_tag->getParam($name, $default);
	}

	/**
	 * Проверяет существование параметра.
	 *
	 * @param string $name
	 * @return boolean
	 */
	protected function hasParam($name) {
		return ($this->_tag->getParam($name, null) !== null);
	}

	/**
	 * Проверяет существование параметров.
	 *
	 * @param string[]
	 * @return boolean
	 */
	protected function hasParams($params) {
		foreach ((array) $params as $param) {
			if (!$this->hasParam($param)) {
				return false;
			}
		}
		return true;
	}

	/**
	 *	Возвращает тэг текущего объекта контроллера.
	 *
	 *	@return CManager_Controller_Tag
	 */
	protected function getTag() {
		return $this->getParam('tagOwner');
	}

	/**
	 *	После вызова sendContent выбрасывается исключение, которотое говорит
	 *	о том, что модуль (action-контроллер) закончил свою работу и подготовил
	 *	контент.
	 *
	 *	@param string $content контент, который возвращает контроллер
	 *	@throws cmController_Action_DoneException
	 */
	public function sendContent($content = null) {
		throw new CManager_Controller_Action_DoneException($content);
	}
}