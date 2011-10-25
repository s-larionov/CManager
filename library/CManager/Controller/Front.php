<?php

/**
 *	Разбирает урл средствами роутера и на основе полученных
 *	от него данных генерирует ответ.
 */

class CManager_Controller_Front extends CManager_Controller_Abstract {
	/**
	 * @var CManager_Controller_Router_Abstract
	 */
	private $_router;

	/**
	 * @param CManager_Controller_Request_Abstract $request
	 * @param CManager_Controller_Response_Abstract $response
	 */
	public function __construct($request = null, $response = null) {
		CManager_Registry::setFrontController($this);
		parent::__construct($request, $response);
	}

	/**
	 * @return CManager_Controller_Router_Abstract
	 */
	public function getRouter() {
		return $this->_router;
	}

	/**
	 * @param CManager_Controller_Router_Abstract $router
	 * @return void
	 */
	public function setRouter(CManager_Controller_Router_Abstract $router) {
		$this->_router = $router;
	}

	/**
	 * @return void
	 */
	public function run() {
		$request	= $this->getRequest();
		$response	= $this->getResponse();

		if ($request instanceof CManager_Controller_Request_Http && !$request->isPost()) {
			// Добавляем слеш в конец пути
			$uri = $request->getRawRequestUri();
			if (substr($uri, -1) !== '/'
					&& strpos($uri, '.') === false
					&& strpos($uri, '?') === false
					&& strpos($uri, $request->getRTSeparator()) === false) {

				$response->setRedirect($uri . '/', 301)->sendResponse(true);
			} else if (strpos($uri, '?') !== false
					&& strpos($uri, '/?') === false
					&& strpos($uri, $request->getRTSeparator()) === false){

				$response->setRedirect(str_replace('?', '/?', $uri), 301)->sendResponse(true);
			}
		}

		$this->getRouter()->run();

		$page = $this->getRouter()->getPage();
		$page->sendHeaders();
		$page->runTagsByMode(CManager_Controller_Tag::MODE_BACKGROUND);
		if ($request->hasRequestTag()) {
			$content = $page->runTagsByName($request->getRequestTag());
		} else {
			$content = $page->render();
		}

		$config = CManager_Registry::getConfig();
		if ($config->debug && $response->isException() && $response->renderExceptions()) {
			foreach ($response->getException() as $e) {
				echo (string)$e->getMessage() ."\n\n";
				if (!($e instanceof CManager_Exception)) {
					echo '<pre>'. $e->getTraceAsString() .'</pre>';
				}
			}
		}

		$this->getResponse()->setBody($content, 'layout')->sendResponse();
	}
}