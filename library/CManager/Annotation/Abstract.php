<?php

abstract class CManager_Annotation_Abstract {
	/**
	 * @var ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionObject
	 */
	protected $reflection;
	protected $description = '';
	protected $annotations = array();

	/**
	 * Parse annotations for public properties
	 *
	 * @return CManager_Annotation_Abstract
	 */
	protected function parseAnnotations() {
		if ($docComment = $this->getReflection()->getDocComment()) {
			$descriptionLines = array();
			foreach(explode("\n", trim(substr($docComment, 3, -2))) as $line) {
				$line = trim($line, "\r\t\n *");

				if (strpos($line, '@') === 0) {
					if ($pos = strpos($line, ' ')) {
						$this->addAnnotation(substr($line, 1, $pos - 1), trim(substr($line, $pos)));
					} else {
						$this->addAnnotation(substr($line, 1), true);
					}
				} else {
					$descriptionLines[] = $line;
				}
				$this->description = trim(implode("\n", $descriptionLines));
			}
		}
		return $this;
	}

	/**
	 * @param string $name
	 * @param string|boolean $value
	 * @return CManager_Annotation_Abstract
	 */
	protected function addAnnotation($name, $value) {
		if (array_key_exists($name, $this->annotations)) {
			if (is_array($this->annotations[$name])) {
				$this->annotations[$name] = array($this->annotations[$name]);
			}
			$this->annotations[$name][] = $value;
		} else {
			$this->annotations[$name] = $value;
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function getAnnotations() {
		return $this->annotations;
	}

	public function hasAnnotation($name) {
		return array_key_exists($name, $this->annotations);
	}

	/**
	 *
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return string|boolean|null
	 */
	public function getAnnotation($name, $default = null) {
		if ($this->hasAnnotation($name)) {
			return $this->annotations[$name];
		}
		return $default;
	}

	/**
	 * @return ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionObject
	 */
	public function getReflection() {
		return $this->reflection;
	}
}
