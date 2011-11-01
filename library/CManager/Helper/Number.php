<?php

class CManager_Helper_Number {

	const SHORT_TYPE_1000	= '1000';
	const SHORT_TYPE_BINARY	= 'binary';
	const SHORT_TYPE_MONEY	= 'money';


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
		if (strpos($value, $point) === false) {
			return $value;
		}
		return str_replace('.', $point, rtrim(rtrim(str_replace(',', '.', $value), '0'), '.'));
	}

	/**
	 * @static
	 * @param int|float $number
	 * @param array $suffixes
	 * @param array $config
	 * @return string
	 */
	public static function short($number, array $suffixes = array(), array $config = array()) {
		$config = array_merge(array(
			'binary'		=> false,
			'point'			=> '.',
			'separator'		=> ' ',
			'accuracy'		=> 1,
			'removeZeros'	=> true
		), $config);
		if (empty($suffixes)) {
			$suffixes = array('', 'K', 'M', 'B');
		}

		$suffixesCount	= count($suffixes);
		$multiplier		= $config['binary']? 1024: 1000;
		$level			= 0;
		while ($number >= $multiplier && $level < $suffixesCount) {
			$number /= $multiplier;
			$level++;
		}

		$result = self::format(
			$number,
			(int) $config['accuracy'],
			(string) $config['separator'],
			(string) $config['point']
		);

		if ($config['removeZeros']) {
			return self::removeZeros($result, (string) $config['point']) . $suffixes[$level];
		}
		return $result . $suffixes[$level];
	}

	/**
	 * @static
	 * @param int|float $bytes
	 * @return string
	 */
	public static function bytesInHuman($bytes) {
		return self::short($bytes, array('b', 'Kb', 'Mb', 'Gb', 'Tb'), array('binary' => true));
	}
}