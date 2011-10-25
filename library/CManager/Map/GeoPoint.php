<?php

class CManager_Map_GeoPoint {
	public $latitude = null;
	public $longitude = null;

	/**
	 * @param float $latitude
	 * @param float $longitude
	 */
	public function __construct($latitude, $longitude) {
		$this->latitude	= (float) $latitude;
		$this->longitude= (float) $longitude;
	}
}