<?php

class cm_Date_Locale {

	const DATE_MONTH_NAME = '%B';
	const DATE_MONTH_SHORT = '%b';

	const LOCALE_DEFAULT = 'en_US';

	/**
	 * @var string
	 */
	protected $_format = null;

	/**
	 * @var string
	 */
	protected $_locale = null;

	/**
	 * @var int
	 */
	protected $_date = null;

	/**
	 * @var array
	 */
	protected static $_localeData = array();

	public function __construct($date = null, $format = null, $locale = null) {
		$this->setDate($date);
		$this->setFormat($format);
		$this->setLocale($locale);
	}

	/**
	 * @param string|null $format
	 * @param string|null $locale
	 * @return bool|string
	 */
	public function toString($format = null, $locale = null) {
		if ($locale === null) {
			$locale = $this->_locale;
		}
		if ($format === null) {
			$format = $this->_format;
		}
		if (empty($locale)) {
			$locale = self::LOCALE_DEFAULT;
		}
		if ($format === null) {
			return false;
		}

		$return = $format;

		if (strpos($format, self::DATE_MONTH_NAME) !== false) {
			try {
				$localeData = $this->getLocalizedData($locale);
				$month = date('n', $this->_date);
				$monthName = $localeData->getSection('month')->getScope('wide')->getItem($month);
				$return = str_replace(self::DATE_MONTH_NAME, $monthName, $return);
			} catch (cm_Date_Exception $e) {}
		}

		if (strpos($format, self::DATE_MONTH_SHORT) !== false) {
			try {
				$localeData = $this->getLocalizedData($locale);
				$month = date('n', $this->_date);
				$monthName = $localeData->getSection('month')->getScope('abbreviated')->getItem($month);
				$return = str_replace(self::DATE_MONTH_NAME, $monthName, $return);
			} catch (cm_Date_Exception $e) {}
		}

		return strftime($return, $this->_date);
	}

	/**
	 * @param string $locale
	 * @return cm_Date_LocaleData
	 */
	public function getLocalizedData($locale) {
		if (!array_key_exists($locale, self::$_localeData)) {
			try {
				self::$_localeData[$locale] = new cm_Date_LocaleData(__DIR__ . '/Locale/' . $locale . '.xml');
			} catch (cm_Date_Exception $e) {
				self::$_localeData[$locale] = null;
				throw $e;
			}
		} elseif (self::$_localeData[$locale] === null) {
			throw new cm_Date_Exception('File for current locale not found');
		}
		return self::$_localeData[$locale];
	}

	public function setFormat($format) {
		$this->_format = (string) $format;
	}

	/**
	 * @param int|string $date
	 * @return cm_Date_Locale
	 */
	public function setDate($date) {
		$this->_date = (int) $date;
		if (!$this->_date) {
			$this->_date = time();
		}
		return $this;
	}

	/**
	 * @param string $locale
	 * @return cm_Date_Locale
	 */
	public function setLocale($locale) {
		$this->_locale = (string) $locale;
		return $this;
	}
}