<?php
/**
 * Licence related functions.
 *
 * @package iconic-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_WDS_Gcal_Core_Licence' ) ) {
	return;
}

/**
 * Iconic_WDS_Gcal_Core_Licence.
 *
 * @class    Iconic_WDS_Gcal_Core_Licence
 * @version  1.0.0
 */
class Iconic_WDS_Gcal_Core_Licence {
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
	 * Freemius instance.
	 *
	 * @var Freemius
	 */
	public static $freemius = null;

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
	 * Construct.
	 */
	private function __construct() {
		self::configure_freemius();
		self::run_hooks();
	}

	/**
	 * Configure Freemius.
	 */
	public static function configure_freemius() {
		if ( ! is_null( self::$freemius ) ) {
			return;
		}

		require_once self::$args['paths']['inc'] . 'vendor/freemius/start.php';

		$menu = array(
			'slug'       => self::get_fs_arg( 'menu/slug', null ),
			'contact'    => self::get_fs_arg( 'menu/contact', false ),
			'support'    => self::get_fs_arg( 'menu/support', false ),
			'account'    => self::get_fs_arg( 'menu/account', false ),
			'pricing'    => self::get_fs_arg( 'menu/pricing', true ),
			'first-path' => self::get_fs_arg( 'menu/first-path', false ),
		);

		$parent = self::get_fs_arg( 'menu/parent', true );

		if ( $parent ) {
			$menu['parent'] = array(
				'slug' => self::get_fs_arg( 'menu/parent/slug', 'woocommerce' ),
			);
		}

		// Requires id, slug, public key, menu slug.
		self::$freemius = fs_dynamic_init(
			array(
				'id'                  => self::get_fs_arg( 'id', null ),
				'slug'                => self::get_fs_arg( 'slug', null ),
				'type'                => self::get_fs_arg( 'type', 'plugin' ),
				'public_key'          => self::get_fs_arg( 'public_key', null ),
				'is_premium'          => self::get_fs_arg( 'is_premium', true ),
				'is_premium_only'     => self::get_fs_arg( 'is_premium_only', true ),
				'has_premium_version' => self::get_fs_arg( 'has_premium_version', true ),
				'has_paid_plans'      => self::get_fs_arg( 'has_paid_plans', true ),
				'has_addons'          => self::get_fs_arg( 'has_addons', false ),
				'is_org_compliant'    => self::get_fs_arg( 'is_org_compliant', false ),
				'menu'                => $menu,
			)
		);

		// Set basename.
		if ( isset( self::$args['paths']['file'] ) ) {
			self::$freemius->set_basename( true, self::$args['paths']['file'] );
		}
	}

	/**
	 * Get FS arg to avoid error when deploying.
	 *
	 * @param string $keys    Keys.
	 * @param array  $default Default arguments.
	 *
	 * @return mixed
	 */
	public static function get_fs_arg( $keys, $default ) {
		$base      = self::$args['freemius'];
		$keys      = explode( '/', $keys );
		$depth     = 0;
		$key_count = count( $keys );

		foreach ( $keys as $key ) {
			$depth ++;

			if ( ! isset( $base[ $key ] ) ) {
				break;
			}

			$base = $base[ $key ];

			if ( $depth === $key_count ) {
				return $base;
			}
		}

		return $default;
	}

	/**
	 * Run hooks.
	 */
	public static function run_hooks() {
		self::$freemius->add_filter( 'connect/before', array( __CLASS__, 'connect_before' ), 10, 1 );
		self::$freemius->add_filter( 'connect/after', array( __CLASS__, 'connect_after' ), 10, 1 );

		self::$freemius->add_filter( 'show_trial', '__return_false' );
		self::$freemius->add_filter( 'templates/account.php', array( __CLASS__, 'back_to_settings_link' ), 10, 1 );
		self::$freemius->add_filter( 'plugin_icon', array( __CLASS__, 'plugin_icon' ), 10, 1 );
		self::$freemius->add_filter( 'hide_account_tabs', '__return_true' );
		add_filter( 'plugin_action_links_' . self::$args['basename'], array( __CLASS__, 'add_action_links' ) );
		add_action( 'admin_notices', array( __CLASS__, 'output_back_to_settings_link' ), 200 );
	}

	/**
	 * Before license activation form.
	 */
	public static function connect_before() {
		?>
		<style>
			body #fs_connect {
				margin: 20px;
				border-radius: 8px;
				box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
				background: #fff;
			}

			body .fs-header {
				display: none;
			}

