<?php

class CManager_String {
	/**
	* Склонение кол-венных сущ.
	*
	* @param int $n
	* @param string $form1	Форма 1: 1 элемент
	* @param string $form2	Форма 2: 2 элемента
	* @param string $form5	Форма 5: 5 элементов
	* @return string
	*/
	public static function pluralForm($n, $form1, $form2, $form5) {
		$n = abs($n) % 100;
		$n1 = $n % 10;
		if ($n > 10 && $n < 20) {
			return $form5;
		}
		if ($n1 > 1 && $n1 < 5) {
			return $form2;
		}
		if ($n1 == 1) {
			return $form1;
		}
		return $form5;
	}

	/**
	 * Склонение кол-венных сущ. в строке по шаблону
	 *
	 * Примеры:
	 *   Уже {%n [день|дня|дней]} {[прошел|прошло]}
	 *   {%n [монета|монеты|монет]}
	 *   {[целая|целых]}
	 *
	 * @param int $n
	 * @param string $template	Строка, содержащая {%n [form1|form2|form5]} %n и form5 можно не указывать
	 * @return string
	 */
	public static function pluralFormParse($template, $n) {
		$result = $template;

		// выполняем поиск соответствий
		preg_match_all('~\\{(?:%n\s*)?(\\[([^\\]]+?|[^\\]]+?)\\])\\}~', $template, $matches, PREG_SET_ORDER);

		// список уже выполненных замен (для исключения повторной замены одного и того же)
		$yetReplaced = array();

		foreach($matches as $match) {
			// если точно такое же совпадение уже было, то пропускаем его
			if (isset($yetReplaced[$match[0]])) {
				continue;
			}
			// подготавливаем формы слова (1, 2 и 5)
			// если 5-ая форма не указана, то берем 2-ую
			$forms = explode('|', $match[2]);
			if (!isset($forms[2])) {
				$forms[2] = $forms[1];
			}
			// подготавливаем непосредственно числительное
			$word = self::pluralForm($n, $forms[0], $forms[1], $forms[2]);

			$replace = str_replace(array($match[1], '%n'), array($word, $n), $match[0]);
			$replace = trim($replace, '{}');

			// заменяем
			$result = str_replace($match[0], $replace, $result);
			$yetReplaced[$match[0]] = true;
		}

		return $result;
	}

	/**
	 * @static
	 * @param string $text
	 * @param int $cutSymbols
	 * @param string $cutText
	 * @return string
	 */
	public static function cut($text, $cutSymbols = 100, $cutText = '…') {
		if (is_numeric($cutSymbols)) {
			$len = mb_strlen($text);
			if ($len > $text) {
				$i = (int) $cutSymbols;
				$spaceSymbols = array("\n", "\r", "\t", " ", ",");
				while (($symb = mb_substr($text, $i, 1)) && !in_array($symb, $spaceSymbols) && $i < $len) {
					$i++;
				}
				$text = mb_substr($text, 0, $i) . ($i < $len? $cutText: '');
			}
		}
		return $text;
	}

	public static function prepareText($text, $trimSymbols = null, $cutText = '…') {
		$text = trim(preg_replace("~(\\r?\\n){2,}~", "\n\n", $text));
		$text = nl2br(htmlspecialchars(self::cut($text, $trimSymbols, $cutText)));
		return $text;
	}
}