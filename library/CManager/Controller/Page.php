<?php

class CManager_Controller_Page extends CManager_Controller_Abstract implements CManager_Controller_Page_Interface {
	const CONTENT_TYPE_DEFAULT = 'text/html; charset=utf-8';

	/**
	 * Код заголовка (200, 403 и т.д.)
	 *
	 * @var int
	 */
	protected $_code = 200;

	/**
	 * Разметка страницы (php-файл)
	 *
	 * @var string
	 */
	protected $_layout;

	/**
	 * Заголовки страницы (ключ == mode)
	 *
	 * @var string[]
	 */
	protected $_titles = array();

	/**
	 * Мета данные страницы
	 * @todo: реализовать
	 *
	 * @var string
	 */
	protected $_meta;

	/**
	 * Массив тэгов страницы
	 * @var array
	 */
	protected $_tags;

	/**
	 * Массив "отложенных" тэгов страницы
	 * @var array
	 */
	protected $_sessionTagsStorage = array();

	/**
	 * @var array
	 */
	protected $_variables = array();

	/**
	 * @var CManager_Controller_Route
	 */
	protected $_route = null;

	/**
	 * @var CManager_Controller_Router_Config_Page
	 */
	protected $_config = array();

	/**
	 * @var array
	 */
	protected $_contentType = self::CONTENT_TYPE_DEFAULT;

	/**
	 * @param CManager_Controller_Router_Config_Page $config
	 * @param CManager_Controller_Request_Abstract|CManager_Controller_Request_Http $request
	 * @param CManager_Controller_Response_Abstract|CManager_Controller_Response_Http $response
	 */
	public function __construct(CManager_Controller_Router_Config_Page $config, CManager_Controller_Request_Abstract $request, CManager_Controller_Response_Abstract $response) {
		parent::__construct($request, $response);
		$this->_config = $config;
		$this->_addTags();

		$this->setCode($config->error_code);
		$this->setContentType($config->content_type);
	}

	/**
	 * Инициализация страницы. Для переопределения в дочерних страницах
	 */
	public function init() {}

	/**
	 * Создает и возвращает тэг
	 *
	 * @param string $name
	 * @param string $namespace
	 * @param string $mode
	 * @param Zend_Config|array $params
	 * @return CManager_Controller_Tag
	 */
	public function createTag($name, $namespace, $mode, $params = null) {
		return new CManager_Controller_Tag($name, $namespace, $mode, $params);
	}

	/**
	 * Создает из массива и возвращает тэг
	 * @param array $data
	 * @return CManager_Controller_Tag
	 */
	final public function unserializeTag($data) {
		return CManager_Controller_Tag::fromArray($data);
	}

	/**
	 * Добавляет тэг на страницу
	 *
	 * @param CManager_Controller_Tag $tag
	 * @return void
	 */
	final public function addTag(CManager_Controller_Tag $tag) {
		$this->_tags[] = $tag;
	}

	/**
	 * Добавляет постоянный тэг на страницу. Постоянный тэг будет доступен при следующем
	 * вызове страницы. Вызывается однократно, затем удаляется. Реализуется с помощью сессии.
	 *
	 * @param CManager_Controller_Tag $tag
	 * @param string $path
	 * @return CManager_Controller_Page
	 */
	final public function addSessionTag(CManager_Controller_Tag $tag, $path = null) {
		$key = 'tag_'. $tag->name;
		$this->_sessionTagsStorage($path)->{$key} = $tag->toArray();
		$this->_sessionTagsStorage($path)->setExpirationHops(1, $key);
		return $this;
	}

	/**
	 * @param string $path
	 * @return Zend_Session_Namespace
	 */
	final private function _sessionTagsStorage($path = null) {
		if (!$path) {
			$path = $this->getRequest()->getPath();
		}

		if (!isset($this->_sessionTagsStorage[$path])) {
			$key = 'CManager_Controller_Page_'. $path;
			$this->_sessionTagsStorage[$path] = new Zend_Session_Namespace($key);
		}

		return $this->_sessionTagsStorage[$path];
	}

