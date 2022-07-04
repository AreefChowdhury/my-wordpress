<?php
/**
 * Locale API: WP_Textdomain_Registry class
 *
 * @package WordPress
 * @subpackage i18n
 * @since 6.1.0
 */

/**
 * Core class used for registering text domains.
 *
 * @since 6.1.0
 */
class WP_Textdomain_Registry {
	/**
	 * List of domains and their language directory paths.
	 *
	 * @since 6.1.0
	 *
	 * @var array
	 */
	protected $domains = array();

	/**
	 * Holds a cached list of available .mo files to improve performance.
	 *
	 * @since 6.1.0
	 *
	 * @var array
	 */
	protected $cached_mo_files;

	/**
	 * Returns the MO file path for a specific domain.
	 *
	 * @since 6.1.0
	 *
	 * @param string $domain Text domain.
	 * @param string $locale Locale.
	 *
	 * @return string|false MO file path or false if there is none available.
	 */
	public function get( $domain, $locale ) {
		if ( isset( $this->domains[ $domain ][ $locale ] ) ) {
			return $this->domains[ $domain ][ $locale ];
		}

		return $this->get_path_from_lang_dir( $domain, $locale );
	}

	/**
	 * Sets the MO file path for a specific domain.
	 *
	 * @since 6.1.0
	 *
	 * @param string       $domain Text domain.
	 * @param string       $locale Locale.
	 * @param string|false $path Language directory path or false if there is none available.
	 */
	public function set( $domain, $locale, $path ) {
		$this->domains[ $domain ][ $locale ] = $path ? trailingslashit( $path ) : false;
	}

	/**
	 * Resets the registry state.
	 *
	 * @since 6.1.0
	 */
	public function reset() {
		$this->cached_mo_files = null;
		$this->domains         = array();
	}

	/**
	 * Gets the path to a translation file in the languages directory for the current locale.
	 *
	 * @since 6.1.0
	 *
	 * @param string $domain Text domain.
	 * @param string $locale Locale.
	 * @return string|false MO file path or false if there is none available.
	 */
	private function get_path_from_lang_dir( $domain, $locale ) {
		if ( null === $this->cached_mo_files ) {
			$this->cached_mo_files = array();

			$this->set_cached_mo_files();
		}

		$mofile = "{$domain}-{$locale}.mo";

		$path = WP_LANG_DIR . '/plugins/' . $mofile;

		if ( in_array( $path, $this->cached_mo_files, true ) ) {
			$path = WP_LANG_DIR . '/plugins/';
			$this->set( $domain, $locale, $path );

			return $path;
		}

		$path = WP_LANG_DIR . '/themes/' . $mofile;
		if ( in_array( $path, $this->cached_mo_files, true ) ) {
			$path = WP_LANG_DIR . '/themes/';
			$this->set( $domain, $locale, $path );

			return $path;
		}

		// If no path is found for the given locale, check if an entry for the default
		// en_US locale exists. This is the case when e.g. using load_plugin_textdomain
		// with a custom path.
		if ( 'en_US' !== $locale && isset( $this->domains[ $domain ]['en_US'] ) ) {
			$this->set( $domain, $locale, $this->domains[ $domain ]['en_US'] );
			return $this->domains[ $domain ]['en_US'];
		}

		$this->set( $domain, $locale, false );

		return false;
	}

	/**
	 * Reads and caches all available MO files from the plugins and themes language directories.
	 *
	 * @since 6.1.0
	 */
	protected function set_cached_mo_files() {
		$locations = array(
			WP_LANG_DIR . '/plugins',
			WP_LANG_DIR . '/themes',
		);

		foreach ( $locations as $location ) {
			$mo_files = glob( $location . '/*.mo' );

			if ( $mo_files ) {
				$this->cached_mo_files = array_merge( $this->cached_mo_files, $mo_files );
			}
		}
	}
}
