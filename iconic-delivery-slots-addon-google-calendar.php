<?php
/**
 * Plugin Name: Iconic Delivery Slots: Addon for Google Calendar
 * Plugin URI: https://iconicwp.com/products/woocommerce-delivery-slots/
 * Description: Adds Google Calendar integration to the WooCommerce Delivery Slots plugin.
 * Version: 1.0.1
 * Author: Iconic
 * Author URI: https://iconicwp.com
 * Author Email: support@iconicwp.com
 * Text Domain: iconic-wds-gcal
 * WC requires at least: 2.6.14
 * WC tested up to: 7.7.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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
	public static $version = '1.0.1';

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

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Plugins loaded.
	 */
	public function plugins_loaded() {
		if ( ! class_exists( 'Iconic_WDS' ) ) {
			add_action( 'admin_notices', array( $this, 'show_wds_core_missing_notice' ) );
			return;
		}

		$this->setup_autoloader();
		$this->load_classes();

		if ( ! Iconic_WDS_Core_Helpers::is_plugin_active( 'woocommerce/woocommerce.php' ) && ! Iconic_WDS_Core_Helpers::is_plugin_active( 'woocommerce-old/woocommerce.php' ) ) {
			return;
		}

		add_action( 'init', array( $this, 'load_textdomain' ) );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'plugin_action_links', array( $this, 'add_plugin_actions' ), 10, 4 );
		}

		Iconic_WDS_Gcal_Google_Calendar::run();
		Iconic_WDS_Gcal_Settings::run();
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
		require_once ICONIC_WDS_GCAL_PATH . 'inc/vendor/autoload.php';
		require_once ICONIC_WDS_GCAL_INC_PATH . 'admin/settings.php';
	}

	/**
	 * Load textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'iconic-wds-gcal', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Admin scripts.
	 */
	public function admin_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'iconic-wds-gcal-admin', ICONIC_WDS_GCAL_URL . 'assets/admin/js/main' . $suffix . '.js', array( 'jquery' ), self::$version, true );
		wp_enqueue_style( 'iconic-wds-gcal-admin', ICONIC_WDS_GCAL_URL . 'assets/admin/css/main' . $suffix . '.css', array(), self::$version );
	}

	/**
	 * Add settings URL to plugin action links.
	 *
	 * @param string[] $actions     Actions.
	 * @param string   $plugin_file Plugin file.
	 * @param array    $plugin_data Plugin data.
	 * @param string   $context     Context.
	 *
	 * @return string[]
	 */
	public static function add_plugin_actions( $actions, $plugin_file, $plugin_data, $context ) {
		if ( false === strpos( $plugin_file, 'iconic-wds-google-calendar.php' ) ) {
			return $actions;
		}

		$settings_url = admin_url( 'admin.php?page=jckwds-settings#tab-integrations' );
		$actions[]    = sprintf( "<a href='%s'>%s</a>", esc_url( $settings_url ), esc_html__( 'Settings', 'iconic-wds-gcal' ) );
		return $actions;
	}

	/**
	 * Show notice for missing WDS core plugin.
	 *
	 * @return void
	 */
	public static function show_wds_core_missing_notice() {
		$screen = get_current_screen();

		if ( 'plugin' === $screen->id ) {
			return;
		}

		$plugin_url = 'https://iconicwp.com/products/woocommerce-delivery-slots/?utm_source=iconic&utm_medium=plugin&utm_campaign=iconic-wds-gcal';
		?>
		<div class="notice notice-error">
			<p>
			<?php
			// Translators: Plugin link.
			echo wp_kses_post( sprintf( __( 'Google Calendar Addon for WooCommerce Delivery Slots requires the <a href="%s" target=_blank>WooCommerce Delivery Slots</a> plugin to be installed and activated.', 'iconic-wds-gcal' ), esc_attr( $plugin_url ) ) );
			?>
			</p>
		</div>
		<?php
	}
}

global $iconic_wds_gcal;

$iconic_wds_gcal = new Iconic_WDS_Gcal();
