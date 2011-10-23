<?php

class cm_Event_Emitter {
	private $_listeners = array();

	/**
	 * @param string $type
	 * @param function|callback $listener
	 * @return void
	 */
	public function addListener($type, $listener) {
		if (!is_callable($listener)) {
			return;
		}
		$type = (string) $type;
		if (!isset($this->_listeners[$type])) {
			$this->_listeners[$type] = array();
		}
		$this->_listeners[$type][] = $listener;
	}

	/**
	 * @param cm_Event $event
	 * @return mixed
	 */
	public function emit(cm_Event $event) {
		if (!isset($this->_listeners[$event->getType()])) {
			return;
		}

		$event->getTarget($this);
		foreach ($this->_listeners[$event->getType()] as $_listener) {
			call_user_func_array($_listener, array($event));
		}
	}

	public function hasListener($type) {
		return isset($this->_listeners[(string) $type]);
	}

	/**
	 * @param cm_Event $event
	 * @param callback $listener
	 * @return void
	 */
	public function removeListener($event, $listener) {
		$type = $event->getType();
		if (!isset($this->_listeners[$type])) {
			return;
		}

		foreach ($this->_listeners[$type] as &$_listener) {
			if ($listener == $_listener) {
				unset($_listener);
				return;
			}
		}
	}
}