<?php

/**
 * @group image
 */
abstract class WP_Image_UnitTestCase extends WP_UnitTestCase {

	/**
	 * Set the image editor engine according to the unit test's specification
	 */
	public function setUp() {
		parent::setUp();

		if ( ! call_user_func( array( $this->editor_engine, 'test' ) ) ) {
			$this->markTestSkipped( sprintf('The image editor engine %s is not supported on this system', $this->editor_engine) );
		}

		add_filter( 'wp_image_editors', array( $this, 'setEngine' ), 10, 2 );
	}

	/**
	 * Undo the image editor override
	 */
	public function tearDown() {
		parent::tearDown();

		remove_filter( 'wp_image_editors', array( $this, 'setEngine' ), 10, 2 );
		parent::tearDown();
	}

	/**
	 * Override the image editor engine
	 * @return string
	 */
	public function setEngine( $editors ) {
		return array( $this->editor_engine );
	}

	/**
	 * Helper assertion for testing alpha on images using GD library
	 *
	 * @param  string $image_path
	 * @param  array $point      array(x,y)
	 * @param  int $alpha
	 */
	protected function assertImageAlphaAtPointGD( $image_path, $point, $alpha ) {

		$im = imagecreatefrompng( $image_path );
		$rgb = imagecolorat($im, $point[0], $point[1]);

		$colors = imagecolorsforindex($im, $rgb);

		$this->assertEquals( $alpha, $colors['alpha'] );
	}

	/**
	 * Helper assertion for testing alpha on images using Imagick
	 *
	 * @param string $image_path
	 * @param array $point      array(x,y)
	 * @param int $expected
	 */
	protected function assertImageAlphaAtPointImagick( $image_path, $point, $expected ) {
		$im = new Imagick( $image_path );
		$pixel = $im->getImagePixelColor( $point[0], $point[1] );
		$color = $pixel->getColorValue( imagick::COLOR_ALPHA );
		$this->assertEquals( $expected, $color );
	}
}
