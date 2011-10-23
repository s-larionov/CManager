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
	 * @param array $config
	 * @param cm_Controller_Request_HTTP $request
	 * @param cm_Controller_Response_HTTP $response
	 */
	public function __construct(array $config, $request, $response) {
		parent::__construct($request, $response);
		// @todo разбор конфига
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
	 * @return void
	 */
	final public function addSessionTag(cm_Controller_Tag $tag, $path = null) {
		$key = 'tag_'. $tag->name;
		$this->sessionTagsStorage($path)->{$key} = $tag->toArray();
		$this->sessionTagsStorage($path)->setExpirationHops(1, $key);
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
		if ($this->_tags === null) {
			$this->_tags = array();
			$this->_addTags();

			/**
			 * Вытаскиваем тэги из сессии
			 */
			$sessionData = $this->sessionTagsStorage()->getIterator();
			foreach ($sessionData as $tag) {
				$this->addTag($this->unserializeTag($tag));
			}
		}

		return $this->_tags;
	}

	/**
	 * Инициализирует все тэги страницы
	 *
	 * @return void
	 */
	protected function _addTags() {

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

//	/**
//	 * @see: cm_Controller_Action_Abstract::checkExtraParams() и
//	 * 		 cm_Controller_Router_Abstract::_construct()
//	 *
//	 * @return boolean
//	 */
//	public function validExtraPath() {
//		$isValid = false;
//		foreach ($this->getTags() as $tag) {
//			$checkResult = $tag->checkExtraPath($this->getRequest(), $this->getResponse());
//			if ($checkResult === true) {
//				$isValid = true;
//			} else if ($checkResult === false) {
//				$tag->isDisabled(true);
//			}
//		}
//		return $isValid;
//	}

	/**
	 * Возвращает урл перенаправления, если его нет возвращает FALSE.
	 *
	 * @return string | boolean
	 */
	public function getRedirectUrl() {

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
	 * @return void
	 */
	final public function setCode($value) {
		$this->_code = (int) $value;
	}

	/**
	 * @return string
	 */
	protected function _getLayout() {

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
	 */
	final public function setLayout($value) {
		$this->_layout = $value;
	}

	/**
	 * @return string
	 */
	protected function _getTitle() {

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
	 */
	final public function setTitle($value) {
		$this->_title = $value;
	}
}