<?php

class CManager_Map_Converter_Mercator {
	/**
	 * @var int
	 */
	protected $_size = 1024;

	/**
	 * @var array
	 */
	protected $_f = array(
		2048	=> 5.667,
		1024	=> 2.837,
		512		=> 1.418,
	);

	/**
	 * @param int $size
	 */
	public function __construct($size = 1024) {
		$this->_size = $size;
	}

	/**
	 * @param float $latitude
	 * @param float $longitude
	 * @param int $size
	 * @return CManager_Map_Point
	 */
	public function fromLatitudeLongitude($latitude, $longitude, $size = null) {
		if (!$size) {
			$size = $this->_size;
		}

		if (!isset($this->_f[$size])) {
			// throw exception ...
		}

		return new CManager_Map_Point(
			(int)($size / 2 + ($longitude * $this->_f[$size])),
			(int)($size / 2 - rad2deg(log(tan((pi() / 4) + deg2rad($latitude) / 2))) * $this->_f[$size])
		);
	}

	/**
	 * @param float $x
	 * @param float $y
	 * @param int $size
	 * @return CManager_Map_GeoPoint
	 * @throws CManager_Exception
	 */
	public function toLatitudeLongitude($x, $y, $size = null) {
		if (!$size) {
			$size = $this->_size;
		}

		if (!isset($this->_f[$size])) {
			throw new CManager_Exception("Point [{$x}:{$y}] not defined");
		}

		return new CManager_Map_GeoPoint(
			($size * .5) * (1 - (log(tan(deg2rad($y) * .5 + M_PI * .25))) / M_PI),
			($x + 180.0) * $size / 360.0
		);
	}

	/**
	 * @param float $x
	 * @param float $y
	 * @param float $fromSize
	 * @param float $toSize
	 * @return CManager_Map_Point
	 */
	public function toLocalPoint($x, $y, $fromSize, $toSize) {
		if ($fromSize == $toSize) {
			return new CManager_Map_Point($x, $y);
		}

		$point = $this->toLatitudeLongitude($x, $y, $fromSize);
		return $this->fromLatitudeLongitude($point->latitude, $point->longitude, $toSize);
	}
}