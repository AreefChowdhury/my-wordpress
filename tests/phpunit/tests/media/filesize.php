<?php

/**
 * @group media
 * @group media_filesize
 */
class Tests_Image_Filesize extends WP_UnitTestCase {
	function tear_down() {
		$this->remove_added_uploads();

		parent::tear_down();
	}

	/**
	 * Check that filesize meta is generated for jpegs.
	 *
	 * @ticket 49412
	 * @covers ::wp_get_attachment_metadata
	 */
	function test_filesize_in_jpg_meta() {
		$attachment = $this->factory->attachment->create_upload_object( DIR_TESTDATA . '/images/33772.jpg' );

		$metadata = wp_get_attachment_metadata( $attachment );

		$this->assertEquals( wp_filesize( get_attached_file( $attachment ) ), $metadata['filesize'] );

		foreach ( $metadata['sizes'] as $intermediate_size ) {
			$this->assertArrayHasKey( 'filesize', $intermediate_size );
			$this->assertNotEmpty( $intermediate_size['filesize'] );
			$this->assertIsNumeric( $intermediate_size['filesize'] );
		}
	}

	/**
	 * Check that filesize meta is generated for pngs.
	 *
	 * @ticket 49412
	 * @covers ::wp_get_attachment_metadata
	 */
	function test_filesize_in_png_meta() {
		$attachment = $this->factory->attachment->create_upload_object( DIR_TESTDATA . '/images/test-image.png' );

		$metadata = wp_get_attachment_metadata( $attachment );

		$this->assertEquals( wp_filesize( get_attached_file( $attachment ) ), $metadata['filesize'] );

		foreach ( $metadata['sizes'] as $intermediate_size ) {
			$this->assertTrue( ! empty( $intermediate_size['filesize'] ) && is_numeric( $intermediate_size['filesize'] ) );
		}
	}

	/**
	 * Check that filesize meta is generated for pdfs.
	 *
	 * @ticket 49412
	 * @covers ::wp_get_attachment_metadata
	 */
	function test_filesize_in_pdf_meta() {
		$attachment = $this->factory->attachment->create_upload_object( DIR_TESTDATA . '/images/wordpress-gsoc-flyer.pdf' );

		$metadata = wp_get_attachment_metadata( $attachment );

		$this->assertEquals( wp_filesize( get_attached_file( $attachment ) ), $metadata['filesize'] );
	}

	/**
	 * Adds psd to allowed mime types for multisite testing.
	 *
	 * @param array $mimes Unfiltered mime types.
	 * @return array Filtered mime types.
	 */
	public function allow_psd_mime_type( $mimes ) {
		$mimes['psd'] = 'application/octet-stream';
		return $mimes;
	}

	/**
	 * Check that filesize meta is generated for psds.
	 *
	 * @ticket 49412
	 * @covers ::wp_get_attachment_metadata
	 */
	function test_filesize_in_psd_meta() {
		// PSD mime type is not allowed by default on multisite.
		if ( is_multisite() ) {
			add_filter( 'upload_mimes', array( $this, 'allow_psd_mime_type' ), 10, 2 );
		}

		$attachment = $this->factory->attachment->create_upload_object( DIR_TESTDATA . '/images/test-image.psd' );

		$metadata = wp_get_attachment_metadata( $attachment );

		$this->assertEquals( wp_filesize( get_attached_file( $attachment ) ), $metadata['filesize'] );

		if ( is_multisite() ) {
			remove_filter( 'upload_mimes', array( $this, 'allow_psd_mime_type' ) );
		}
	}
}
