<?php

/**
 * @group taxonomy
 *
 * @covers ::get_term_field
 */
class Tests_Term_getTermField extends WP_UnitTestCase {

	public static $taxonomy = 'wptests_tax';

	public static $term;

	/**
	 * Set up shared fixtures.
	 *
	 * @param WP_UnitTest_Factory $factory
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		register_taxonomy( self::$taxonomy, 'post' );
		self::$term = $factory->term->create_and_get(
			array(
				'taxonomy'    => self::$taxonomy,
				'description' => wpautop( 'Test term description' ),
			)
		);
	}

	public function set_up() {
		parent::set_up();
		// Required as taxonomies are reset between tests.
		register_taxonomy( self::$taxonomy, 'post' );
	}

	/**
	 * @ticket 34245
	 */
	public function test_get_term_field_should_not_return_error_for_empty_taxonomy() {
		$term = self::$term;

		$found = get_term_field( 'taxonomy', $term->term_id, '' );
		$this->assertNotWPError( $found );
		$this->assertSame( self::$taxonomy, $found );
	}

	/**
	 * @ticket 34245
	 */
	public function test_get_term_field_supplying_a_taxonomy() {
		$term = self::$term;

		$found = get_term_field( 'taxonomy', $term->term_id, $term->taxonomy );
		$this->assertSame( self::$taxonomy, $found );
	}

	/**
	 * @ticket 34245
	 */
	public function test_get_term_field_supplying_no_taxonomy() {
		$term = self::$term;

		$found = get_term_field( 'taxonomy', $term->term_id );
		$this->assertSame( self::$taxonomy, $found );
	}

	/**
	 * @ticket 34245
	 */
	public function test_get_term_field_should_accept_a_WP_Term_id_or_object() {
		$term = self::$term;

		$this->assertInstanceOf( 'WP_Term', $term );
		$this->assertSame( $term->term_id, get_term_field( 'term_id', $term ) );
		$this->assertSame( $term->term_id, get_term_field( 'term_id', $term->data ) );
		$this->assertSame( $term->term_id, get_term_field( 'term_id', $term->term_id ) );
	}

	/**
	 * @ticket 34245
	 */
	public function test_get_term_field_invalid_taxonomy_should_return_WP_Error() {
		$term = self::$term;

		$found = get_term_field( 'taxonomy', $term, 'foo-taxonomy' );
		$this->assertWPError( $found );
		$this->assertSame( 'invalid_taxonomy', $found->get_error_code() );
	}

	/**
	 * @ticket 34245
	 */
	public function test_get_term_field_invalid_term_should_return_WP_Error() {
		$found = get_term_field( 'taxonomy', 0, self::$taxonomy );

		$this->assertWPError( $found );
		$this->assertSame( 'invalid_term', $found->get_error_code() );

		$_found = get_term_field( 'taxonomy', 0 );

		$this->assertWPError( $_found );
		$this->assertSame( 'invalid_term', $_found->get_error_code() );
	}

	public function test_get_term_field_term_id() {
		$term = self::$term;

		$this->assertSame( $term->term_id, get_term_field( 'term_id', $term ) );
		$this->assertSame( $term->term_id, get_term_field( 'term_id', $term->data ) );
		$this->assertSame( $term->term_id, get_term_field( 'term_id', $term->term_id ) );
	}

	public function test_get_term_field_name() {
		$name = 'baz';

		$term = self::factory()->term->create_and_get(
			array(
				'name'     => $name,
				'taxonomy' => self::$taxonomy,
			)
		);

		$this->assertSame( $name, get_term_field( 'name', $term ) );
		$this->assertSame( $name, get_term_field( 'name', $term->data ) );
		$this->assertSame( $name, get_term_field( 'name', $term->term_id ) );
	}

	public function test_get_term_field_slug_when_slug_is_set() {
		$slug = 'baz';

		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => self::$taxonomy,
				'slug'     => $slug,
			)
		);

		$this->assertSame( $slug, get_term_field( 'slug', $term ) );
		$this->assertSame( $slug, get_term_field( 'slug', $term->data ) );
		$this->assertSame( $slug, get_term_field( 'slug', $term->term_id ) );
	}

	public function test_get_term_field_slug_when_slug_falls_back_from_name() {
		$name = 'baz';

		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => self::$taxonomy,
				'name'     => $name,
			)
		);

		$this->assertSame( $name, get_term_field( 'slug', $term ) );
		$this->assertSame( $name, get_term_field( 'slug', $term->data ) );
		$this->assertSame( $name, get_term_field( 'slug', $term->term_id ) );
	}

	public function test_get_term_field_slug_when_slug_and_name_are_not_set() {
		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => self::$taxonomy,
			)
		);

		$this->assertSame( $term->slug, get_term_field( 'slug', $term ) );
		$this->assertSame( $term->slug, get_term_field( 'slug', $term->data ) );
		$this->assertSame( $term->slug, get_term_field( 'slug', $term->term_id ) );
	}

	public function test_get_term_field_taxonomy() {
		$term = self::$term;

		$this->assertSame( self::$taxonomy, get_term_field( 'taxonomy', $term ) );
		$this->assertSame( self::$taxonomy, get_term_field( 'taxonomy', $term->data ) );
		$this->assertSame( self::$taxonomy, get_term_field( 'taxonomy', $term->term_id ) );
	}

	public function test_get_term_field_description() {
		$description = wpautop( 'Test term description' );

		$term = self::$term;

		$this->assertSame( $description, get_term_field( 'description', $term ) );
		$this->assertSame( $description, get_term_field( 'description', $term->data ) );
		$this->assertSame( $description, get_term_field( 'description', $term->term_id ) );
	}

	public function test_get_term_field_parent() {
		$parent = self::$term;
		$term   = self::factory()->term->create_and_get(
			array(
				'taxonomy' => self::$taxonomy,
				'parent'   => $parent->term_id,
			)
		);

		$this->assertSame( $parent->term_id, get_term_field( 'parent', $term ) );
		$this->assertSame( $parent->term_id, get_term_field( 'parent', $term->data ) );
		$this->assertSame( $parent->term_id, get_term_field( 'parent', $term->term_id ) );
	}
}
