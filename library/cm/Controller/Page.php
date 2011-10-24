<?php

class cm_Controller_Page extends cm_Controller_Abstract {
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
	 * Заголовок страницы
	 *
	 * @var string
	 */
	protected $_title;

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
	 * @var cm_Controller_Route
	 */
	protected $_route = null;

	/**
	 * @var array
	 */
	protected $_config = array();

	/**
	 * @param array $config
	 * @param cm_Controller_Request_Abstract|cm_Controller_Request_HTTP $request
	 * @param cm_Controller_Response_Abstract|cm_Controller_Response_HTTP $response
	 */
	public function __construct(array $config, $request, $response) {
		parent::__construct($request, $response);
		$this->_config = $config;
		$this->_addTags();
	}

	/**
	 * Создает и возвращает тэг
	 *
	 * @param string $name
	 * @param string $namespace
	 * @param string $mode
	 * @param Zend_Config $params
	 * @return cm_Controller_Tag
	 */
	public function createTag($name, $namespace, $mode, $params = null) {
		return new cm_Controller_Tag($name, $namespace, $mode, $params);
	}

	/**
	 * Создает из массива и возвращает тэг
	 * @param array $data
	 * @return cm_Controller_Tag
	 */
	final public function unserializeTag($data) {
		return cm_Controller_Tag::fromArray($data);
	}

	/**
	 * Добавляет тэг на страницу
	 *
	 * @param cm_Controller_Tag $tag
	 * @return void
	 */
	final public function addTag(cm_Controller_Tag $tag) {
		$this->_tags[] = $tag;
	}

	/**
	 * Добавляет постоянный тэг на страницу. Постоянный тэг будет доступен при следующем
	 * вызове страницы. Вызывается однократно, затем удаляется. Реализуется с помощью сессии.
	 *
	 * @param cm_Controller_Tag $tag
	 * @param string $path
	 * @return cm_Controller_Page
	 */
	final public function addSessionTag(cm_Controller_Tag $tag, $path = null) {
		$key = 'tag_'. $tag->name;
		$this->sessionTagsStorage($path)->{$key} = $tag->toArray();
		$this->sessionTagsStorage($path)->setExpirationHops(1, $key);
		return $this;
	}

	/**
	 * @param string $path
	 * @return Zend_Session_Namespace
	 */
	final private function sessionTagsStorage($path = null) {
		if (!$path) {
			$path = $this->getRequest()->getPath();
		}

		if (!isset($this->_sessionTagsStorage[$path])) {
			$key = 'cm_Controller_Page_'. $path;
			$this->_sessionTagsStorage[$path] = new Zend_Session_Namespace($key);
		}

		return $this->_sessionTagsStorage[$path];
	}

	/**
	 * Возвращает все тэги страницы
	 *
	 * @return cm_Controller_Tag[]
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

		if (!isset($this->_config['tag'])) {
			return;
		}
		$tags = $this->_config['tag'];
		if (!is_array($tags) || !array_key_exists(0, $tags)) {
			$tags = array($tags);
		}

		foreach ($tags as $tag) {
			if (!isset($tag['name'])) {
				throw new cm_Controller_Page_Exception("Attribute @name is required");
			}
			if (!isset($tag['namespace'])) {
				throw new cm_Controller_Page_Exception("Attribute @namespace is required");
			}

			$params = isset($tag['params'])? $tag['params']: array();
			if (!is_array($params)) {
				$params = array($params);
			}

			// определяем режим работы
			$mode = isset($tag['mode'])? $tag['mode']: null;
			switch ($mode) {
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
			$this->addTag($this->createTag($tag['name'], $tag['namespace'], $mode, $params));
		}

		// Вытаскиваем тэги из сессии
		$sessionData = $this->sessionTagsStorage()->getIterator();
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
	 * @return cm_Controller_Tag|null
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
	 * @param cm_Controller_Tag $tag
	 * @return string
	 * @throws cm_Controller_Action_DoneException
	 */
	private function _runTag(cm_Controller_Tag $tag) {
		$out = '';
		try {
			$out .= $tag->run($this->getRequest(), $this->getResponse());
		} catch (cm_Controller_Action_DoneException $e) {
			return (string) $e;
		} catch (Exception $e) {
			if ($tag->mode === cm_Controller_Tag::MODE_ACTION) {
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
			$layouts = cm_Registry::getConfig()->get('layouts');

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
				throw new cm_Exception("Layout ". basename($this->getLayout()) ." not found.");
			}

			ob_start();
			cm_Display::setApplication(cm_Registry::getFrontController());
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
		// TODO
		if (isset($this->_config['layout'])) {
			return $this->_config['layout'];
		}
		$currentRoute = $this->getRoute()->getParent();
		$recursion = 0;
		while ($currentRoute !== null) {
			if (($layout = $currentRoute->getPageConfig('layout')) !== null) {
				return $layout;
			}
			if (++$recursion > 50) {
				throw new cm_Controller_Page_Exception("Recursion detected");
			}
			$currentRoute = $currentRoute->getParent();
		}
		if ($layout = $this->getRoute()->getRouter()->getStructure('layout')) {
			return $layout;
		}
		throw new cm_Controller_Page_Exception("Attribute @layout not defined");
	}

	/**
	 * @return string
	 */
	protected function _getTitle() {
		if (!isset($this->_config['title'])) {
			return '';
		}
		return $this->_config['title'];
	}

	/**
	 * Возвращает урл перенаправления, если его нет возвращает FALSE.
	 *
	 * @return string | boolean
	 */
	public function getRedirectUrl() {
		if (!isset($this->_config['redirect'])) {
			return false;
		}

		$url = $this->_config['redirect'];
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
	 * @return cm_Controller_Page
	 */
	final public function setCode($value) {
		$this->_code = (int) $value;
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
	 * @return cm_Controller_Page
	 */
	final public function setLayout($value) {
		$this->_layout = $value;
		return $this;
	}

	/**
	 * @todo сделать для тега <title/> в структуре атрибут mode, что бы можно было
	 * @todo задавать несколько разных заголовков. И если какого-то нет, возвращать без @mode
	 * @return string
	 */
	final public function getTitle() {
		if ($this->_title === null) {
			$this->_title = $this->_getTitle();
		}
		return $this->_title;
	}

	/**
	 * @param string $value
	 * @return cm_Controller_Page
	 */
	final public function setTitle($value) {
		$this->_title = $value;
		return $this;
	}

	/**
	 * @param array $variables
	 * @return cm_Controller_Page
	 */
	final public function setVars(array $variables) {
		foreach($variables as $name => $variable) {
			$this->setVar($name, $variable);
		}
		return $this;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	final public function setVar($name, $value) {
		$this->_variables[$name] = $value;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	final public function getVar($name) {
		if (array_key_exists($name, $this->_variables)) {
			return $this->_variables[$name];
		}
		return null;
	}

	/**
	 * @param cm_Controller_Route $route
	 * @return cm_Controller_Page
	 */
	final public function setRoute(cm_Controller_Route $route) {
		$this->_route = $route;
		return $this;
	}

	/**
	 * @return cm_Controller_Route
	 */
	final public function getRoute() {
		return $this->_route;
	}
}