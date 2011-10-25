<?php

class CManager_Map_Point {
	/**
	 * @var float
	 */
	public $x = 0;
	/**
	 * @var float
	 */
	public $y = 0;

	/**
	 * @param float $x
	 * @param float $y
	 */
	public function __construct($x, $y) {
		$this->x = (float)$x;
		$this->y = (float)$y;
	}

	/**
	 * @param CManager_Map_Point[] $polygon
	 * @return bool
	 */
	public function inPolygon($polygon) {
		$countPoints = count($polygon);
		$j = $countPoints - 1;
		$c = false;
		for ($i = 0; $i < $countPoints; $i++) {
			if (((($polygon[$i]['y'] <= $this->y) && ($this->y < $polygon[$j]['y'])) || (($polygon[$j]['y'] <= $this->y) && ($this->y < $polygon[$i]['y']))) &&
				($this->x > ($polygon[$j]['x'] - $polygon[$i]['x']) * ($this->y - $polygon[$i]['y']) / ($polygon[$j]['y'] - $polygon[$i]['y']) + $polygon[$i]['x'])) {
				$c = !$c;
			}
			$j = $i;
		}
		return $c;
	}
}