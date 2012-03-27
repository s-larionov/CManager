<?php

class CManager_Cli_ProgressBar {
	const SYMBOL_BS					= "\x08";
	const SYMBOL_BEGIN				= '[';
	const SYMBOL_END				= ']';
	const SYMBOL_FILLED				= '#';
	const SYMBOL_SPACE				= ' ';

	const DEFAULT_WIDTH				= 30;

	protected $width				= self::DEFAULT_WIDTH;
	protected $showDigits			= true;

	protected $minValue				= 0;
	protected $maxValue				= 100;
	protected $currentValue			= 0;

	protected $isRendered			= false;
	protected $autoRender			= true;

	protected $lastRenderContent	= '';

	/**
	 * @return int
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * @param int $width
	 * @return CManager_Cli_ProgressBar
	 */
	public function setWidth($width) {
		$this->width = (int) $width;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getMinValue() {
		return $this->minValue;
	}

	/**
	 * @param int $value
	 * @return CManager_Cli_ProgressBar
	 */
	public function setMinValue($value) {
		$this->minValue = (int) $value;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getMaxValue() {
		return $this->maxValue;
	}

	/**
	 * @param int $value
	 * @return CManager_Cli_ProgressBar
	 */
	public function setMaxValue($value) {
		$this->maxValue = (int) $value;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCurrentValue() {
		return $this->currentValue;
	}

	/**
	 * @param int $value
	 * @return CManager_Cli_ProgressBar
	 */
	public function setCurrentValue($value) {
		$this->currentValue = (int) $value;
		if ($this->getAutoRender()) {
			$this->render();
		}
		return $this;
	}

	/**
	 * @return bool bool
	 */
	public function getShowDigits() {
		return $this->showDigits;
	}

	/**
	 * @param bool $showDigits
	 * @return CManager_Cli_ProgressBar
	 */
	public function setShowDigits($showDigits) {
		$this->showDigits = (bool) $showDigits;
		return $this;
	}

	/**
	 * @return CManager_Cli_ProgressBar
	 */
	public function render() {
		$this->clear();

		$currentValueAsPercents = (int) (($this->getCurrentValue() - $this->getMinValue()) * 100 / ($this->getMaxValue() - $this->getMinValue()));
		$barWidth = (int) ($currentValueAsPercents * $this->getWidth() / 100);

		$this->lastRenderContent .= self::SYMBOL_BEGIN;
		$this->lastRenderContent .= str_repeat(self::SYMBOL_FILLED, $barWidth);
		$this->lastRenderContent .= str_repeat(self::SYMBOL_SPACE, $this->getWidth() - $barWidth);
		$this->lastRenderContent .= self::SYMBOL_END;

		if ($this->getShowDigits()) {
			$this->lastRenderContent .= sprintf('%4d', $currentValueAsPercents) . '%';
		}

		echo $this->lastRenderContent;

		$this->setIsRendered(true);
		return $this;
	}

	/**
	 * @return CManager_Cli_ProgressBar
	 */
	public function clear() {
		if ($this->getIsRendered()) {
			echo str_repeat(self::SYMBOL_BS, strlen($this->lastRenderContent));
			$this->setIsRendered(false);
		}
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getIsRendered() {
		return $this->isRendered;
	}

	/**
	 * @param bool $isRendered
	 * @return CManager_Cli_ProgressBar
	 */
	protected function setIsRendered($isRendered) {
		$this->isRendered = (bool) $isRendered;
		if (!$this->isRendered) {
			$this->lastRenderContent = '';
		}
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getAutoRender() {
		return $this->autoRender;
	}

	/**
	 * @param bool $autoRender
	 * @return CManager_Cli_ProgressBar
	 */
	public function setAutoRender($autoRender) {
		$this->autoRender = (bool) $autoRender;
		return $this;
	}
}
