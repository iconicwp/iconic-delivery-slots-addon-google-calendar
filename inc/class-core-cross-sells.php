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
	 * Single instance of the Iconic_WDS_Gcal_Core_License_Uplink object.
	 *
	 * @var Iconic_WDS_Gcal_Core_License_Uplink
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
	 * Creates/returns the single instance Iconic_WDS_Gcal_Core_License_Uplink object.
	 *
	 * @param array $args Arguments.
	 *
	 * @return Iconic_WDS_Gcal_Core_License_Uplink
	 */
	public static function run( $args = array() ) {
		if ( null === self::$single_instance ) {
			self::$args            = $args;
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Get plugin slug.
	 *
	 * @return string
	 */
	public static function get_plugin_slug() {
		return str_replace( '-', '_', 'iconic-wds-gcal' );
	}

	/**
	 * Get remote data.
	 *
	 * @param string      $transient_name  Transient name.
	 * @param string      $path            Path.
	 * @param string|bool $custom_base_url Custom base URL.
	 *
	 * @return array
	 */
	private static function get_remote_data( $transient_name, $path, $custom_base_url = false ) {
		$data = get_transient( $transient_name );

		if ( ! empty( $data ) ) {
			return $data;
		}

		$base_url = defined( 'ICONIC_BASE_URL' ) ? ICONIC_BASE_URL : 'https://iconicwp.com';
		$base_url = ( $custom_base_url ) ? $custom_base_url : $base_url;
		$request  = wp_remote_get( $base_url . $path );

		if ( is_wp_error( $request ) ) {
			return array(); // Bail early.
		}

		$body = wp_remote_retrieve_body( $request );
		$data = json_decode( $body, true );

		if ( empty( $data ) ) {
			return array();
		}

		set_transient( $transient_name, $data, HOUR_IN_SECONDS * 48 );

		return $data;
	}

	/**
	 * Get plugins.
	 *
	 * @return array
	 */
	private static function get_plugins() {
		$plugins = self::get_remote_data( 'iconic_get_plugins', '/wp-json/wp/v2/cpt_product?per_page=100' );
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
    	/**
		 * Filter whether cross sells plugins should be skipped.
		 *
		 * @hook {plugin-slug}_skip_core_cross_sells_selected_plugins
		 * @param  bool $skip_selected_plugins Whether skip cross sells plugins. Default: false.
		 */
    	$skip_selected_plugins = apply_filters( self::get_plugin_slug() . '_skip_core_cross_sells_selected_plugins', false );

		if ( $skip_selected_plugins ) {
			return false;
    	}

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

		return apply_filters( self::get_plugin_slug() . '_core_cross_sells_selected_plugins', array_slice( $selected_plugins, 0, $limit ) );
	}

	/**
	 * Get the API sidebar content.
	 *
	 * @return array
	 */
	public static function get_api_sidebars() {
		$api_host = 'https://api.iconicwp.com';
		$site_url = home_url();
		/**
		 * Enable/Disable using the local API endpoint, rather than the remote.
		 */
		$local_dev = apply_filters( 'iconic_api_local_endpoint', false );
		$base_url  = ( $local_dev ) ? $site_url : $api_host;
		$sidebars  = self::get_remote_data( 'iconic_api_sidebars_iconic-wds-gcal', '/wp-json/wp/v2/iconic-api-sidebars?plugin_id=iconic-wds-gcal', $base_url );

		return $sidebars;
	}
}
