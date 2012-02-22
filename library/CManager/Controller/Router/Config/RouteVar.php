<?php

class CManager_Controller_Router_Config_RouteVar extends CManager_Controller_Router_Config_Abstract {
	/**
	 * @var string
	 * @required
	 */
	public $name;

	/**
	 * @var boolean
	 */
	public $pass = false;

	/**
	 * @var string
	 */
	public $rule = '.*';

	/**
	 * @var string
	 */
	public $explode;

	/**
	 * @var string
	 */
	public $default;

	/**
	 * @var string
	 */
	public $pattern;

	/**
	 * @var string
	 */
	public $namespace;

	public function parse() {
		parent::parse();

		// реализовываем возможность подставлять константы для дефолтного значения
		if ($this->default && is_string($this->default) && defined($this->default)) {
			$this->default = constant($this->default);
		}
	}
}
