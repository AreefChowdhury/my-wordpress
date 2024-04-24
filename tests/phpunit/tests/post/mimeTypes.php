<?php
/**
 * Unit tests covering post mime types.
 *
 * @ticket 59195
 *
 * @group post
 *
 * @covers ::get_available_post_mime_types
 */

class Tests_Get_Available_Post_Mime_Types extends WP_UnitTestCase {
	/**
	 * Test get_available_post_mime_types.
	 */
	public function test_get_available_post_mime_types() {
		// Upload a JPEG image.
		$filename = DIR_TESTDATA . '/images/test-image.jpg';
		$contents = file_get_contents( $filename );
		$upload   = wp_upload_bits( wp_basename( $filename ), null, $contents );
		$this->assertEmpty( $upload['error'] );
		$this->_make_attachment( $upload );

		// Upload a PDF file.
		$filename = DIR_TESTDATA . '/images/test-alpha.pdf';
		$contents = file_get_contents( $filename );
		$upload   = wp_upload_bits( wp_basename( $filename ), null, $contents );
		$this->assertEmpty( $upload['error'] );
		$this->_make_attachment( $upload );

		$mime_types = get_available_post_mime_types();

		$this->assertIsArray( $mime_types );
		$this->assertSame( array( 'image/jpeg', 'application/pdf' ), $mime_types );
	}

	/**
	 * Test handling of nulls in get_available_post_mime_types.
	 */
	public function test_get_available_post_mime_types_with_null() {
		// Add filter to inject null into the mime types array.
		add_filter( 'pre_get_available_post_mime_types', array( $this, 'filter_add_null_to_post_mime_types' ) );

		$mime_types = get_available_post_mime_types();
		$this->assertIsArray( $mime_types );
		$this->assertEqualsCanonicalizing( array( 'image/jpeg', 'image/png' ), $mime_types );

		// Remove filter.
		remove_filter( 'pre_get_available_post_mime_types', array( $this, 'filter_add_null_to_post_mime_types' ) );
	}

	/**
	 * Filter to inject null into the mime types array.
	 *
	 * @param string $type Post type.
	 * @return array
	 */
	public function filter_add_null_to_post_mime_types( $type ) {
		return array( 'image/jpeg', null, 'image/png' );
	}
}
