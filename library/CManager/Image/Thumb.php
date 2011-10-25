<?php
/**
 * This is a driver for the thumbnail creating
 *
 * PHP versions 4 and 5
 *
 * LICENSE:
 *
 * The PHP License, version 3.0
 *
 * Copyright (c) 1997-2005 The PHP Group
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following url:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @author      Ildar N. Shaimordanov <ildar-sh@mail.ru>
 * @license     http://www.php.net/license/3_0.txt
 *              The PHP License, version 3.0
 */

/**
 * Cropping of fragment
 */
define('THUMBNAIL_METHOD_CROP',			1);

/**
 * Fill image in area
 */
define('THUMBNAIL_METHOD_FILL',			2);

/**
 * Fit image in area
 */
define('THUMBNAIL_METHOD_FIT',			3);

/**
 * Fit on height image in area
 */
define('THUMBNAIL_METHOD_FIT_HEIGHT',	4);

/**
 * Fit on width image in area
 */
define('THUMBNAIL_METHOD_FIT_WIDTH',	5);

/**
 * Stretch image
 */
define('THUMBNAIL_METHOD_STRETCH',		6);


/**
 * Fit on width image in area
 */
define('THUMBNAIL_MODE_WITH_FIELDS',	1);

/**
 * Stretch image
 */
define('THUMBNAIL_MODE_WITHOUT_FIELDS',	2);

class CManager_Image_Thumb {
	/**
	 * Create a GD image resource from given input.
	 *
	 * This method tried to detect what the input, if it is a file the
	 * createImageFromFile will be called, otherwise createImageFromString().
	 *
	 * @param  mixed $input The input for creating an image resource. The value
	 *                      may a string of filename, string of image data or
	 *                      GD image resource.
	 *
	 * @return resource     An GD image resource on success or false
	 * @access public
	 * @static
	 * @see    self::imageCreateFromFile(), self::imageCreateFromString()
	 */
	public static function imageCreate($input) {
		if ( is_file($input) ) {
			return self::imageCreateFromFile($input);
		} else if ( is_string($input) ) {
			return self::imageCreateFromString($input);
		} else {
			return $input;
		}
	}

	/**
	 * Create a GD image resource from file (JPEG, PNG support).
	 *
	 * @param  string $filename The image filename.
	 *
	 * @return mixed            GD image resource on success, FALSE on failure.
	 * @access public
	 * @static
	 */
	public static function imageCreateFromFile($filename) {
		if (!is_file($filename) || !is_readable($filename)) {
			throw new CManager_Image_Exception('Unable to open file "' . $filename . '"');
		}

		// determine image format
		list( , , $type) = getimagesize($filename);
		switch ($type) {
			case IMAGETYPE_JPEG:
				return imagecreatefromjpeg($filename);
				break;
			case IMAGETYPE_PNG:
				return imagecreatefrompng($filename);
				break;
			case IMAGETYPE_GIF:
				return imagecreatefromgif($filename);
				break;
		}
		throw new CManager_Image_Exception('Unsupported image type');
	}

	/**
	 * Create a GD image resource from a string data.
	 *
	 * @param  string $string The string image data.
	 *
	 * @return mixed          GD image resource on success, FALSE on failure.
	 * @access public
	 * @static
	 */
	public static function imageCreateFromString($string) {
		if (!is_string($string) || empty($string)) {
			throw new CManager_Image_Exception('Invalid image value in string');
		}
		return imagecreatefromstring($string);
	}

