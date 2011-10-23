<?php

class cm_Date {
	protected $_date = null;
	protected $_locale = null;

	public function __construct($date = null, $locale = null) {
		if ($date !== null) {
			$this->_date = self::_toValidDate($date);
		} else {
			$this->_date = time();
		}
		if ($locale !== null) {
			$this->_locale = (string) $locale;
		}
	}

	/**
	 * @param array $options
	 * @return bool|string
	 */
	public function toString($options = array()) {
		$defaultOptions = array(
			'DATE_TPL_TODAY'		=> 'Today',
			'DATE_TPL_YESTERDAY'	=> 'Yesterday',
			'DATE_TPL_DEFAULT'		=> '%B, %d'
		);
		if ($options === null || !is_array($options)) {
			$options = $defaultOptions;
		} else {
			$options = array_merge($defaultOptions, $options);
		}

		// разница дат в днях
		$diff = (intval(time()/86400) - intval($this->_date/86400));
		
		switch(true) {
			case $diff == 0:
				$format = $options['DATE_TPL_TODAY'];
				break;
			case $diff == 1:
				$format = $options['DATE_TPL_YESTERDAY'];
				break;
			default:
				$format = $options['DATE_TPL_DEFAULT'];
		}

		$locale = new cm_Date_Locale($this->_date, $format, $this->_locale);
		return $locale->toString($format);
	}

	/**
	 * @static
	 * @param string|int $date
	 * @return bool|int
	 */
	protected static function _toValidDate($date) {
		switch(true) {
			case is_numeric($date):
				$iDate = intval($date);
				// todo: разобраться как сделать это более аккуратно и правильно
				if ($iDate === 1 || substr($date, -3) === '000') {// значит дата в формате unixtime, только в мсек (пустыми)
					return (int) ($date / 1000);
				}
				return $iDate;
				break;
			//case is_string($date):
			default:
				$time = strtotime($date);
				if ($time) {
					return $time;
				}
				break;
		}
		return false;
	}

	/**
	 * @static
	 * @param string|int $date1
	 * @param string|int $date2
	 * @param array $options
	 * @return string
	 */
	public static function diffDate($date1, $date2 = null, $options = null) {
		$date1 = self::_toValidDate($date1);

		if (is_null($date2)) {
			$date2 = time();
		} else {
			$date2 = self::_toValidDate($date2);
		}

		// разница дат в днях
		$diff = (intval($date2/86400) - intval($date1/86400));

		$defaultOptions = array(
			'DIFF_DATE_TPL_TODAY'		=> 'Today',
			'DIFF_DATE_TPL_YESTERDAY'	=> 'Yesterday',
			'DIFF_DATE_TPL_DAYS_AGO'	=> '{%n [day|days]} ago',
			'DIFF_DATE_TPL_WEEKS_AGO'	=> '{%n [week|weeks]} ago',
			'DIFF_DATE_TPL_MONTHS_AGO'	=> '{%n [month|months]} ago',
			'DIFF_DATE_TPL_DEFAULT'		=> '%Y-%m-%d'
		);
		if ($options === null || !is_array($options)) {
			$options = $defaultOptions;
		} else {
			$options = array_merge($defaultOptions, $options);
		}

		switch(true) {
			case $diff == 0 && ($options['DIFF_DATE_TPL_TODAY'] != $options['DIFF_DATE_TPL_DEFAULT']):
				$return = $options['DIFF_DATE_TPL_TODAY'];
				break;
			case $diff == 1 && ($options['DIFF_DATE_TPL_YESTERDAY'] != $options['DIFF_DATE_TPL_DEFAULT']):
				$return = $options['DIFF_DATE_TPL_YESTERDAY'];
				break;
			case $diff < 7 && ($options['DIFF_DATE_TPL_DAYS_AGO'] != $options['DIFF_DATE_TPL_DEFAULT']):
				$return = cm_String::pluralFormParse($options['DIFF_DATE_TPL_DAYS_AGO'], $diff);
				break;
			case $diff < 30 && ($options['DIFF_DATE_TPL_WEEKS_AGO'] != $options['DIFF_DATE_TPL_DEFAULT']):
				$return = cm_String::pluralFormParse($options['DIFF_DATE_TPL_WEEKS_AGO'], round($diff/7));
				break;
			case $diff < 365 && ($options['DIFF_DATE_TPL_MONTHS_AGO'] != $options['DIFF_DATE_TPL_DEFAULT']):
				$return = cm_String::pluralFormParse($options['DIFF_DATE_TPL_MONTHS_AGO'], round($diff/30));
				break;
			default:
				$return = strftime($options['DIFF_DATE_TPL_DEFAULT'], $date1);
		}

		return $return;
	}

	/**
	 * @static
	 * @param string|int $date
	 * @param array $options
	 * @return string
	 */
	public static function beautiful($date, $options = null) {
		return self::diffDate($date, null, $options);
	}
}