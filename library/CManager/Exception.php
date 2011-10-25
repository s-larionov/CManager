<?php

class CManager_Exception extends Exception {
	protected $_rawMessage;
	
	public function __construct($message, $showTrace = true) {
		$this->_rawMessage = $message;
		$this->showTrace = $showTrace;
		
		parent::__construct($this->_getMessage($message, get_class($this),
											$this->getFile(), $this->getLine()));
	}
	
	public function getRawMessage() {
		return $this->_rawMessage;
	}

	protected function _getMessage($msg, $class, $file = false, $line = false) {
		$file = $file == false ? $this->getFile() : $file;
		$line = $line == false ? $this->getLine() : $line;
		
		
		if (PHP_SAPI == 'cli' || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
									$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
			$message  = "<pre class=\"prn-out pwAction\" rel=\"destroy\">\n";
			$message .= "$class   $file   $line\n";
			$message .= str_repeat('-', strlen($message)-1) ."\n";
			$message .= $msg ."\n\n";
			if ($this->showTrace) {
				$message .= $this->getTraceAsString() ."\n\n";
			}
			$message  .= "</pre>\n";
			
		} else {
			
			$message  = '<div style="background: #fff; color: #555; font: 15px arial; border: 1px solid #ccc; padding: 5px 8px">';
			$message .= '	<span style="font-weight: bold; font-size: 12px; padding-right: 10px; letter-spacing: .1em">'. $class .'</span> ';
			$message .= '	<span style="background: #F8EFDA; font-size: 12px; padding: 0 5px">flie <span style="font-weight: bold; letter-spacing: .1em; padding: 0 10px">'. $file .'</span> line '. $line .' </span>';
			$message .= '	<div style="border-top: 1px solid #c9c9c9; padding-top: 5px">'. $msg .'</div>';
			if ($this->showTrace) {
				$message .= '	<div style="border-top: 1px solid #c9c9c9; padding-top: 5px; width: 100%; overflow: auto">'.
						'<pre>'. $this->getTraceAsString() .'</pre></div>';
			}
			$message .= '</div>';
			
		}
		return $message;
	}
	
	public function __toString() {
		return $this->message;
	}
}