	/**
	 * Возвращает все тэги страницы
	 *
	 * @return CManager_Controller_Tag[]
	 */
	final public function getTags() {
		return $this->_tags;
	}

	/**
	 * Инициализирует все тэги страницы
	 *
	 * @return void
	 */
	protected function _addTags() {
		$this->_tags = array();
		// выбираем тэги

		foreach ($this->_config->tag as $tag) {
			// определяем режим работы
			switch ($tag->mode) {
				case 'background':
					$mode = CManager_Controller_Tag::MODE_BACKGROUND;
					break;
				case 'action':
					$mode = CManager_Controller_Tag::MODE_ACTION;
					break;
				case 'normal':
				default:
					$mode = CManager_Controller_Tag::MODE_NORMAL;
					break;
			}

			// создаем объект
			$this->addTag($this->createTag($tag->name, $tag->namespace, $mode, $tag->param));
		}

		// Вытаскиваем тэги из сессии
		$sessionData = $this->_sessionTagsStorage()->getIterator();
		foreach ($sessionData as $tag) {
			$this->addTag($this->unserializeTag($tag));
		}
	}

	/**
	 * Возвращает тэги с именем $name
	 *
	 * @param string $name
	 * @param string $mode
	 * @return array
	 */
	public function getTagsByName($name, $mode = null) {
		$result = array();
		foreach ($this->getTags() as $tag) {
			if ($tag->name === $name && ($mode === null || $mode === $tag->mode)) {
				$result[] = $tag;
			}
		}
		return $result;
	}

	/**
	 * Возвращает первый найденный тэг с именем $name
	 *
	 * @param string $name
	 * @param string $mode
	 * @return CManager_Controller_Tag|null
	 */
	public function getTagByName($name, $mode = null) {
		$tags = $this->getTagsByName($name, $mode);
		return isset($tags[0])? $tags[0]: null;
	}

	/**
	 * Запускает тэги с именем $name
	 *
	 * @param string $name
	 * @param string $mode
	 * @return string
	 */
	public function runTagsByName($name, $mode = null) {
		$out = '';

		foreach ($this->getTags() as $tag) {
			if ($tag->name === $name && ($mode === null || $mode === $tag->mode)) {
				$out .= $this->_runTag($tag);
			}
		}

		return $out;
	}

	/**
	 * Запускает тэги с режимом $mode
	 *
	 * @param string $mode
	 * @return string
	 */
	public function runTagsByMode($mode) {
		$out = '';
		foreach ($this->getTags() as $tag) {
			if ($tag->mode === $mode) {
				$out .= $this->_runTag($tag);
			}
		}
		return $out;
	}

	/**
	 * @param CManager_Controller_Tag $tag
	 * @return string
	 * @throws CManager_Controller_Action_DoneException
	 */
	private function _runTag(CManager_Controller_Tag $tag) {
		$out = '';
		try {
			$out .= $tag->run($this->getRequest(), $this->getResponse());
		} catch (CManager_Controller_Action_DoneException $e) {
			return (string) $e;
		} catch (Exception $e) {
			if ($tag->mode === CManager_Controller_Tag::MODE_ACTION) {
				throw $e;
			} else {
				$this->getResponse()->setException($e);
			}
		}
		return $out;
	}

	/**
	 * Рисуем страницу
	 *
	 * @return string
	 */
	public function render() {
		try {
			$layouts = CManager_Registry::getConfig()->get('layouts');

			if ($layouts instanceof Zend_Config) {
				$layouts = $layouts->toArray();
			}

			if (!is_array($layouts)) {
				$layouts = array($layouts);
			}

			$layout = null;
			foreach ($layouts as $value) {
				$value = rtrim($value) . '/' . $this->getLayout();
				if (file_exists($value) && !is_dir($value)) {
					$layout = $value;
					break;
				}
			}

			if (!$layout) {
				throw new CManager_Exception("Layout ". basename($this->getLayout()) ." not found.");
			}

			ob_start();
			CManager_Display::setApplication(CManager_Registry::getFrontController());
			include $layout;
			return ob_get_clean();
		} catch (Exception $e) {
			$this->getResponse()->setException($e);
		}

		return '';
	}

