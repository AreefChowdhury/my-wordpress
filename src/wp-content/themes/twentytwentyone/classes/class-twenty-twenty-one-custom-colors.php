<?php
/**
 * Custom Colors Class
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

/**
 * This class is in charge of color customization via the Customizer.
 */
class Twenty_Twenty_One_Custom_Colors {

	/**
	 * Instantiate the object.
	 *
	 * @since Twenty Twenty-One 1.0
	 */
	public function __construct() {

		// Enqueue color variables for customizer & frontend.
		add_action( 'wp_enqueue_scripts', array( $this, 'custom_color_variables' ) );

		// Enqueue color variables for editor.
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_custom_color_variables' ) );

		// Add body-class if needed.
		add_filter( 'body_class', array( $this, 'body_class' ) );
	}

	/**
	 * Determine the luminance of the given color and then return #fff or #000 so that the text is always readable.
	 *
	 * @since Twenty Twenty-One 1.0
	 *
	 * @param string $background_color The background color.
	 * @return string (hex color)
	 */
	public function custom_get_readable_color( $background_color ) {
		return ( self::get_relative_luminance_from_hex( $background_color ) > 127 ) ? '#000' : '#fff';
	}

	/**
	 * Generate color variables.
	 *
	 * Adjust the color value of the CSS variables depending on the background color theme mod.
	 * Both text and link colors needs to be updated.
	 * The code below needs to be updated, because the colors are no longer theme mods.
	 *
	 * @since Twenty Twenty-One 1.0
	 *
	 * @param string|null $context Can be "editor" or null.
	 * @return string
	 */
	public function generate_custom_color_variables( $context = null ) {

		$theme_css        = $context === 'editor' ? ':root .editor-styles-wrapper{' : ':root{';
		$background_color = get_theme_mod( 'background_color', 'D1E4DD' );

		if ( strtolower( $background_color ) !== 'd1e4dd' ) {
			$theme_css .= '--global--color-background: #' . $background_color . ';';
			$theme_css .= '--global--color-primary: ' . $this->custom_get_readable_color( $background_color ) . ';';
			$theme_css .= '--global--color-secondary: ' . $this->custom_get_readable_color( $background_color ) . ';';
			$theme_css .= '--button--color-background: ' . $this->custom_get_readable_color( $background_color ) . ';';
			$theme_css .= '--button--color-text-hover: ' . $this->custom_get_readable_color( $background_color ) . ';';

			if ( $this->custom_get_readable_color( $background_color ) === '#fff' ) {
				$theme_css .= '--table--stripes-border-color: rgba(240, 240, 240, 0.15);';
				$theme_css .= '--table--stripes-background-color: rgba(240, 240, 240, 0.15);';
			}
		}

		$theme_css .= '}';

		return $theme_css;
	}

	/**
	 * Customizer & frontend custom color variables.
	 *
	 * @since Twenty Twenty-One 1.0
	 *
	 * @return void
	 */
	public function custom_color_variables() {
		if ( strtolower( get_theme_mod( 'background_color', 'D1E4DD' ) ) !== 'd1e4dd' ) {
			wp_add_inline_style( 'twenty-twenty-one-style', $this->generate_custom_color_variables() );
		}
	}

	/**
	 * Editor custom color variables.
	 *
	 * @since Twenty Twenty-One 1.0
	 *
	 * @return void
	 */
	public function editor_custom_color_variables() {
		wp_enqueue_style(
			'twenty-twenty-one-custom-color-overrides',
			get_theme_file_uri( 'assets/css/custom-color-overrides.css' ),
			array(),
			wp_get_theme()->get( 'Version' )
		);

		$background_color = get_theme_mod( 'background_color', 'D1E4DD' );
		if ( strtolower( $background_color ) !== 'd1e4dd' ) {
			wp_add_inline_style( 'twenty-twenty-one-custom-color-overrides', $this->generate_custom_color_variables( 'editor' ) );
		}
	}

	/**
	 * Get luminance from a HEX color.
	 *
	 * @static
	 *
	 * @since Twenty Twenty-One 1.0
	 *
	 * @param string $hex The HEX color.
	 * @return int Returns a number (0-255).
	 */
	public static function get_relative_luminance_from_hex( $hex ) {

		// Remove the "#" symbol from the beginning of the color.
		$hex = ltrim( $hex, '#' );

		// Make sure there are 6 digits for the below calculations.
		if ( strlen( $hex ) === 3 ) {
			$hex = substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 2, 1 ) . substr( $hex, 2, 1 );
		}

		// Get red, green, blue.
		$red   = hexdec( substr( $hex, 0, 2 ) );
		$green = hexdec( substr( $hex, 2, 2 ) );
		$blue  = hexdec( substr( $hex, 4, 2 ) );

		// Calculate the luminance.
		$lum = ( 0.2126 * $red ) + ( 0.7152 * $green ) + ( 0.0722 * $blue );
		return (int) round( $lum );
	}

	/**
	 * Adds a class to <body> if the background-color is dark.
	 *
	 * @since Twenty Twenty-One 1.0
	 *
	 * @param array $classes The existing body classes.
	 * @return array
	 */
	public function body_class( $classes ) {
		$background_color = get_theme_mod( 'background_color', 'D1E4DD' );
		$luminance        = self::get_relative_luminance_from_hex( $background_color );

		if ( $luminance < 127 ) {
			$classes[] = 'is-dark-theme';
		} else {
			$classes[] = 'is-light-theme';
		}

		if ( $luminance >= 225 ) {
			$classes[] = 'has-background-white';
		}

		return $classes;
	}
}
