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
					$this->inheritSingle($property);
				}
			}
		}
	}

	/**
	 * @param string $propertyName
	 * @param CManager_Annotation_Property $annotation
	 * @return void
	 * @throws CManager_Structure_Exception
	 */
	protected function inheritMultiple($propertyName, CManager_Annotation_Property $annotation) {
		// настройки для поля:
		// * по какому атрибуту наследовать,
		// * по какому атрибуту исключать наследование родительских элементов,
		//   т.е. считать что в текущем элементе значение заменяет родительское если этот атрибут совпадает
		$passBy		= $annotation->getAnnotation('passBy', 'pass');
		$identifyBy = $annotation->getAnnotation('identifyBy');
		$exclusionBy= $annotation->getAnnotation('exclusionBy');

		// получаем список значений аттрибута, по которому определяется уникальность
		$identifies = array();
		if ($identifyBy !== null) {
			foreach($this->{$propertyName} as $property) {
				// если итем не является наследником CManager_Scheme_Abstract
				// или у него отсутсвует св-во для идентификации, то пропускаем этот пункт
				if (!$property instanceof CManager_Scheme_Abstract || !property_exists($property, $identifyBy)) {
					break;
				}
				if ($property->{$identifyBy} !== null && !in_array($property->{$identifyBy}, $identifies )) {
					$identifies[] = $property->{$identifyBy};
				}
			}
		}

		// получаем список исключенных элементов (если указан $config['exclusion'])
		$exclusions = array();
		if ($exclusionBy !== null) {
			$exclusionProperties = $this->{$exclusionBy};
			if ($exclusionProperties && is_array($exclusionProperties)) {
				foreach($exclusionProperties as $exclusionProperty) {
					// если итем не является наследником CManager_Scheme_Abstract
					// или у него отсутсвует св-во для идентификации, то пропускаем этот пункт
					if (!$exclusionProperty instanceof CManager_Scheme_Abstract || !property_exists($exclusionProperty, $identifyBy)) {
						break;
					}

					if ($exclusionProperty->{$identifyBy} !== null && !in_array($exclusionProperty->{$identifyBy}, $exclusions)) {
						$exclusions[] = $exclusionProperty->{$identifyBy};
					}
				}
			}
		}

		// собираем элементы с родителей
		$parent = $this;
		while ($parent = $parent->getParent()) {
			// если у родителя нет такого св-ва, то пропускаем его
			if (!property_exists($parent, $propertyName)) {
				continue;
			}

			if (($properties = $parent->$propertyName) !== null) {
				if (!is_array($properties)) {
					$properties = array($properties);
				}

				foreach($properties as $propertyItem) {
					// если св-ва, по которому наследуется или св-во по которому идентифицируется элемент
					// не определено или не установлено, то пропускаем
					if (!property_exists($propertyItem, $passBy) || !$propertyItem->{$passBy}) {
						continue;
					}

					if ($identifyBy === null) {
						$this->{$propertyName}[] = $propertyItem;
					} else if (property_exists($propertyItem, $identifyBy)) {
						if ($propertyItem->{$identifyBy} !== null) {
							// если такой элемент уже наследовался или элемент с такой же идентификацией
							// исключен из наследования, то пропускаем
							if (in_array($propertyItem->{$identifyBy}, $identifies) || in_array($propertyItem->{$identifyBy}, $exclusions)) {
								continue;
							}
							$identifies[] = $propertyItem->{$identifyBy};
						}
						$this->{$propertyName}[] = $propertyItem;
					}
				}
			}
		}
	}

	/**
	 * Наследование св-в. Работает только если нет значения по-умолчанию.
	 *
	 * @param string $propertyName
	 * @return void
	 * @throws CManager_Structure_Exception
	 */
	protected function inheritSingle($propertyName) {
		// наследуем только если оно не переопределено в текущем элементе
		if ($this->{$propertyName} !== null) {
			return;
		}

		// собираем элементы с родителей
		$parent = $this;
		while ($parent = $parent->getParent()) {
			// если у родителя нет такого св-ва, то пропускаем его
			if (!property_exists($parent, $propertyName)) {
				continue;
			}

			if (($property = $parent->$propertyName) !== null) {
				$this->{$propertyName} = $property;
				return;
			}
		}
	}

}
