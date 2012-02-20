<?php

abstract class CManager_Scheme_Abstract  {
	/**
	 * @var CManager_Scheme_Adapter_Abstract
	 */
	protected $adapter;

	/**
	 * @var CManager_Annotation_Object
	 */
	protected $annotation;

	/**
	 * @var CManager_Scheme_Abstract|null
	 */
	protected $parent = null;

	final public function __construct(CManager_Scheme_Adapter_Abstract $adapter) {
		$this->adapter = $adapter;
		$this->annotation = new CManager_Annotation_Object($this);
		$this->parse();
	}

	public function parse() {
		foreach($this->getAnnotation()->getPropertiesAnnotations() as $property => $annotation) {
			$value	= $this->getAdapter()->get($property);

			if ($annotation->hasAnnotation('required') && $value === null) {
				throw new CManager_Scheme_Exception("{$property} is required");
			}

			if (!$annotation->hasAnnotation('multiple') && is_array($value)) {
				throw new CManager_Scheme_Exception("Child '{$property}' should be only one");
			}

			if ($annotation->hasAnnotation('multiple') && !is_array($value)) {
				$value = $value === null? array(): array($value);
			}

			if ($annotation->hasAnnotation('multiple')) {
				$values = array();
				foreach($value as $item) {
					var_dump($item);
					$values[] = $this->createValue($item, $annotation);
				}
				$this->$property = $values;
			} else {
				$this->$property = $this->createValue($value, $annotation);
			}
		}
	}

	/**
	 * @return CManager_Scheme_Abstract|null
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * @param CManager_Scheme_Abstract $parent
	 * @return CManager_Scheme_Abstract
	 */
	public function setParent(CManager_Scheme_Abstract $parent) {
		$this->parent = $parent;
		return $this;
	}

	/**
	 * @return CManager_Scheme_Adapter_Abstract
	 */
	public function getAdapter() {
		return $this->adapter;
	}

	/**
	 * @return CManager_Annotation_Object
	 */
	public function getAnnotation() {
		return $this->annotation;
	}

	/**
	 * @param CManager_Scheme_Adapter_Abstract[]|CManager_Scheme_Adapter_Abstract|string|null $value
	 * @param CManager_Annotation_Property $annotation
	 * @param boolean $onlyScalar
	 * @return mixed
	 * @throws CManager_Scheme_Exception
	 */
	protected function createValue($value = null, CManager_Annotation_Property $annotation) {
		if ($value === null) {
			$value = $annotation->getDefaultValue();
			if ($value === null) {
				return null;
			}
		}

		$namespace = $annotation->getAnnotation('var', 'string');
		if (substr($namespace, -2) == '[]') {
			$namespace = substr($namespace, 0, -2);
		}
		switch(true) {
			case strpos($namespace, 'enum') === 0:
				$enumValues	= explode(',', substr($namespace, 5, -1));
				$value		= (string) $value;
				if (!in_array($value, $enumValues)) {
					throw new CManager_Structure_Exception("Value '{$value}' not in {$namespace}");
				}
				break;
			case $namespace == 'int':
				$value = (int) $value;
				break;
			case $namespace == 'float':
			case $namespace == 'double':
				$value = (double) $value;
				break;
			case $namespace == 'bool':
			case $namespace == 'boolean':
				$value = (bool) $value;
				break;
			case $namespace == 'string':
				var_dump($annotation->getAnnotations());
				var_dump($value);
				$value = (string) $value;
				break;
			case class_exists($namespace):
				if (!$value instanceof CManager_Scheme_Adapter_Abstract) {
					throw new CManager_Scheme_Exception("Value for {$namespace} must instanceof CManager_Structure_Adapter_Abstract");
				}
				$value = CManager_Helper_Object::newInstance($namespace, __CLASS__, array($value, $this));
				break;
			default:
				throw new CManager_Structure_Exception("Namespace {$namespace} not defined");
		}
		return $value;
	}
}
