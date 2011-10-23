<?php

/**
 *	Разбирает урл средствами роутера и на основе полученных
 *	от него данных генерирует ответ.
 */

class cm_Controller_Front extends cm_Controller_Abstract {
	/**
	 * @var cm_Controller_Router_Abstract
	 */
	private $_router;

	/**
	 * @param cm_Controller_Request_Abstract $request
	 * @param cm_Controller_Response_Abstract $response
	 */
	public function __construct($request = null, $response = null) {
		cm_Registry::setFrontController($this);
		parent::__construct($request, $response);
	}

	/**
	 * @return cm_Controller_Router_Abstract
	 */
	public function getRouter() {
		return $this->_router;
	}

	/**
	 * @param cm_Controller_Router_Abstract $router
	 * @return void
	 */
	public function setRouter(cm_Controller_Router_Abstract $router) {
		$this->_router = $router;
	}

	/**
	 * @return void
	 */
	public function run() {
		$request	= $this->getRequest();
		$response	= $this->getResponse();

		if ($request instanceof cm_Controller_Request_HTTP && !$request->isPost()) {
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

		$this->getRouter()->getPage()->runTagsByMode(cm_Controller_Tag::MODE_BACKGROUND);

		if ($request->hasRequestTag()) {
			$content = $this->getRouter()->getPage()->runTagsByName($request->getRequestTag());
		} else {
			$content = $this->getRouter()->getPage()->render();
		}

		$config = cm_Registry::getConfig();
		if ($config->debug && $response->isException() && $response->renderExceptions()) {
			foreach ($response->getException() as $e) {
				echo (string)$e->getMessage() ."\n\n";
				if (!($e instanceof cm_Exception)) {
					echo '<pre>'. $e->getTraceAsString() .'</pre>';
				}
			}
		}

		$this->getResponse()->setBody($content, 'layout')->sendResponse();
	}
}