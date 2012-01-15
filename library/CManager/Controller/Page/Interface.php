<?php

interface CManager_Controller_Page_Interface {
	/**
	 * @param CManager_Controller_Router_Config_Page $config
	 * @param CManager_Controller_Request_Abstract|CManager_Controller_Request_Http $request
	 * @param CManager_Controller_Response_Abstract|CManager_Controller_Response_Http $response
	 */
	public function __construct(CManager_Controller_Router_Config_Page $config,
								CManager_Controller_Request_Abstract $request,
								CManager_Controller_Response_Abstract $response);

	public function init();

	public function tryRedirect();
	public function getRedirectUrl();

	public function getCode();

	/**
	 * @param int $code
	 * @return CManager_Controller_Page
	 */
	public function setCode($code);

	public function getLayout();

	/**
	 * @param string $layout
	 * @return CManager_Controller_Page
	 */
	public function setLayout($layout);

	public function getTitle();

	/**
	 * @param string $title
	 * @param string $mode
	 * @return CManager_Controller_Page
	 */
	public function setTitle($title, $mode = 'default');

	public function setVariables(array $variables);
	/**
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setVariable($name, $value);
	public function getVariables();
	/**
	 * @param string $name
	 * @return mixed|CManager_Controller_Route_Var_Abstract
	 */
	public function getVariable($name);

	public function setRoute(CManager_Controller_Route $route);
	public function getRoute();

	/**
	 * Запускает тэги с режимом $mode
	 *
	 * @param string $mode
	 * @return string
	 */
	public function runTagsByMode($mode);
	/**
	 * Запускает тэги с именем $name
	 *
	 * @param string $name
	 * @param string $mode
	 * @return string
	 */
	public function runTagsByName($name, $mode = null);
	/**
	 * Возвращает первый найденный тэг с именем $name
	 *
	 * @param string $name
	 * @param string $mode
	 * @return CManager_Controller_Tag|null
	 */
	public function getTagByName($name, $mode = null);
	/**
	 * Возвращает тэги с именем $name
	 *
	 * @param string $name
	 * @param string $mode
	 * @return array
	 */
	public function getTagsByName($name, $mode = null);
	public function getTags();

	/**
	 * Создает и возвращает тэг
	 *
	 * @param string $name
	 * @param string $namespace
	 * @param string $mode
	 * @param Zend_Config|array $params
	 * @return CManager_Controller_Tag
	 */
	public function createTag($name, $namespace, $mode, $params = null);
	public function addTag(CManager_Controller_Tag $tag);
	/**
	 * Создает из массива и возвращает тэг
	 * @param array $data
	 * @return CManager_Controller_Tag
	 */
	public function unserializeTag($data);
	/**
	 * Добавляет постоянный тэг на страницу. Постоянный тэг будет доступен при следующем
	 * вызове страницы. Вызывается однократно, затем удаляется. Реализуется с помощью сессии.
	 *
	 * @param CManager_Controller_Tag $tag
	 * @param string $path
	 * @return CManager_Controller_Page
	 */
	public function addSessionTag(CManager_Controller_Tag $tag, $path = null);
}