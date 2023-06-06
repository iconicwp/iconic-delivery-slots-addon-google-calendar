<?php
/**
 * Cross-sell functions.
 *
 * @package iconic-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_WDS_Gcal_Core_Cross_Sells' ) ) {
	return;
}

/**
 * Iconic_WDS_Gcal_Core_Cross_Sells.
 *
 * @class    Iconic_WDS_Gcal_Core_Cross_Sells
 * @version  1.0.0
 */
class Iconic_WDS_Gcal_Core_Cross_Sells {
	/**
	 * Single instance of the Iconic_WDS_Gcal_Core_Licence object.
	 *
	 * @var Iconic_WDS_Gcal_Core_Licence
	 */
	public static $single_instance = null;

	/**
	 * Class args.
	 *
	 * @var array
	 */
	public static $args = array();

	/**
	 * Array of selected plugins.
	 *
	 * @var array
	 */
	private static $selected_plugins = array();

	/**
	 * Creates/returns the single instance Iconic_WDS_Gcal_Core_Licence object.
	 *
	 * @param array $args Arguments.
	 *
	 * @return Iconic_WDS_Gcal_Core_Licence
	 */
	public static function run( $args = array() ) {
		if ( null === self::$single_instance ) {
			self::$args            = $args;
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Get plugins.
	 *
	 * @return array
	 */
	private static function get_plugins() {
		$transient_name = 'iconic_get_plugins';
		$plugins        = get_transient( $transient_name );

		if ( ! empty( $plugins ) ) {
			return $plugins;
		}

		$request = wp_remote_get( 'https://iconicwp.com/wp-json/wp/v2/cpt_product?per_page=100' );

		if ( is_wp_error( $request ) ) {
			return array(); // Bail early.
		}

		$body    = wp_remote_retrieve_body( $request );
		$plugins = json_decode( $body, true );

		if ( empty( $plugins ) ) {
			return array();
		}

		set_transient( $transient_name, $plugins, HOUR_IN_SECONDS * 48 );

		return $plugins;
	}

	/**
	 * Get plugin.
	 *
	 * @return bool|stdClass
	 */
	public static function get_plugin() {
		$class_name = '';
		$plugins    = self::get_plugins();

		if ( empty( $plugins ) ) {
			return false;
		}

		foreach ( $plugins as $plugin ) {
			if ( empty( $plugin['product'] ) || $class_name !== $plugin['product']['class_name'] ) {
				continue;
			}

			return $plugin;
		}

		return false;
	}

	/**
	 * Get selected plugins.
	 *
	 * @param int $limit Max number of plugins to fetch.
	 *
	 * @return bool|array
	 */
	public static function get_selected_plugins( $limit = 2 ) {
		$this_plugin = self::get_plugin();

		if ( empty( $this_plugin ) ) {
			return false;
		}

		$plugins          = self::get_plugins();
		$selected_plugins = array();

		foreach ( $plugins as $plugin ) {
			if ( empty( $plugin ) || ! in_array( $plugin['id'], (array) $this_plugin['product']['related'], true ) ) {
				continue;
			}

			if ( class_exists( $plugin['product']['class_name'] ) || function_exists( $plugin['product']['class_name'] ) ) {
				continue;
			}

			$selected_plugins[] = $plugin;
		}

		if ( empty( $selected_plugins ) ) {
			return false;
		}

		shuffle( $selected_plugins );

		return array_slice( $selected_plugins, 0, $limit );
	}
}