			body #fs_connect .fs-box-container {
				background-color: #fff;
				box-shadow: none;
				padding-top: 0;
				border-radius: 8px;
			}

			body #fs_connect.require-license-key .fs-content {
				padding-bottom: 25px;
			}

			body #fs_connect .fs-content {
				padding: 25px 30px;
				background: none;
			}

			body #fs_connect .fs-license-key-container {
				width: 100%;
				margin: 25px 0 0;
			}

			body #fs_connect .fs-license-key-container input {
				padding: 4px 38px 4px 12px;
				box-sizing: border-box;
				height: 40px;
				line-height: 30px;
				border-radius: 8px;
			}

			body #fs_connect .fs-license-key-container .dashicons {
				top: 11px;
				right: 10px;
			}

			body #fs_connect a.show-license-resend-modal {
				margin: 8px 0 2px;
				text-align: center;
				display: block;
				box-shadow: none !important;
			}

			body #fs_connect .fs-actions {
				padding: 0 30px 25px;
				background: #fff;
				border-bottom: 1px solid #F0F0F1;
			}

			body #fs_connect #license_issues_link {
				font-size: 13px;
				margin-top: 8px;
				box-shadow: none !important;
			}

			body #fs_connect .fs-permissions {
				padding: 15px 30px;
				transition: none;
			}

			body #fs_connect .fs-permissions .fs-license-sync-disclaimer {
				margin-bottom: 0;
			}

			body #fs_connect .fs-terms {
				background: no-repeat;
				padding: 15px 30px;
				border-top: 1px solid #F0F0F1;
			}

			body #fs_connect .fs-actions .button.button-primary {
				height: 40px;
				line-height: 38px;
				padding: 0 15px;
				border-radius: 8px;
			}

			body #fs_connect a:not(.button) {
				color: #2271b1;
				text-decoration: none;
			}
		</style>
		<div class="wpsf-settings">
		<?php
		if ( class_exists( 'Iconic_WDS_Gcal_Core_Settings' ) ) {
			Iconic_WDS_Gcal_Core_Settings::$settings_framework->settings_header();
		}
	}

	/**
	 * After license activation form.
	 */
	public static function connect_after() {
		?>
		</div><?php // .wpsf-settings ?>
		<?php
	}

	/**
	 * Set plugin icon.
	 *
	 * @param string $icon Icon.
	 *
	 * @return string
	 */
	public static function plugin_icon( $icon ) {
		return self::$args['paths']['plugin'] . '/assets/img/plugin-icon.png';
	}

	/**
	 * Back to settings link.
	 *
	 * @param string $html HTML.
	 *
	 * @todo Move to settings class.
	 */
	public static function back_to_settings_link( $html = '' ) {
		return $html . sprintf( '<a href="%s" class="button button-secondary">&larr; %s</a>', self::$args['urls']['settings'], esc_html__( 'Back to Settings', 'iconic-wds-gcal' ) );
	}

	/**
	 * Output back to settings link.
	 */
	public static function output_back_to_settings_link() {
		if ( ! Iconic_WDS_Gcal_Core_Settings::is_settings_page( '-account' ) ) {
			return;
		}

		?>
		<div style="margin: 20px 0 10px;">
			<?php echo wp_kses_post( self::back_to_settings_link() ); ?>
		</div>
		<?php
	}

	/**
	 * Add action links to "plugins" page.
	 *
	 * @param array $links Links.
	 *
	 * @return array
	 */
	public static function add_action_links( $links ) {
		$links[] = sprintf( '<a href="%s" target="_blank">%s</a>', self::$args['urls']['product'] . '/changelog/?utm_source=Iconic&utm_medium=Plugin&utm_campaign=iconic-wds-gcal&utm_content=changelog-link', esc_html__( 'Changelog', 'iconic-wds-gcal' ) );

		return $links;
	}

	/**
	 * Get admin account link button.
	 *
	 * @return string
	 */
	public static function admin_account_link() {
		return sprintf( '<a href="%s" class="button button-secondary">%s</a>', self::$args['urls']['account'], esc_html__( 'Manage Licence &amp; Billing', 'iconic-wds-gcal' ) );
	}

	/**
	 * Has valid licence?
	 *
	 * @return bool
	 */
	public static function has_valid_licence() {
		if ( self::$freemius->can_use_premium_code() ) {
			return true;
		}

		return false;
	}

	/**
	 * Is bundle?
	 *
	 * @return bool
	 */
	public static function is_bundle() {
		if ( ! method_exists( self::$freemius, '_get_license' ) || ! class_exists( 'FS_Plugin_License' ) ) {
			return false;
		}

		$license = self::$freemius->_get_license();

		return is_object( $license ) && FS_Plugin_License::is_valid_id( $license->parent_license_id );
	}

	/**
	 * Get license quota.
	 *
	 * @return int
	 */
	public static function get_license_quota() {
		if ( ! method_exists( self::$freemius, '_get_license' ) ) {
			return false;
		}

		$license = self::$freemius->_get_license();

		return is_object( $license ) ? (int) $license->quota : 1;
	}
}
