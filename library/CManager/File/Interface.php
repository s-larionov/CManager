<?php

interface CManager_File_Interface {
	/**
	 * @param string $filename
	 */
	public function __construct($filename);

	/**
	 * @abstract
	 * @return string
	 */
	public function getContent();

	/**
	 * @abstract
	 * @param string $content
	 * @return CManager_File_Interface
	 */
	public function setContent($content);

	/**
	 * @abstract
	 * @return string
	 */
	public function getFilename();

	/**
	 * @abstract
	 * @return boolean
	 */
	public function exists();
}
