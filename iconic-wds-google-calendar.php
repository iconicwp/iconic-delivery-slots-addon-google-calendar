<?php
/**
 * Plugin Name: Google Calendar addon for WooCommerce Delivery Slots by Iconic
 * Plugin URI: https://iconicwp.com/products/woocommerce-delivery-slots/
 * Description: Adds Google Calendar integration to the WooCommerce Delivery Slots plugin.
 * Version: 0.1.0
 * Author: Iconic
 * Author URI: https://iconicwp.com
 * Author Email: support@iconicwp.com
 * Text Domain: iconic-wds-gcal
 * WC requires at least: 2.6.14
 * WC tested up to: 5.8.0
 *
 * @package Iconic_WDS_Gcal
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main class.
 *
 * @class Iconic_WDS_Gcal
 */
class Iconic_WDS_Gcal {
	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public static $version = '0.1.0';

	/**
	 * Plugin path.
	 *
	 * @var string
	 */
	public $plugin_path;

	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	public $plugin_url;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->define_constants();

		if ( ! Iconic_WDS_Core_Helpers::is_plugin_active( 'woocommerce/woocommerce.php' ) && ! Iconic_WDS_Core_Helpers::is_plugin_active( 'woocommerce-old/woocommerce.php' ) ) {
			return;
		}

		$this->setup_autoloader();

		$this->load_classes();

		add_action( 'init', array( $this, 'initiate' ) );
	}

	/**
	 * Define Constants.
	 */
	private function define_constants() {
		$this->define( 'ICONIC_WDS_GCAL_PATH', plugin_dir_path( __FILE__ ) );
		$this->define( 'ICONIC_WDS_GCAL_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'ICONIC_WDS_GCAL_INC_PATH', ICONIC_WDS_GCAL_PATH . 'inc/' );
		$this->define( 'ICONIC_WDS_GCAL_BASENAME', plugin_basename( __FILE__ ) );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Definition name.
	 * @param string|bool $value Definition value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Setup autoloader.
	 */
	private function setup_autoloader() {
		require_once ICONIC_WDS_GCAL_INC_PATH . 'class-core-autoloader.php';

		Iconic_WDS_Gcal_Core_Autoloader::run(
			array(
				'prefix'   => 'Iconic_WDS_Gcal_',
				'inc_path' => ICONIC_WDS_GCAL_INC_PATH,
			)
		);
	}

	/**
	 * Load classes.
	 */
	private function load_classes() {
		require_once ICONIC_WDS_GCAL_PATH . 'vendor/autoload.php';
		require_once ICONIC_WDS_GCAL_INC_PATH . 'admin/settings.php';

		Iconic_WDS_Gcal_Google_Calendar::run();
	}

	/**
	 * Runs when the plugin is initialized
	 */
	public function initiate() {
		// Setup localization.
		load_plugin_textdomain( 'iconic-wds-gcal', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		if ( is_admin() ) {
			// add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		} else {
			// add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		}

	}

}

global $iconic_wds_gcal;

$iconic_wds_gcal = new Iconic_WDS_Gcal();
