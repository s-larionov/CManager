<?php

class CManager_Timer {
	/**
	 * @var array
	 */
	protected static $_timers = array();

	/**
	 * @static
	 * @param string $label
	 */
	public static function start($label = '') {
		self::$_timers[$label] = array(
			'start' => microtime(true)
		);
	}

	/**
	 * @static
	 * @param string $label
	 * @return int
	 */
	public static function end($label = '') {
		if (isset(self::$_timers[$label]) && is_array(self::$_timers[$label]) && isset(self::$_timers[$label]['start'])) {
			self::$_timers[$label]['end'] = microtime(true);
			return self::get($label);
		}
		self::start($label);
		return self::end($label);
	}

	/**
	 * @static
	 * @param string $label
	 * @return int
	 */
	public static function get($label = '') {
		if (isset(self::$_timers[$label]) && is_array(self::$_timers[$label]) && isset(self::$_timers[$label]['start']) && isset(self::$_timers[$label]['end'])) {
			return self::$_timers[$label]['end'] - self::$_timers[$label]['start'];
		}
		return self::end($label);
	}

	/**
	 * @static
	 * @param string|null $label
	 */
	public static function dump($label = null) {
		if ($label === null) {
			$dump = array();
			foreach(self::$_timers as $timerLabel => $timer) {
				$dump[$timerLabel] = CManager_Helper_Number::format(self::get($timerLabel) * 1000, 2, ' ', '.') . ' msec';
			}
			Zend_Debug::dump($dump, __CLASS__, true);
		} else {
			Zend_Debug::dump(CManager_Helper_Number::format(self::get($label) * 1000, 2, ' ', '.') . ' msec', $label, true);
		}
	}
}