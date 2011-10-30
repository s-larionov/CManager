<?php

class CManager_Helper_Number {
	/**
	* Форматирование чисел
	*
	* @param float $number			Число
	* @param mixed $accuracy		Точность (кол-во знаков после запятой)
	* @param mixed $thousandsSep	Разделитель разрядов
	* @param mixed $point			Разделитель дробной части
	* @return string
	*/
	public static function format($number, $accuracy = 0, $thousandsSep = ' ', $point = ',') {
		$number = floatval(str_replace(',', '.', $number));
		if ($number > 10000) {
			// $thousandsSep заменяем через str_replace из-за того
			// что в number_format можно передавать только один символ
			$formattedNumber = str_replace('|', $thousandsSep, number_format($number, $accuracy, $point, '|'));
		} else {
			$formattedNumber = number_format($number, $accuracy, $point, '');
		}
		return $formattedNumber;
	}

	/**
	* Удаление нулей справа у чисел
	* Например, 0.0450 -> 0.045, 4.00 -> 4 и т.д.
	*
	* @param float $value
	* @param string $point
	* @return string
	*/
	public static function removeZeros($value, $point = '.') {
		return str_replace('.', $point, rtrim(rtrim(str_replace(',', '.', $value), '0'), '.'));
	}

	/**
	 * @static
	 * @param int|float $bytes
	 * @param bool $binary
	 * @param bool $full
	 * @return string
	 */
	public static function bytesInHuman($bytes, $binary = true, $full = false) {
		if ($binary) {
			$labels = $full
				? array('byte', 'kilobyte', 'megabyte', 'gigabyte', 'terabyte', 'petabyte', 'exabyte', 'zettabyte', 'yottabyte')
				: array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
		} else {
			$labels = $full
				? array('byte', 'kilobyte', 'megabyte', 'gigabyte', 'terabyte', 'petabyte', 'exabyte', 'zettabyte', 'yottabyte')
				: array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		}

		$levelsCount = count($labels);
		$mul = $binary? 1024: 1000;
		$level = 0;
		while ($bytes >= 1024 && $level < $levelsCount) {
			$bytes /= $mul;
			$level++;
		}
		return self::removeZeros(self::format($bytes, 2, '', ','), ',') . ' ' . $labels[$level];
	}
}