	/**
	 * Display rendered image (send it to browser or to file).
	 * This method is a common implementation to render and output an image.
	 * The method calls the render() method automatically and outputs the
	 * image to the browser or to the file.
	 *
	 * options:
	 *		 <pre>
	 *		 width   int	Width of thumbnail
	 *		 height  int	Height of thumbnail
	 *		 percent number Size of thumbnail per size of original image
	 *		 method  int	Method of thumbnail creating
	 *		 halign  int	Horizontal align
	 *		 valign  int	Vertical align
	 *		 </pre>
	 *
	 * @param mixed		$input		Destination image, a filename or an image string data or a GD image resource
	 * @param null		$output
	 * @param array		$options
	 * @return bool					TRUE on success or FALSE on failure.
	 * @access public
	 */
	public static function output($input, $output = null, $options = array()) {
		// Load source file and render image
		$renderImage = self::render($input, $options);
		if ( ! $renderImage ) {
			throw new CManager_Image_Exception('Error rendering image');
		}

		// Set output image type
		// By default PNG image
		$type = isset($options['type']) ? $options['type'] : IMAGETYPE_PNG;

		// Before output to browsers send appropriate headers
		if ( empty($output) ) {
			$content_type = image_type_to_mime_type($type);
			if ( ! headers_sent() ) {
				header('Content-Type: ' . $content_type);
			} else {
				throw new CManager_Image_Exception('Headers have already been sent. Could not display image.');
			}
		}

		// Define outputing function
		switch ($type) {
		case IMAGETYPE_PNG:
			$result = empty($output) ? imagepng($renderImage) : imagepng($renderImage, $output);
			break;
		case IMAGETYPE_JPEG:
			$result = empty($output) ? imagejpeg($renderImage, null, 90) : imagejpeg($renderImage, $output, 90);
			break;
		case IMAGETYPE_GIF:
			$result = empty($output) ? imagegif($renderImage) : imagegif($renderImage, $output);
			break;
		default:
			throw new CManager_Image_Exception('Image type ' . $content_type . ' not supported by PHP');
		}

		// Output image (to browser or to file)
		if ( ! $result ) {
			throw new CManager_Image_Exception('Error output image');
		}

		// Free a memory from the target image
		imagedestroy($renderImage);

		return true;
	}

