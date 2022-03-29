<?php

/**
 * Tests specific to managing network options in multisite.
 *
 * Some tests will run in single site as the `_network_option()` functions
 * are available and internally use `_option()` functions as fallbacks.
 *
 * @group option
 * @group ms-option
 * @group multisite
 */
class Tests_Option_NetworkOption extends WP_UnitTestCase {

	/**
	 * @group ms-required
	 */
	public function test_add_network_option_not_available_on_other_network() {
		$id     = self::factory()->network->create();
		$option = __FUNCTION__;
		$value  = __FUNCTION__;

		add_site_option( $option, $value );
		$this->assertFalse( get_network_option( $id, $option, false ) );
	}

	/**
	 * @group ms-required
	 */
	public function test_add_network_option_available_on_same_network() {
		$id     = self::factory()->network->create();
		$option = __FUNCTION__;
		$value  = __FUNCTION__;

		add_network_option( $id, $option, $value );
		$this->assertSame( $value, get_network_option( $id, $option, false ) );
	}

	/**
	 * @group ms-required
	 */
	public function test_delete_network_option_on_only_one_network() {
		$id     = self::factory()->network->create();
		$option = __FUNCTION__;
		$value  = __FUNCTION__;

		add_site_option( $option, $value );
		add_network_option( $id, $option, $value );
		delete_site_option( $option );
		$this->assertSame( $value, get_network_option( $id, $option, false ) );
	}

	/**
	 * @ticket 22846
	 * @group ms-excluded
	 */
	public function test_add_network_option_is_not_stored_as_autoload_option() {
		$key = __FUNCTION__;

		add_network_option( null, $key, 'Not an autoload option' );

		$options = wp_load_alloptions();

		$this->assertArrayNotHasKey( $key, $options );
	}

	/**
	 * @ticket 22846
	 * @group ms-excluded
	 */
	public function test_update_network_option_is_not_stored_as_autoload_option() {
		$key = __FUNCTION__;

		update_network_option( null, $key, 'Not an autoload option' );

		$options = wp_load_alloptions();

		$this->assertArrayNotHasKey( $key, $options );
	}

	/**
	 * @dataProvider data_network_id_parameter
	 *
	 * @param $network_id
	 * @param $expected_response
	 */
	public function test_add_network_option_network_id_parameter( $network_id, $expected_response ) {
		$option = rand_str();
		$value  = rand_str();

		$this->assertSame( $expected_response, add_network_option( $network_id, $option, $value ) );
	}

	/**
	 * @dataProvider data_network_id_parameter
	 *
	 * @param $network_id
	 * @param $expected_response
	 */
	public function test_get_network_option_network_id_parameter( $network_id, $expected_response ) {
		$option = rand_str();

		$this->assertSame( $expected_response, get_network_option( $network_id, $option, true ) );
	}

	public function data_network_id_parameter() {
		return array(
			// Numeric values should always be accepted.
			array( 1, true ),
			array( '1', true ),
			array( 2, true ),

			// Null, false, and zero will be treated as the current network.
			array( null, true ),
			array( false, true ),
			array( 0, true ),
			array( '0', true ),

			// Other truthy or string values should be rejected.
			array( true, false ),
			array( 'string', false ),
		);
	}

	/**
	 * @ticket 37181
	 * @group ms-required
	 */
	public function test_meta_api_use_values_in_network_option() {
		$network_id = self::factory()->network->create();
		$option     = __FUNCTION__;
		$value      = __FUNCTION__;

		add_metadata( 'site', $network_id, $option, $value, true );
		$this->assertEquals( get_metadata( 'site', $network_id, $option ), array( get_network_option( $network_id, $option, true ) ) );
	}

	/**
	 * @ticket 37181
	 * @group ms-required
	 */
	function test_funky_post_meta() {
		$network_id      = self::factory()->network->create();
		$option          = __FUNCTION__;
		$classy          = new StdClass();
		$classy->ID      = 1;
		$classy->stringy = 'I love slashes\\\\';
		$funky_meta[]    = $classy;

		$classy          = new StdClass();
		$classy->ID      = 2;
		$classy->stringy = 'I love slashes\\\\ more';
		$funky_meta[]    = $classy;

		// Add a network meta item
		$this->assertIsInt( add_metadata( 'site', $network_id, $option, $funky_meta, true ) );

		//Check they exists
		$this->assertEquals( $funky_meta, get_network_option( $network_id, $option ) );
	}

	/**
	 * @ticket 37181
	 * @group ms-required
	 */
	public function test_meta_api_multiple_values_in_network_option() {
		$network_id = self::factory()->network->create();
		$option     = __FUNCTION__;
		add_metadata( 'site', $network_id, $option, 'monday', true );
		add_metadata( 'site', $network_id, $option, 'tuesday', true );
		add_metadata( 'site', $network_id, $option, 'wednesday', true );
		$this->assertEquals( 'monday', get_network_option( $network_id, $option, true ) );
	}

	/**
	 * @ticket 37181
	 * @group ms-required
	 */
	public function test_register_meta_network_option_single_false() {
		$network_id = self::factory()->network->create();
		$option     = __FUNCTION__;
		$value      = __FUNCTION__;
		register_meta( 'site', $option, array(
			'type'    => 'string',
			'default' => $value,
			'single'  => false,
		) );

		$this->assertSame( $value, get_network_option( $network_id, $option ) );
	}

	/**
	 * @ticket 37181
	 * @group ms-required
	 */
	public function test_register_meta_network_option_single_true() {
		$network_id = self::factory()->network->create();
		$option     = __FUNCTION__;
		$value      = __FUNCTION__;
		register_meta( 'site', $option, array(
			'type'    => 'string',
			'default' => $value,
			'single'  => true,
		) );

		$this->assertSame( $value, get_network_option( $network_id, $option ) );
	}

	/**
	 * @ticket 37181
	 * @group ms-required
	 */
	public function test_register_meta_network_option_value() {
		$network_id = self::factory()->network->create();
		$option     = __FUNCTION__;
		$value      = __FUNCTION__;
		register_meta( 'site', $option, array(
			'type'    => 'string',
			'default' => $value,
			'single'  => true,
		) );

		add_metadata( 'site', $network_id, $option, 'monday', true );
		$this->assertSame( 'monday', get_network_option( $network_id, $option ) );
	}
}
