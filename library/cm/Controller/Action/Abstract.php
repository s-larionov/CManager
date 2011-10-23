<?php

abstract class cm_Controller_Action_Abstract extends cm_Controller_Abstract {

	/**
	 * @var Zend_Config
	 */
	protected $params;

	/**
	 * @var cm_Controller_Router_Abstract
	 */
	protected $_router = null;

	/**
	 * @param Zend_Config $params
	 * @param cm_Controller_Request_Abstract $request
	 * @param cm_Controller_Response_Abstract $response
	 */
	public function __construct(Zend_Config $params, $request = null, $response = null) {
		parent::__construct($request, $response);
		$this->params = $params;
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
	 *	@return cm_Controller_Router_Abstract
	 */
	protected function getRouter() {
		if ($this->_router === null) {
			$this->_router = cm_Registry::getFrontController()->getRouter();
		}
		return $this->_router;
	}

	/**
	 *	Возвращает объект текущей страницы.
	 *
	 *	@return cm_Controller_Page_Abstract
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
		return $this->params->get($name, $default);
	}

	/**
	 * Возвращает значение входящего параметра по имени.
	 *
	 * @param string  $name   имя параметра
	 * @param mixed   $value
	 * @return void
	 */
	protected function setParam($name, $value = null) {
		$this->params->$name = $value;
	}

	/**
	 * @return Zend_Config
	 */
	protected function getParams() {
	    return $this->params;
	}

	/**
	 * Проверяет существование параметра.
	 *
	 * @param string $name
	 * @return boolean
	 */
	protected function hasParam($name) {
		return ($this->params->get($name, null) !== null);
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
	 *	@return cm_Controller_Tag
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
		throw new cm_Controller_Action_DoneException($content);
	}
}