	/**
	 * Draw thumbnail result to resource.
	 *
	 * @param  mixed   $input   Destination image, a filename or an image string data or a GD image resource
	 * @param  array   $options Thumbnail options
	 *
	 * @return resource
	 * @access public
	 * @see    self::output()
	 */
	public static function render($input, $options = array()) {
		// Create the source image
		$sourceImage = self::imageCreate($input);
		if ( ! is_resource($sourceImage) ) {
			throw new CManager_Image_Exception('Invalid image resource');
		}

		$sourceWidth  = imagesx($sourceImage);
		$sourceHeight = imagesy($sourceImage);

		// Set default options
		static $defOptions = array(
			'width'		=> 150,
			'height'	=> 150,
			'method'	=> THUMBNAIL_METHOD_FILL,
			'mode'		=> THUMBNAIL_MODE_WITHOUT_FIELDS,
			'percent'	=> 0,
			'halign'	=> 50, //in percents
			'valign'	=> 50, //in percents
		);

		// Если указана только ширина или только высота, то вычисляем размеры
		if (isset($options['width']) && isset($options['height'])) {
			if ($options['width'] <= 0 && $options['height'] > 0) {
				$options['width'] = intval($options['height'] * $sourceWidth / $sourceHeight);
			} else if($options['height'] <= 0 && $options['width'] > 0) {
				$options['height'] = intval($options['width'] * $sourceHeight / $sourceWidth);
			}
		}

		foreach ($defOptions as $k => $v) {
			if ( ! isset($options[$k]) ) {
				$options[$k] = $v;
			}
		}

		// размеры будущего изображения (с полями)
		$width			= (int) (isset($options['width'])? $options['width']: $sourceWidth);
		$height			= (int) (isset($options['height'])? $options['height']: $sourceHeight);

		// непосредственные размеры копируемого изображения (без полей) на оригинале
		$sourceCopyWidth= $sourceWidth;
		$sourceCopyHeight= $sourceHeight;

		// непосредственные размеры копируемого изображения (без полей) на новой картинке
		$copyWidth		= $width;
		$copyHeight		= $height;

		// положение копируемого изображения на оригинальной картинке
		$sourceX		= 0;
		$sourceY		= 0;

		// положение копируемого изображения на будущей картинке
		$X				= 0;
		$Y				= 0;

		// коэффициенты соотношения сторон
		$sourceCoefficient	= $sourceWidth / $sourceHeight;
		$coefficient		= $width / $height;

		if ($options['height'] > $sourceHeight && $options['width'] > $sourceWidth) {
			$width = $copyWidth = $sourceWidth;
			$height = $copyHeight = $sourceHeight;
		}

		switch($options['method']) {
			case THUMBNAIL_METHOD_STRETCH:
				break;
			case THUMBNAIL_METHOD_FILL:
				if ($sourceCoefficient > $coefficient) {
					$sourceCopyWidth = floor($sourceHeight * $coefficient);
					$sourceX = -floor(($sourceCopyWidth - $sourceWidth)*$options['halign']/100);
				} else {
					$sourceCopyHeight = floor($sourceWidth / $coefficient);
					$sourceY = -floor(($sourceCopyHeight - $sourceHeight)*$options['valign']/100);
				}
				break;
			case THUMBNAIL_METHOD_FIT:
				if ($options['mode'] == THUMBNAIL_MODE_WITH_FIELDS) {
					if ($sourceCoefficient > $coefficient) {
						$copyHeight = floor($width / $sourceCoefficient);
						$Y = -floor(($copyHeight - $height)*$options['valign']/100);
					} else {
						$copyWidth = floor($height * $sourceCoefficient);
						$X = floor(($width - $copyWidth)*$options['halign']/100);
					}
				} else {
					if ($sourceCoefficient > $coefficient) {
						$height = $copyHeight = floor($width / $sourceCoefficient);
					} else {
						$width = $copyWidth = floor($height * $sourceCoefficient);
					}
				}
				break;
			case THUMBNAIL_METHOD_FIT_HEIGHT:
				if ($sourceCoefficient < $coefficient) {
					$width = floor($copyWidth = $copyHeight * $sourceCoefficient);
				} else {
					$copyWidth = floor($height * $sourceCoefficient);
					$X = floor(($width - $copyWidth)*$options['halign']/100);
				}
				break;
			case THUMBNAIL_METHOD_FIT_WIDTH:
				if ($sourceCoefficient > $coefficient) {
					$height = $copyHeight = floor($copyWidth / $sourceCoefficient);
				} else {
					$copyHeight = floor($width / $sourceCoefficient);
					$Y = -floor(($copyHeight - $height)*$options['valign']/100);
				}
				break;
			case THUMBNAIL_METHOD_CROP:
			default:
				if ($options['percent']) {
					$sourceCopyWidth = floor($options['percent'] * $sourceWidth);
					$sourceCopyHeight = floor($options['percent'] * $sourceHeight);
				} else {
					$sourceCopyWidth = $options['width'];
					$sourceCopyHeight = $options['height'];
				}
		}

		// Create the target image
		if ( function_exists('imagecreatetruecolor') ) {
			$targetImage = imagecreatetruecolor($width, $height);
		} else {
			$targetImage = imagecreate($width, $height);
		}
		if (isset($options['fillColor'])) {
			$colorRGB = self::_rgb2array($options['fillColor']);
			$color = imagecolorallocate($targetImage, $colorRGB[0], $colorRGB[1], $colorRGB[2]);
			imagefill($targetImage, 1, 1, $color);
		}
		if ( ! is_resource($targetImage) ) {
			throw new CManager_Image_Exception('Cannot initialize new GD image stream');
		}

		// Copy the source image to the target image
		if ($options['method'] == THUMBNAIL_METHOD_CROP) {
			$result = imagecopy($targetImage, $sourceImage, $X, $Y, $sourceX, $sourceY, $sourceCopyWidth, $sourceCopyHeight);
		} elseif (function_exists('imagecopyresampled')) {
			$result = imagecopyresampled($targetImage, $sourceImage, $X, $Y, $sourceX, $sourceY, $copyWidth, $copyHeight, $sourceCopyWidth, $sourceCopyHeight);
		} else {
			$result = imagecopyresized($targetImage, $sourceImage, $X, $Y, $sourceX, $sourceY, $copyWidth, $copyHeight, $sourceCopyWidth, $sourceCopyHeight);
		}
		if (!$result) {
			throw new CManager_Image_Exception('Cannot resize image');
		}

		// Free a memory from the source image
		imagedestroy($sourceImage);

		// Save the resulting thumbnail
		return $targetImage;
	}

	/**
	 * Convert color from hex in XXXXXX (eg. FFFFFF, 000000, FF0000) to array(R, G, B)
	 * of integers (0-255).
	 *
	 * name: rgb2array
	 * author: Yetty
	 * @param string $rgb hex in XXXXXX (eg. FFFFFF, 000000, FF0000)
	 * @return array; array(R, G, B) of integers (0-255)
	 */
	protected static function _rgb2array($rgb) {
		return array(
			base_convert(substr($rgb, 0, 2), 16, 10),
			base_convert(substr($rgb, 2, 2), 16, 10),
			base_convert(substr($rgb, 4, 2), 16, 10),
		);
	}
}