	/**
	 * @return string
	 */
	protected function _getLayout() {
		if ($this->_config->layout === null) {
			throw new CManager_Controller_Page_Exception("Attribute @layout not defined");
		}
		return $this->_config->layout;
	}

	/**
	 * @param string $mode
	 * @return string
	 */
	protected function _getTitle($mode = 'default') {
		foreach($this->_config->title as $title) {
			if ($title->mode == $mode) {
				return (string) $title->value;
			}
		}
		if ($mode != 'default') {
			return $this->_getTitle('default');
		}
		return '';
	}

	/**
	 * Возвращает урл перенаправления, если его нет возвращает FALSE.
	 *
	 * @return string | boolean
	 */
	public function getRedirectUrl() {
		if ($this->_config->redirect === null) {
			return false;
		}

		$url = $this->_config->redirect;
		if (strpos($url, '/') !== 0) {
			$url = rtrim($this->getRequest()->getRawRequestUri(false), '/') .'/'. $url;
		}

		return $url;
	}

	/**
	 * Пытаемся сделать редирект
	 *
	 * @return void
	 */
	final public function tryRedirect() {
		if ($redirect = $this->getRedirectUrl()) {
			$this->getResponse()
				 ->clearHeaders()
				 ->setRedirect($redirect)
				 ->sendHeaders(true);
		}
	}

	/**
	 * @return int
	 */
	final public function getCode() {
		return $this->_code;
	}

	/**
	 * @param int $value
	 * @return CManager_Controller_Page
	 */
	final public function setCode($value) {
		$this->_code = (int) $value;
		return $this;
	}

	/**
	 * @return string
	 */
	final public function getContenttype() {
		return $this->_contentType;
	}

	/**
	 * @param string $contentType
	 * @return CManager_Controller_Page
	 */
	final public function setContentType($contentType) {
		$this->_contentType = (string) $contentType;
		return $this;
	}

	/**
	 * @return string
	 */
	final public function getLayout() {
		if ($this->_layout === null) {
			$this->_layout = $this->_getLayout();
		}
		return $this->_layout;
	}

	/**
	 * @param string $value
	 * @return CManager_Controller_Page
	 */
	final public function setLayout($value) {
		$this->_layout = $value;
		return $this;
	}

	/**
	 * @param string $mode
	 * @return string
	 */
	final public function getTitle($mode = 'default') {
		if (!array_key_exists($mode, $this->_titles)) {
			$this->_titles[$mode] = $this->_getTitle($mode);
		}
		return $this->_titles[$mode];
	}

	/**
	 * @param string $value
	 * @param string $mode
	 * @return CManager_Controller_Page
	 */
	final public function setTitle($value, $mode = 'default') {
		$this->_titles[$mode] = $value;
		return $this;
	}

	/**
	 * @param array $variables
	 * @return CManager_Controller_Page
	 */
	final public function setVariables(array $variables) {
		foreach($variables as $name => $variable) {
			$this->setVariable($name, $variable);
		}
		return $this;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	final public function setVariable($name, $value) {
		$this->_variables[$name] = $value;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	final public function getVariable($name) {
		if (array_key_exists($name, $this->_variables)) {
			return $this->_variables[$name];
		}
		return null;
	}

	/**
	 * @return array
	 */
	final public function getVariables() {
		return $this->_variables;
	}

	/**
	 * @param CManager_Controller_Route $route
	 * @return CManager_Controller_Page
	 */
	final public function setRoute(CManager_Controller_Route $route) {
		$this->_route = $route;
		return $this;
	}

	/**
	 * @return CManager_Controller_Route
	 */
	final public function getRoute() {
		return $this->_route;
	}

	public function sendHeaders() {
		// посылаем заголовки
		$response = $this->getResponse();
		$response->setHttpResponseCode($this->getCode());
		$response->setHeader('Content-Type', $this->getContenttype());
	}
}