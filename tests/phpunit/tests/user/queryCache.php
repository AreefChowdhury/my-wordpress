<?php
/**
 * Test WP_User Query, in wp-includes/user.php
 *
 * @group user
 */
class Tests_User_Query_Cache extends WP_UnitTestCase {
	protected static $author_ids;
	protected static $sub_ids;
	protected static $editor_ids;
	protected static $contrib_id;
	protected static $admin_ids;

	protected $user_id;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$author_ids = $factory->user->create_many(
			4,
			array(
				'role' => 'author',
			)
		);

		self::$sub_ids = $factory->user->create_many(
			2,
			array(
				'role' => 'subscriber',
			)
		);

		self::$editor_ids = $factory->user->create_many(
			3,
			array(
				'role' => 'editor',
			)
		);

		self::$contrib_id = $factory->user->create(
			array(
				'role' => 'contributor',
			)
		);

		self::$admin_ids = $factory->user->create_many(
			2,
			array(
				'role' => 'administrator',
			)
		);
	}

	/**
	 * @dataProvider data_returning_field_subset_as_array
	 *
	 * @param array $args
	 */
	public function test_query_cache( $args ) {
		$query1       = new WP_User_Query( $args );
		$users1       = $query1->get_results();
		$users_total1 = $query1->get_total();

		$queries_before = get_num_queries();
		$query2         = new WP_User_Query( $args );
		$users2         = $query2->get_results();
		$users_total2   = $query2->get_total();
		$queries_after  = get_num_queries();

		$this->assertSame( $queries_before, $queries_after );
		$this->assertSame( $users_total1, $users_total2 );
		$this->assertSameSets( $users1, $users2 );
	}

	/**
	 * Data provider
	 *
	 * @return array
	 */
	public function data_returning_field_subset_as_array() {
		$data = array(
			'id'                    => array(
				'args' => array( 'fields' => array( 'id' ) ),

			),
			'ID'                    => array(
				'args' => array( 'fields' => array( 'ID' ) ),

			),
			'user_login'            => array(
				'args' => array( 'fields' => array( 'user_login' ) ),
			),
			'user_nicename'         => array(
				'args' => array( 'fields' => array( 'user_nicename' ) ),
			),
			'user_email'            => array(
				'args' => array( 'fields' => array( 'user_email' ) ),
			),
			'user_url'              => array(
				'args' => array( 'fields' => array( 'user_url' ) ),
			),
			'user_status'           => array(
				'args' => array( 'fields' => array( 'user_status' ) ),
			),
			'display_name'          => array(
				'args' => array( 'fields' => array( 'display_name' ) ),
			),
			'invalid_field'         => array(
				'args' => array( 'fields' => array( 'invalid_field' ) ),
			),
			'valid array inc id'    => array(
				'args' => array( 'fields' => array( 'display_name', 'user_email', 'id' ) ),
			),
			'valid array inc ID'    => array(
				'args' => array( 'fields' => array( 'display_name', 'user_email', 'ID' ) ),
			),
			'partly valid array'    => array(
				'args' => array( 'fields' => array( 'display_name', 'invalid_field' ) ),
			),
			'meta query'            => array(
				'args' => array(
					'fields'     => array( 'ID' ),
					'meta_query' => array(
						'foo_key' => array(
							'key'     => 'foo',
							'compare' => 'EXISTS',
						),
					),
					'orderby'    => 'foo_key',
					'order'      => 'DESC',
				),
			),
			'published posts'       => array(
				'args' => array(
					'has_published_posts' => true,
					'fields'              => array( 'ID' ),
				),
			),
			'published posts order' => array(
				'args' => array(
					'orderby' => 'post_count',
					'fields'  => array( 'ID' ),
				),
			),
			'published count_total' => array(
				'args' => array(

					'count_total' => false,
					'fields'      => array( 'ID' ),
				),
			),
			'capability'            => array(
				'args' => array(
					'capability' => 'install_plugins',
					'fields'     => array( 'ID' ),
				),
			),
			'include'               => array(
				'args' => array(
					'includes' => self::$author_ids,
					'fields'   => array( 'ID' ),
				),
			),
			'exclude'               => array(
				'args' => array(
					'exclude' => self::$author_ids,
					'fields'  => array( 'ID' ),
				),
			),

		);

		if ( is_multisite() ) {
			$data['spam']    = array(
				'args' => array( 'fields' => array( 'spam' ) ),
			);
			$data['deleted'] = array(
				'args' => array( 'fields' => array( 'deleted' ) ),
			);
		}

		return $data;
	}

	public function test_query_cache_update_user_role() {
		$user_id = self::factory()->user->create( array( 'role' => 'author' ) );

		$q1 = new WP_User_Query(
			array(
				'role' => 'author',
			)
		);

		$found = wp_list_pluck( $q1->get_results(), 'ID' );

		$this->assertContains( $user_id, $found, 'Find author in returned values' );

		$user = get_user_by( 'id', $user_id );
		$user->remove_role( 'author' );

		$q2 = new WP_User_Query(
			array(
				'role' => 'author',
			)
		);

		$found = wp_list_pluck( $q2->get_results(), 'ID' );
		$this->assertNotContains( $user_id, $found, 'Not to find author in returned values' );
	}

	public function test_query_cache_delete_user() {
		$user_id = self::factory()->user->create();

		$q1 = new WP_User_Query(
			array(
				'include' => array( $user_id ),
			)
		);

		$found    = wp_list_pluck( $q1->get_results(), 'ID' );
		$expected = array( $user_id );

		$this->assertSameSets( $expected, $found, 'Find author in returned values' );

		wp_delete_user( $user_id );

		$q2 = new WP_User_Query(
			array(
				'include' => array( $user_id ),
			)
		);

		$found = wp_list_pluck( $q2->get_results(), 'ID' );
		$this->assertNotContains( $user_id, $found, 'Not to find author in returned values' );
	}

	public function test_query_cache_update_user() {
		$user_id = self::factory()->user->create();

		wp_update_user(
			array(
				'ID'            => $user_id,
				'user_nicename' => 'paul',
			)
		);

		$args = array(
			'nicename__in' => array( 'paul' ),
		);

		$q1 = new WP_User_Query( $args );

		$found    = wp_list_pluck( $q1->get_results(), 'ID' );
		$expected = array( $user_id );

		$this->assertSameSets( $expected, $found, 'Find author in returned values' );

		wp_update_user(
			array(
				'ID'            => $user_id,
				'user_nicename' => 'linda',
			)
		);

		$q2 = new WP_User_Query( $args );

		$found = wp_list_pluck( $q2->get_results(), 'ID' );
		$this->assertNotContains( $user_id, $found, 'Not to find author in returned values' );
	}

	public function test_query_cache_create_user() {
		$user_id = self::factory()->user->create();

		$args = array( 'blog_id' => get_current_blog_id() );

		$q1 = new WP_User_Query( $args );

		$found = wp_list_pluck( $q1->get_results(), 'ID' );

		$this->assertContains( $user_id, $found, 'Find author in returned values' );

		$user_id_2 = self::factory()->user->create();

		$q2 = new WP_User_Query( $args );

		$found = wp_list_pluck( $q2->get_results(), 'ID' );
		$this->assertContains( $user_id_2, $found, 'Find author in returned values' );
	}

	public function test_has_published_posts_delete_post() {
		register_post_type( 'wptests_pt_public', array( 'public' => true ) );

		$post_id = self::factory()->post->create(
			array(
				'post_author' => self::$author_ids[2],
				'post_status' => 'publish',
				'post_type'   => 'wptests_pt_public',
			)
		);

		$q1 = new WP_User_Query(
			array(
				'has_published_posts' => true,
			)
		);

		$found    = wp_list_pluck( $q1->get_results(), 'ID' );
		$expected = array( self::$author_ids[2] );

		$this->assertSameSets( $expected, $found, 'Find author in returned values' );

		wp_delete_post( $post_id, true );

		$q2 = new WP_User_Query(
			array(
				'has_published_posts' => true,
			)
		);

		$found = wp_list_pluck( $q2->get_results(), 'ID' );
		$this->assertSameSets( array(), $found, 'Not to find author in returned values' );
	}

	public function test_has_published_posts_delete_post_order() {
		register_post_type( 'wptests_pt_public', array( 'public' => true ) );

		$user_id = self::factory()->user->create();

		$post_id = self::factory()->post->create(
			array(
				'post_author' => $user_id,
				'post_status' => 'publish',
				'post_type'   => 'wptests_pt_public',
			)
		);

		$q1 = new WP_User_Query(
			array(
				'orderby' => 'post_count',
			)
		);

		$found1 = wp_list_pluck( $q1->get_results(), 'ID' );
		$this->assertContains( $user_id, $found1, 'Find author in returned values' );

		wp_delete_post( $post_id, true );

		$q2 = new WP_User_Query(
			array(
				'orderby' => 'post_count',
			)
		);

		$found2 = wp_list_pluck( $q2->get_results(), 'ID' );
		$this->assertContains( $user_id, $found1, 'Find author in returned values' );
		$this->assertSameSets( $found1, $found2, 'Not same order' );
	}

	public function test_meta_query_cache_invalidation() {
		add_user_meta( self::$author_ids[0], 'foo', 'bar' );
		add_user_meta( self::$author_ids[1], 'foo', 'bar' );

		$q1 = new WP_User_Query(
			array(
				'meta_query' => array(
					array(
						'key'   => 'foo',
						'value' => 'bar',
					),
				),
			)
		);

		$found    = wp_list_pluck( $q1->get_results(), 'ID' );
		$expected = array( self::$author_ids[0], self::$author_ids[1] );

		$this->assertSameSets( $expected, $found );

		delete_user_meta( self::$author_ids[1], 'foo' );

		$q2 = new WP_User_Query(
			array(
				'meta_query' => array(
					array(
						'key'   => 'foo',
						'value' => 'bar',
					),
				),
			)
		);

		$found    = wp_list_pluck( $q2->get_results(), 'ID' );
		$expected = array( self::$author_ids[0] );

		$this->assertSameSets( $expected, $found );
	}

	/**
	 * @group ms-required
	 */
	public function test_get_single_capability_multisite_blog_id() {
		$blog_id = self::factory()->blog->create();

		add_user_to_blog( $blog_id, self::$author_ids[0], 'subscriber' );
		add_user_to_blog( $blog_id, self::$author_ids[1], 'author' );
		add_user_to_blog( $blog_id, self::$author_ids[2], 'editor' );

		$q1 = new WP_User_Query(
			array(
				'capability' => 'publish_posts',
				'blog_id'    => $blog_id,
			)
		);

		$found = wp_list_pluck( $q1->get_results(), 'ID' );

		$this->assertNotContains( self::$author_ids[0], $found );
		$this->assertContains( self::$author_ids[1], $found );
		$this->assertContains( self::$author_ids[2], $found );

		remove_user_from_blog( self::$author_ids[2], $blog_id );

		$q2 = new WP_User_Query(
			array(
				'capability' => 'publish_posts',
				'blog_id'    => $blog_id,
			)
		);

		$found = wp_list_pluck( $q2->get_results(), 'ID' );
		$this->assertNotContains( self::$author_ids[0], $found );
		$this->assertContains( self::$author_ids[1], $found );
		$this->assertNotContains( self::$author_ids[2], $found );
	}

	/**
	 * @ticket 32250
	 * @group ms-required
	 */
	public function test_has_published_posts_should_respect_blog_id() {
		$blogs = self::factory()->blog->create_many( 2 );

		add_user_to_blog( $blogs[0], self::$author_ids[0], 'author' );
		add_user_to_blog( $blogs[0], self::$author_ids[1], 'author' );
		add_user_to_blog( $blogs[1], self::$author_ids[0], 'author' );
		add_user_to_blog( $blogs[1], self::$author_ids[1], 'author' );

		switch_to_blog( $blogs[0] );
		self::factory()->post->create(
			array(
				'post_author' => self::$author_ids[0],
				'post_status' => 'publish',
				'post_type'   => 'post',
			)
		);
		restore_current_blog();

		switch_to_blog( $blogs[1] );
		$post_id = self::factory()->post->create(
			array(
				'post_author' => self::$author_ids[1],
				'post_status' => 'publish',
				'post_type'   => 'post',
			)
		);
		restore_current_blog();

		$q = new WP_User_Query(
			array(
				'has_published_posts' => array( 'post' ),
				'blog_id'             => $blogs[1],
			)
		);

		$found    = wp_list_pluck( $q->get_results(), 'ID' );
		$expected = array( self::$author_ids[1] );

		$this->assertSameSets( $expected, $found );
		switch_to_blog( $blogs[1] );
		wp_delete_post( $post_id, true );
		restore_current_blog();

		$q = new WP_User_Query(
			array(
				'has_published_posts' => array( 'post' ),
				'blog_id'             => $blogs[1],
			)
		);

		$found = wp_list_pluck( $q->get_results(), 'ID' );

		$this->assertSameSets( array(), $found );
	}
}
