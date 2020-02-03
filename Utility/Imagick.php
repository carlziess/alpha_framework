<?php
/*================================================================
*   File Name：Imagick.php
*   Author：carlziess, lizhenglin@g7.com.cn
*   Create Date：2016-02-21 13:16:01
*   Description：
================================================================*/
namespace Utility;
class Imagick
{
	private $imagick, $type;
	public function __construct() {
	}
	public function __destroy() {
		if (null !== $this->imagick && $this->imagick instanceof \Imagick) {
			$this->imagick->clear ();
			$this->imagick->destroy ();
		}
	}
	public function open($path) {
		try {
			$imagick = new \Imagick ( $path );
		} catch ( \Exception $e ) {
			throw new \Exception ( sprintf ( 'Unable to open image %s', $path ), $e->getCode (), $e );
		}
		$this->imagick = $imagick;
		$this->type = ( string ) strtolower ( $this->imagick->getImageFormat () );
		return $this;
	}
	public function load($string) {
		try {
			$imagick = new \Imagick ();
			$imagick->readImageBlob ( $string );
			$imagick->setImageMatte ( true );
		} catch ( \ImagickException $e ) {
			throw new \Exception ( 'Could not load image from string', $e->getCode (), $e );
		}
		$this->imagick = $imagick;
		$this->type = ( string ) strtolower ( $this->imagick->getImageFormat () );
		return $this;
	}
	public function read($resource) {
		if (! is_resource ( $resource )) {
			throw new \Exception ( 'Variable does not contain a stream resource' );
		}
		try {
			$imagick = new \Imagick ();
			$imagick->readImageFile ( $resource );
			p ( $imagick );
		} catch ( \ImagickException $e ) {
			throw new \Exception ( ' Could not read image from resource', $e->getCode (), $e );
		}
		$this->imagick = $imagick;
		$this->type = ( string ) strtolower ( $this->imagick->getImageFormat () );
		return $this;
	}
	public function getType() {
		return $this->type;
	}
	public function thumbnail($w, $h = null, $isShape = false) {
		if (null === $h) {
			$h = $w;
		}
		if ('gif' === $this->type) {
			$imagick = $this->imagick->coalesceImages ();
			foreach ( $imagick as $frame ) {
				$frame->thumbnailImage ( $w, $h, $isShape );
			}
			$this->imagick = $imagick->optimizeImageLayers ();
		} else {
			$this->imagick->thumbnailImage ( $w, $h, $isShape );
		}
		return $this;
	}
	public function cropThumbnail($w, $h = null) {
		if (null === $h) {
			$h = $w;
		}
		if ('gif' === $this->type) {
			$imagick = $this->imagick->coalesceImages ();
			foreach ( $imagick as $frame ) {
				$frame->cropThumbnailImage ( $w, $h );
			}
			$this->imagick = $imagick->optimizeImageLayers ();
		} else {
			$this->imagick->cropThumbnailImage ( $w, $h );
		}
		return $this;
	}
	public function resizepercent($per) {
		$wh = $this->imagick->getImageGeometry ();
		$this->imagick->resizeImage ( $wh ['width'] * $per, $wh ['height'] * $per, \Imagick::FILTER_CATROM, 1 );
		return $this;
	}
	public function crop($x = 0, $y = 0, $w = null, $h = null) {
		$wh = $this->imagick->getImageGeometry ();
		if ($x < 0)
			$x = 0;
		elseif ($x > $wh ['width'])
			$x = $_w - 1;
		if ($y < 0)
			$y = 0;
		elseif ($y > $wh ['height'])
			$y = $_h - 1;
		if (null === $w)
			$w = $wh ['width'] - $x;
		if (null === $h)
			$h = $wh ['height'] - $y;
		if ($w > 0 && $h > 0) {
			if ('gif' === $this->type) {
				$imagick = $this->imagick->coalesceImages ();
				foreach ( $imagick as $frame ) {
					$frame->cropImage ( $w, $h, $x, $y );
				}
				$this->imagick = $imagick->optimizeImageLayers ();
			} else {
				$this->imagick->cropImage ( $w, $h, $x, $y );
			}
		}
		return $this;
	}
	public function resize($w, $h = null, $filter = \Imagick::FILTER_CATROM) {
		$wh = $this->imagick->getImageGeometry ();
		if ($w < 1)
			$w = 1;
		else {
			if ($w > $wh ['width'])
				$w = $wh ['width'];
		}
		
		if ($h === null)
			$h = $w;
		else {
			if ($h < 1)
				$h = 1;
			else {
				if ($h > $wh ['height'])
					$h = $wh ['height'];
			}
		}
		try {
			$this->imagick->resizeImage ( $w, $h, $filter, 1 );
		} catch ( \ImagickException $e ) {
			throw new \Exception ( 'Resize operation failed', $e->getCode (), $e );
		}
		
		return $this;
	}
	public function fill() {
	}
	public function copy() {
		return (new self ())->load ( $this->imagick->getImageBlob () );
	}
	public function strip() {
		try {
			$this->imagick->stripImage ();
		} catch ( \ImagickException $e ) {
			throw new \Exception ( 'Strip operation failed', $e->getCode (), $e );
		}
		return $this;
	}
	public function format($type = 'jpeg') {
		$this->imagick->setImageFormat ( $type );
		$this->type = $type;
		return $this;
	}
	public function quality($q = 71) {
		$a = $this->imagick->getImageCompressionQuality ();
		if (true === $q) {
			$q = $a * 0.75 ?  : 71;
		}
		if ($q < $a) {
			if ('gif' === $this->type) {
				$imagick = $this->imagick->coalesceImages ();
				foreach ( $imagick as $frame ) {
					$frame->setImageCompressionQuality ( $q );
				}
				$this->imagick = $imagick->optimizeImageLayers ();
			} else {
				$this->imagick->setImageCompressionQuality ( $q );
			}
		}
		return $this;
	}
	public function compress() {
		$this->format ();
		$this->imagick->setImageCompression ( \Imagick::COMPRESSION_JPEG );
		$this->quality ( true );
		$this->strip ();
		return $this;
	}
	public function getBlob() {
		if ('gif' === $this->type) {
			return $this->imagick->getImagesBlob ();
		}
		return $this->imagick->getImageBlob ();
	}
	public function getGeometry() {
		return $this->imagick->getImageGeometry ();
	}
	public function addWatermark($path, $x = 0, $y = 0) {
		$watermark = new Imagick ( $path );
		$draw = new \ImagickDraw ();
		$draw->composite ( $watermark->getImageCompose (), $x, $y, $watermark->getImageWidth (), $watermark->getimageheight (), $watermark );
		
		if ($this->type == 'gif') {
			$imagick = $this->imagick->coalesceImages ();
			foreach ( $imagick as $frame ) {
				$frame->drawImage ( $draw );
			}
			$this->imagick = $imagick->optimizeImageLayers ();
		} else {
			$this->imagick->drawImage ( $draw );
		}
	}
	public function addText($text, $draw, $x = 0, $y = 0, $angle = 0) {
		if ('gif' == $this->type) {
			$imagick = $this->imagick->coalesceImages ();
			foreach ( $imagick as $frame ) {
				$frame->annotateImage ( $draw, $x, $y, $angle, $text );
			}
		} else {
			$this->imagick->annotateImage ( $draw, $x, $y, $angle, $text );
		}
		return $this;
	}
	public function image() {
	}
	public function flop() {
		try {
			$this->imagick->flopImage ();
		} catch ( \ImagickException $e ) {
			throw new \Exception ( 'Horizontal Flip operation failed', $e->getCode (), $e );
		}
		return $this;
	}
	public function flip() {
		try {
			$this->imagick->flipImage ();
		} catch ( \ImagickException $e ) {
			throw new \Exception ( 'Vertical flip operation failed', $e->getCode (), $e );
		}
		return $this;
	}
	public function charcoal($radius = 0.5, $sigma = 0.5) {
		$this->imagick->charcoalImage ( $radius, $sigma );
		return $this;
	}
	public function border($c = 'rgb(220, 220, 220)', $w = 1, $h = 1) {
		$color = new \ImagickPixel ();
		$color->setColor ( $c );
		$this->imagick->borderImage ( $color, $w, $h );
		return $this;
	}
	public function oilPaint($radius) {
		$this->imagick->oilPaintImage ( $radius );
		return $this;
	}
	public function modulate($brightness, $saturation, $hue) {
		$this->imagick->modulateImage ( $brightness, $saturation, $hue );
		return $this;
	}
}
