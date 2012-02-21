<?php

/**
 * @property string $name
 * @property string $rule
 * @property string|null $explode
 * @property string|null $default
 * @property string|null $pattern
 * @property string|null $namespace
 */
class CManager_Controller_Router_Config_RouteVar extends CManager_Controller_Router_Config_Abstract {
	/**
	 * @var string
	 * @required
	 */
	public $name;

	/**
	 * @var string
	 * @required
	 */
	public $rule;

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
