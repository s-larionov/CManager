<?php

class cm_DOM_Exception extends cm_Exception {
	public function __construct($error, $xml = false) {
		if (!is_array($error)) {
			$error = array($error);
		}

		$xml = $xml !== false ? explode("\n", $xml) : false;
		$message = '';
		foreach ($error as $i) {
			if (is_string($i)) {
				$message .= $i;
			} elseif (get_class($i) === 'LibXMLError') {
				$message .= $this->LibXMLError2String($i, $xml);
			}
		}

		libxml_clear_errors();

		parent::__construct($message);
	}

	private function LibXMLError2String(LibXMLError $error, $xml = false) {
		$return = (PHP_SAPI != 'cli' ? '<pre>' : '');
		if ($xml) {
			if ($error->line > 0) {
				$return .= $xml[$error->line - 1] . "\n";
				$return .= str_repeat('-', $error->column) . "^\n";
			} elseif ($xml) {
				$return .= str_replace(array('<', '>'), array('&lt;', '&gt;'), implode("\n", $xml)) ."\n\n";
			}
		}
        
		switch ($error->level) {
			case LIBXML_ERR_WARNING:
				$return .= "Warning $error->code: ";
			break;
				case LIBXML_ERR_ERROR:
				$return .= "Error $error->code: ";
			break;
			case LIBXML_ERR_FATAL:
				$return .= "Fatal Error $error->code: ";
			break;
		}
		
		$return .= trim($error->message) .
			"\nСтрока: $error->line, " .
			"Колонка: $error->column\n\n";
		
		if ($error->file) {
			$return .= "\nФайл: $error->file";
		}
		
		return $return . (PHP_SAPI != 'cli' ? '</pre>' : '');
	}
}