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

			if ($annotation->hasAnnotation('single') && is_array($value)) {
				throw new CManager_Scheme_Exception("Child '{$property}' should be only one");
			}

			if (!$annotation->hasAnnotation('single') && !is_array($value)) {
				$value = $value === null? array(): array($value);
			}

			$this->$property = $this->createValue($value, $annotation);
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
	protected function createValue($value = null, CManager_Annotation_Property $annotation, $onlyScalar = false) {
		if ($value === null) {
			$defaultValues = $this->getAnnotation()->getReflection()->getDefaultProperties();
			$default = $defaultValues[$annotation->getReflection()->getName()];
			if ($default === null) {
				return null;
			}
			$value = $default;
		}



		if (!$annotation->hasAnnotation('single')) {
			$result = array();
			$itemConfig = array_merge($annotation, array('single' => true));
			foreach($value as $item) {
				$result[] = $this->_createValue($item, $itemConfig, $onlyScalar);
			}
			return $result;
		}

		switch(true) {
			case strpos($annotation['namespace'], 'enum') === 0:
				$enumValues	= explode(',', substr($annotation['namespace'], 5, -1));
				$value		= (string) $value;
				if (!in_array($value, $enumValues)) {
					throw new CManager_Structure_Exception("Value '{$value}' not in {$annotation['namespace']}");
				}
				break;
			case $annotation['namespace'] == 'int':
				$value = (int) $value;
				break;
			case $annotation['namespace'] == 'float':
			case $annotation['namespace'] == 'double':
				$value = (double) $value;
				break;
			case $annotation['namespace'] == 'bool':
			case $annotation['namespace'] == 'boolean':
				$value = (bool) $value;
				break;
			case $annotation['namespace'] == 'string':
				$value = (string) $value;
				break;
			case !$onlyScalar && class_exists(static::NAMESPACE_PREFIX . $annotation['namespace']):
				if (!$value instanceof CManager_Structure_Adapter_Abstract) {
					throw new CManager_Structure_Exception("Value for {$annotation['namespace']} must instanceof CManager_Structure_Adapter_Abstract");
				}

				$value = CManager_Helper_Object::newInstance(
					static::NAMESPACE_PREFIX . $annotation['namespace'],
					__CLASS__,
					array($value, $this)
				);
				break;
			default:
				throw new CManager_Structure_Exception("Namespace {$annotation['namespace']} not defined");
		}
		return $value;
	}
}
