<?php

abstract class CManager_Scheme_Inheritable extends CManager_Scheme_Abstract  {
	public function parse() {
		parent::parse();

		// смотрим, нужно ли наследование
		foreach($this->getAnnotation()->getPropertiesAnnotations() as $property => $annotation) {
			if ($annotation->hasAnnotation('inherit')) {
				if ($annotation->hasAnnotation('multiple')) {
					$this->inheritMultiple($property, $annotation);
				} else {
//					$this->inheritSingle($property, $annotation);
				}
			}
		}
	}

	/**
	 * @param string $property
	 * @param CManager_Annotation_Property $annotation
	 * @return void
	 * @throws CManager_Structure_Exception
	 */
	protected function inheritMultiple($property, CManager_Annotation_Property $annotation) {
		// настройки для поля:
		// * по какому атрибуту наследовать,
		// * по какому атрибуту исключать наследование родительских элементов,
		//   т.е. считать что в текущем элементе значение заменяет родительское если этот атрибут совпадает
		$passBy		= $annotation->getAnnotation('passBy', 'pass');
		$identifyBy	= $annotation->getAnnotation('identifyBy');

		// получаем список значений аттрибута, по которому определяется уникальность
		$identifyExists	= array();
		if ($identifyBy !== null) {
			foreach($this->$property as $item) {
				$identify = $item->$identifyBy;
				if ($identify !== null && !in_array($identify, $identifyExists)) {
					$identifyExists[] = $identify;
				}
			}
		}

		// получаем список исключенных элементов (если указан $config['exclusion'])
		$namesExclusion = array();
		if ($annotation['exclusion']) {
			$exclusions = $this->{$annotation['exclusion']};
			if ($exclusions && is_array($exclusions)) {
				foreach($exclusions as $exclusion) {
					$identify = $exclusion->name;
					if ($identify !== null && !in_array($identify, $namesExclusion)) {
						$namesExclusion[] = $identify;
					}
				}
			}
		}

		// собираем элементы с родителей
		$parent = $this;
		while ($parent = $parent->getParent()) {
			$elements = $parent->$property;
			if ($elements !== null) {
				if (!is_array($elements)) {
					$elements = array($elements);
				}

				$namesExistsCurrent = array();
				foreach($elements as $item) {
					if ($item->{self::PASS_ATTRIBUTE} === null) {
						continue;
					}
					$identify = $item->name;
					if ($identify !== null && !in_array($identify, $identifyExists)) {
						$namesExistsCurrent[] = $identify;
						if (in_array($identify, $namesExclusion)) {
							continue;
						}
						$elementsCurrent[] = $item;
					} else if ($identify === null) {
						$elementsCurrent[] = $item;
					}
				}
				$identifyExists = array_merge($identifyExists, $namesExistsCurrent);
			}
		}
		$this->_set($property, $elementsCurrent);
	}

}
