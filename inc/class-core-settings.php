<?php
/**
 * Setting related functions.
 *
 * @package iconic-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_WDS_Gcal_Core_Settings' ) ) {
	return;
}

/**
 * Iconic_WDS_Gcal_Core_Settings.
 *
 * @class    Iconic_WDS_Gcal_Core_Settings
 * @version  1.0.6
 */
class Iconic_WDS_Gcal_Core_Settings {
	/**
	 * Single instance of the Iconic_WDS_Gcal_Core_Settings object.
	 *
	 * @var Iconic_WDS_Gcal_Core_Settings
	 */
	public static $single_instance = null;

	/**
	 * Class args.
	 *
	 * @var array
	 */
	public static $args = array();

	/**
	 * Settings framework instance.
	 *
	 * @var Iconic_WDS_Gcal_Settings_Framework
	 */
	public static $settings_framework = null;

	/**
	 * Settings.
	 *
	 * @var array
	 */
	public static $settings = array();

	/**
	 * Docs base url.
	 *
	 * @var string
	 */
	public static $docs_base = 'https://iconicwp.com/docs/';

	/**
	 * Iconic svg src.
	 *
	 * @var string
	 */
	public static $iconic_svg = 'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjMwcHgiIGhlaWdodD0iMzUuNDU1cHgiIHZpZXdCb3g9IjAgMCAzMCAzNS40NTUiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDMwIDM1LjQ1NSIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8Zz4NCgk8Zz4NCgkJPHBvbHlnb24gcG9pbnRzPSIxMC45MSwzMy44MTggMTMuNjM2LDM1LjQ1NSAxMy42MzYsMTkuMDkxIDEwLjkxLDE3LjQ1NSAJCSIvPg0KCQk8cG9seWdvbiBwb2ludHM9IjE2LjM2MywzNS40NTUgMzAsMjcuMTY4IDMwLDIzLjk3NiAxNi4zNjMsMzIuMjYzIAkJIi8+DQoJCTxnPg0KCQkJPHBvbHlnb24gcG9pbnRzPSIxMi4zNSwxLjU5IDI1Ljk4Niw5Ljc3MiAyOC42MzcsOC4xODIgMTUsMCAJCQkiLz4NCgkJCTxwb2x5Z29uIHBvaW50cz0iNS40NTUsMzAuNTQ1IDguMTgyLDMyLjE4MiA4LjE4MiwxNS44MTggNS40NTUsMTQuMTgyIAkJCSIvPg0KCQkJPHBvbHlnb24gcG9pbnRzPSIxNi4zNjMsMjguOTIxIDMwLDIwLjYzNCAzMCwxNy40NDIgMTYuMzYzLDI1LjcyOSAJCQkiLz4NCgkJCTxwb2x5Z29uIHBvaW50cz0iNi44NzEsNC45ODQgMjAuNTA4LDEzLjE2NyAyMy4xNTgsMTEuNTc2IDkuNTIxLDMuMzk1IAkJCSIvPg0KCQkJPHBvbHlnb24gcG9pbnRzPSIyLjcyNywxMi41NDUgMCwxMC45MDkgMCwyNy4yNzMgMi43MjcsMjguOTA5IAkJCSIvPg0KCQkJPHBvbHlnb24gcG9pbnRzPSIxNi4zNjMsMjIuMzg4IDMwLDE0LjEgMzAsMTAuOTA5IDE2LjM2MywxOS4xOTYgCQkJIi8+DQoJCQk8cG9seWdvbiBwb2ludHM9IjEuMzkyLDguMTY1IDE1LjAyOCwxNi4zNDcgMTcuNjc4LDE0Ljc1NiA0LjA0Miw2LjU3NSAJCQkiLz4NCgkJPC9nPg0KCTwvZz4NCjwvZz4NCjwvc3ZnPg0K';

	/**
	 * Creates/returns the single instance Iconic_WDS_Gcal_Core_Settings object.
	 *
	 * @param array $args Arguments.
	 *
	 * @return Iconic_WDS_Gcal_Core_Settings
	 */
	public static function run( $args = array() ) {
		if ( null === self::$single_instance ) {
			self::$args                            = $args;
			self::$args['option_group_underscore'] = str_replace( '-', '_', self::$args['option_group'] );
			self::$args['account_link']            = $args['account_link'] ?? 'https://my.iconicwp.com';
			self::$args['utm_source']              = $args['utm_source'] ?? 'Iconic';
			self::$single_instance                 = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Construct.
	 */
	private function __construct() {
		// If init hook has already run, call init() immediately.
		if ( did_action( 'init' ) ) {
			self::init();
		} else {
			add_action( 'init', array( __CLASS__, 'init' ) );
		}
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ), 20 );
		add_action( 'in_admin_header', array( __CLASS__, 'clean_notices' ), 9999 );
		add_action( 'wpsf_after_tab_links_' . self::$args['option_group'], array( __CLASS__, 'add_sidebar' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'wpsf_after_title_' . self::$args['option_group'], array( __CLASS__, 'add_version' ) );

		if ( defined( 'ICONIC_WDS_GCAL_FILE' ) ) {
			add_filter( 'plugin_action_links_' . plugin_basename( ICONIC_WDS_GCAL_FILE ), array( __CLASS__, 'plugin_action_links' ) );
		}
	}

	/**
	 * Init.
	 */
	public static function init() {
		require_once self::$args['vendor_path'] . 'wp-settings-framework/wp-settings-framework.php';

		add_filter( 'wpsf_register_settings_' . self::$args['option_group'], array( __CLASS__, 'setup_dashboard' ) );

		self::$settings_framework = new Iconic_WDS_Gcal_Settings_Framework( self::$args['settings_path'], self::$args['option_group'] );
		self::$settings           = self::$settings_framework->get_settings();
	}

	/**
	 * Get setting.
	 *
	 * @param string $setting Setting.
	 *
	 * @return mixed
	 */
	public static function get_setting( $setting ) {
		if ( empty( self::$settings ) ) {
			return null;
		}

		if ( ! isset( self::$settings[ $setting ] ) ) {
			return null;
		}

		return self::$settings[ $setting ];
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
	 * Get a setting directly from the database.
	 *
	 * @param string $section_id May also be prefixed with tab ID.
	 * @param string $field_id   The id of the specific field.
	 * @param mixed  $default    Default field value.
	 *
	 * @return mixed
	 */
	public static function get_setting_from_db( $section_id, $field_id, $default = false ) {
		$options = get_option( self::$args['option_group'] . '_settings' );

		// If no settings saved, return default.
		if ( false === $options ) {
			return $default;
		}

		if ( isset( $options[ $section_id . '_' . $field_id ] ) ) {
			return $options[ $section_id . '_' . $field_id ];
		}

		return false;
	}

	/**
	 * Add settings page.
	 */
	public static function add_settings_page() {
		if ( ! defined( 'ICONIC_DISABLE_BRAND' ) && ! defined( 'ICONIC_WDS_GCAL_DISABLE_BRAND' ) ) {
			$url = self::add_utm_query_args('https://iconicwp.com/', 'settings-title');

			$default_title = sprintf(
				'<div style="height: 28px; line-height: 28px;"><img width="24" height="28" style="display: inline-block; vertical-align: middle; margin: 0 8px 0 0" src="%s"> %s by <a href="%s" target="_blank">Iconic</a></div>',
				esc_attr( self::$iconic_svg ),
				self::$args['title'],
				esc_url($url)
			);
		} else {
			$default_title = sprintf( '<div style="height: 28px; line-height: 28px;">%s</div>', self::$args['title'] );
		}

		self::$settings_framework->add_settings_page(
			array(
				'parent_slug' => isset( self::$args['parent_slug'] ) ? self::$args['parent_slug'] : 'woocommerce',
				'page_title'  => isset( self::$args['page_title'] ) ? self::$args['page_title'] : $default_title,
				'menu_title'  => self::$args['menu_title'],
				'capability'  => self::get_settings_page_capability(),
			)
		);

		/**
		 * Do admin menu action for option group.
		 *
		 * @since 1.0.6
		 */
		do_action( 'admin_menu_' . self::$args['option_group'] );
	}

	/**
	 * Get settings page capability.
	 *
	 * @return mixed
	 */
	public static function get_settings_page_capability() {
		$capability = isset( self::$args['capability'] ) ? self::$args['capability'] : 'manage_woocommerce';

		/**
		 * Filter settings page capability.
		 *
		 * @param string $capability Capability.
		 *
		 * @return string
		 *
		 * @since 1.0.6
		 */
		return apply_filters( self::$args['option_group'] . '_settings_page_capability', $capability );
	}

	/**
	 * Is settings page?
	 *
	 * @param string $suffix Suffix.
	 *
	 * @return bool
	 */
	public static function is_settings_page( $suffix = '' ) {
		if ( ! is_admin() ) {
			return false;
		}

		$path = str_replace( '_', '-', self::$args['option_group'] ) . '-settings' . $suffix;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['page'] ) || $_GET['page'] !== $path ) {
			return false;
		}

		return true;
	}

	/**
	 * Clean notices for our settings page.
	 */
	public static function clean_notices() {
		if ( ! self::is_settings_page() && ! self::is_settings_page( '-account' ) ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		add_action( 'admin_notices', array( self::$settings_framework, 'admin_notices' ), 50 );
		add_action( 'admin_notices', array( __CLASS__, 'hide_notices' ), 1 );
	}

	/**
	 * Hide Iconic notices if set.
	 */
	public static function hide_notices() {
		$hide_notice = filter_input( INPUT_GET, 'iconic-wds-gcal-hide-notice', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( empty( $hide_notice ) ) {
			return;
		}

		$notice_nonce = filter_input( INPUT_GET, '_' . self::$args['option_group_underscore'] . '_notice_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! wp_verify_nonce( $notice_nonce, self::$args['option_group_underscore'] . '_hide_notices_nonce' ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce' ) );
		}

		update_user_meta( get_current_user_id(), 'dismissed_' . $hide_notice . '_notice', true );
	}

	/**
	 * Get doc links.
	 *
	 * @return array
	 */
	public static function get_doc_links() {
		$transient_name = self::$args['option_group'] . '_getting_started_links';
		$saved_return   = get_transient( $transient_name );

		if ( false !== $saved_return ) {
			return $saved_return;
		}

		$return   = array();
		$url      = self::get_docs_url( 'getting-started' );
		$response = wp_remote_get( $url );
		$html     = wp_remote_retrieve_body( $response );

		if ( ! $html ) {
			set_transient( $transient_name, $return, 12 * HOUR_IN_SECONDS );

			return $return;
		}

		$dom = new DOMDocument();

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@$dom->loadHTML( $html );

		$lists = $dom->getElementsByTagName( 'ul' );

		if ( empty( $lists ) ) {
			set_transient( $transient_name, $return, 12 * HOUR_IN_SECONDS );

			return $return;
		}

		foreach ( $lists as $list ) {
			$classes = $list->getAttribute( 'class' );

			if ( strpos( $classes, 'articleList' ) === false ) {
				continue;
			}

			$links = $list->getElementsByTagName( 'a' );

			foreach ( $links as $link ) {
				$return[] = array(
					'href'  => $link->getAttribute( 'href' ),
					'title' => $link->nodeValue, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				);
			}
		}

		set_transient( $transient_name, $return, 30 * DAY_IN_SECONDS );

		return $return;
	}

	/**
	 * Output getting started links.
	 */
	public static function output_getting_started_links() {
		$links = self::get_doc_links();

		if ( empty( $links ) ) {
			return;
		}
		?>
		<h3><?php esc_html_e( 'Getting Started', 'iconic-wds-gcal' ); ?></h3>

		<ol>
			<?php foreach ( $links as $link ) { ?>
				<?php
					$url = self::get_docs_url() . $link['href'];
					$url = self::add_utm_query_args($url, 'getting-started-links');
				?>
				<li>
					<a href="<?php echo esc_url( $url ); ?>" target="_blank"><?php echo esc_html( $link['title'] ); ?></a>
				</li>
			<?php } ?>
		</ol>
		<?php
	}

	/**
	 * Get docs URL.
	 *
	 * @param bool $type Type.
	 *
	 * @return mixed|string
	 */
	public static function get_docs_url( $type = false ) {
		if ( ! $type || 'base' === $type || ! isset( self::$args['docs'][ $type ] ) ) {
			return self::$docs_base;
		}

		return self::$docs_base . self::$args['docs'][ $type ];
	}

	/**
	 * Configure settings dashboard.
	 *
	 * @param array $settings Settings.
	 *
	 * @return mixed
	 */
	public static function setup_dashboard( $settings ) {
		if ( ! self::is_settings_page() ) {
			return $settings;
		}

		$settings['tabs']     = isset( $settings['tabs'] ) ? $settings['tabs'] : array();
		$settings['sections'] = isset( $settings['sections'] ) ? $settings['sections'] : array();

		$settings['tabs'][] = array(
			'id'    => 'dashboard',
			'title' => esc_html__( 'Dashboard', 'iconic-wds-gcal' ),
		);

		if ( current_user_can( 'manage_options' ) && ! defined( 'ICONIC_DISABLE_DASH' ) && ! defined( 'ICONIC_WDS_GCAL_DISABLE_DASH' ) ) {
			$settings['sections']['license'] = array(
				'tab_id'              => 'dashboard',
				'section_id'          => 'general',
				'section_title'       => esc_html__( 'License &amp; Account Settings', 'iconic-wds-gcal' ),
				'section_description' => '',
				'section_order'       => 10,
				'fields'              => array(
					array(
						'id'       => 'license_field',
						'title'    => __( 'License Key', 'iconic-wds-gcal' ),
						'subtitle' => __( 'Activate your license to get the latest updates and support.', 'iconic-wds-gcal' ),
						'type'     => 'custom',
						'output'   => '',
					),
					array(
						'id'       => 'account',
						'title'    => __( 'Your Account', 'iconic-wds-gcal' ),
						'subtitle' => __( 'Manage all of your Iconic plugins, supscriptions, renewals, and more.', 'iconic-wds-gcal' ),
						'type'     => 'custom',
						'output'   => self::account_link(),
					),
				),

			);

			$settings['sections']['support'] = array(
				'tab_id'              => 'dashboard',
				'section_id'          => 'support',
				'section_title'       => esc_html__( 'Support', 'iconic-wds-gcal' ),
				'section_description' => '',
				'section_order'       => 30,
				'fields'              => array(
					array(
						'id'       => 'support',
						'title'    => esc_html__( 'Support', 'iconic-wds-gcal' ),
						'subtitle' => esc_html__( 'Get premium support with a valid license.', 'iconic-wds-gcal' ),
						'type'     => 'custom',
						'output'   => self::support_link(),
					),
					array(
						'id'       => 'documentation',
						'title'    => esc_html__( 'Documentation', 'iconic-wds-gcal' ),
						'subtitle' => esc_html__( 'Read the plugin documentation.', 'iconic-wds-gcal' ),
						'type'     => 'custom',
						'output'   => self::documentation_link(),
					),
				),
			);
		}

		return $settings;
	}

	/**
	 * Add settings sidebar.
	 */
	public static function add_sidebar() {
		if ( ! current_user_can( 'manage_options' ) || defined( 'ICONIC_DISABLE_DASH' ) || defined( 'ICONIC_WDS_GCAL_DISABLE_DASH' ) ) {
			return;
		}

		$cross_sells  = Iconic_WDS_Gcal_Core_Cross_Sells::get_selected_plugins();
		$api_sidebars = Iconic_WDS_Gcal_Core_Cross_Sells::get_api_sidebars();

		if ( empty( $cross_sells ) && empty( $api_sidebars ) ) {
			return;
		}

		?>
		<style>
			/* Blocks colour palette classes*/
			.has-accent-color {
				color: #5559DA;
			}

			.has-accent-background-color {
				background-color: #5559DA;
			}

			.has-accent-border-color {
				border-color: #5559DA;
			}

			.has-accent-alt-color {
				color: #4249C6;
			}

			.has-accent-alt-background-color {
				background-color: #4249C6;
			}

			.has-accent-alt-border-color {
				border-color: #4249C6;
			}

			.has-dark-strongest-color {
				color: #24242D;
			}

			.has-dark-strongest-background-color {
				background-color: #24242D;
			}

			.has-dark-strongest-border-color {
				border-color: #24242D;
			}

			.has-dark-strong-color {
				color: #363644;
			}

			.has-dark-strong-background-color {
				background-color: #363644;
			}

			.has-dark-strong-border-color {
				border-color: #363644;
			}

			.has-dark-medium-color {
				color: #505057;
			}

			.has-dark-medium-background-color {
				background-color: #505057;
			}

			.has-dark-medium-border-color {
				border-color: #505057;
			}

			.has-dark-subtle-color {
				color: #7c7c81;
			}

			.has-dark-subtle-background-color {
				background-color: #7c7c81;
			}

			.has-dark-subtle-border-color {
				border-color: #7c7c81;
			}

			.has-light-subtle-color {
				color: #f5f5fd;
			}

			.has-light-subtle-background-color {
				background-color: #f5f5fd;
			}

			.has-light-subtle-border-color {
				border-color: #f5f5fd;
			}

			.has-light-lighter-color {
				color: #fafafe;
			}

			.has-light-lighter-background-color {
				background-color: #fafafe;
			}

			.has-light-lighter-border-color {
				border-color: #fafafe;
			}

			.has-light-lightest-color {
				color: #ffffff;
			}

			.has-light-lightest-background-color {
				background-color: #ffffff;
			}

			.has-light-lightest-border-color {
				border-color: #ffffff;
			}

			.wpsf-settings__content:after,
			.wpsf-settings__content form:after {
				display: table;
				clear: both;
			}

			.wpsf-settings__content form {
				width: calc( 100% - 300px );
				overflow: hidden;
			}

			.iconic-settings-sidebar {
				float: right;
				width: 100%;
				max-width: 280px;
				margin: 20px 0 0;
			}

			.iconic-settings-sidebar__widget {
				box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
				background: #fff;
				border-radius: 8px;
				padding: 15px;
				margin: 0 0 20px;
				box-sizing: border-box;
			}

			.iconic-settings-sidebar__widget:last-child {
				margin-bottom: 0 !important;
			}

			.iconic-settings-sidebar__widget :first-child {
				margin-top: 0 !important;
			}

			.iconic-settings-sidebar__widget :last-child {
				margin-bottom: 0 !important;
			}

			.iconic-settings-sidebar__widget--note {
				background: #FDF2C7;
				color: #473f24;
			}
			/* blocks */
			.iconic-settings-sidebar__widget--blocks .wp-block-embed iframe {
				height: auto;
			}
			.iconic-settings-sidebar__widget--blocks ul {
				list-style: initial;
			}

			.iconic-settings-sidebar__widget--blocks ul,
			.iconic-settings-sidebar__widget--blocks ol {
				padding-left: 15px;
			}

			/* buttons */

			.iconic-button.button {
				background: #5558da;
				border: none;
				color: #fff;
				padding: 6px 18px;
				border-radius: 4px;
				font-size: 16px;
			}

			.iconic-button.button:hover,
			.iconic-button.button:active,
			.iconic-button.button:focus {
				background: #5558da;
				border: none;
				color: #fff;
				text-decoration: underline;
			}

			.iconic-button--small.button {
				padding: 4px 12px;
				font-size: 14px;
			}

			/* cross sells */

			.iconic-settings-sidebar__widget--works-well {
				text-align: center;
			}

			.iconic-settings-sidebar__widget > * {
				margin-bottom: 15px !important;
				margin-left: 0 !important;
				margin-right: 0 !important;
				margin-top: 15px !important;
			}

			/* iconic product */

			.iconic-product {
				margin: 0 0 15px;
			}

			.iconic-product__image {
				background: #5ea4ee;
				padding: 15px 0;
				position: relative;
				border-radius: 4px 4px 0 0;
			}

			.iconic-product__image:after {
				width: 100%;
				height: 100%;
				content: '';
				position: absolute;
				top: 0;
				right: 0;
				bottom: 0;
				left: 0;
				z-index: 1;
				background: radial-gradient(circle, rgba(97, 209, 249, 0.4) 15%, rgba(97, 209, 249, 0) 80%);
			}

			.iconic-product img {
				max-width: 100%;
				width: 100%;
				height: auto;
				display: block;
				border-radius: 4px 4px 0 0;
				z-index: 10;
				position: relative;
			}

			.iconic-product__content {
				padding: 20px 15px;
				border: 1px solid #eaeaea;
				border-top: none;
				border-radius: 0 0 4px 4px;
			}

			.iconic-product__title {
				font-size: 16px;
			}

			.iconic-product__description {
				max-width: 220px;
				margin-left: auto;
				margin-right: auto;
			}

			.iconic-product__buttons {
				margin: 1.33em 0 0;
			}

			.iconic-product__buttons p {
				margin-bottom: 8px;
			}

			.iconic-product__buttons p:last-child {
				margin: 0;
			}

			/* media queries */

			@media only screen and (max-width: 1580px) {
				.wpsf-settings__content form {
					width: calc( 100% - 310px );
				}

				.iconic-settings-sidebar {
					max-width: 290px;
				}
			}

			@media only screen and (max-width: 1024px) {
				.wpsf-settings__content form {
					width: 100%;
				}

				.iconic-settings-sidebar {
					display: none;
				}
			}
		</style>

		<div class="iconic-settings-sidebar">
			<?php
			foreach ( $api_sidebars as $sidebar ) {
				if ( ! empty( $sidebar['content']['rendered'] ) ) {
					?>
					<div class="iconic-settings-sidebar__widget iconic-settings-sidebar__widget--blocks">
						<?php
						// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
						echo wp_kses_post( apply_filters( 'the_content', $sidebar['content']['rendered'] ) );
						?>
					</div>
					<?php
				}
			}

			if ( ! empty( $cross_sells ) ) {
				?>
				<div class="iconic-settings-sidebar__widget iconic-settings-sidebar__widget--works-well">
					<h3><?php esc_html_e( 'Works well with...', 'iconic-wds-gcal' ); ?></h3>

					<?php
					/**
					 * Fires before the cross sells products.
					 *
					 * @param array $cross_sells Cross sells.
					 */
					do_action( self::get_plugin_slug() . '_core_cross_sells_before_products', $cross_sells );
					?>

					<?php foreach ( $cross_sells as $cross_sell ) { ?>
						<?php
							$url = self::add_utm_query_args($cross_sell['link'], 'cross-sell');
						?>
						<div class="iconic-product">
							<div class="iconic-product__image">
								<?php echo wp_kses_post( $cross_sell['product']['image'] ); ?>
							</div>
							<div class="iconic-product__content">
								<h4 class="iconic-product__title"><a target="_blank" href="<?php echo esc_url( $url ); ?>" target="_blank"><?php echo wp_kses_post( $cross_sell['title']['rendered'] ); ?></a></h4>
								<p class="iconic-product__description"><?php echo wp_kses_post( $cross_sell['product']['description'] ); ?></p>
								<?php
								if ( ! empty( $cross_sell['product']['checkout_url'] ) ) {
									$checkout_url = self::add_utm_query_args($cross_sell['product']['checkout_url'], 'cross-sell');
									?>
									<div class="iconic-product__buttons">
										<p>
											<a href="<?php echo esc_url( $checkout_url ); ?>" class="button iconic-button iconic-button--small" target="_blank">
												<?php esc_html_e( 'Buy Plugin', 'iconic-wds-gcal' ); ?>
											</a>
										</p>
										<p><?php esc_html_e( '30 Day Money-Back Guarantee', 'iconic-wds-gcal' ); ?></p>
									</div>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
					<?php
					/**
					 * Fires after the cross sells products.
					 *
					 * @param array $cross_sells Cross sells.
					 */
					do_action( self::get_plugin_slug() . '_core_cross_sells_after_products', $cross_sells );
					?>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Add version number to header.
	 */
	public static function add_version() {
		printf( '<span style="margin: 0 0 0 auto; background: #f0f0f1; display: inline-block; padding: 0 10px; border-radius: 13px; height: 26px; line-height: 26px; white-space: nowrap; box-sizing: border-box; color: #656565;">v%s</span>', esc_attr( self::$args['version'] ) );
	}

	/**
	 * Get support button.
	 *
	 * @return string
	 */
	public static function support_link() {
		$url = self::add_utm_query_args('https://iconicwp.com/support/', 'support-link');

		return sprintf(
			'<a href="%s" class="button button-secondary" target="_blank">%s</a>',
			esc_url($url),
			esc_html__( 'Submit Ticket', 'iconic-wds-gcal' ) );
	}

	/**
	 * Get documentation button.
	 *
	 * @return string
	 */
	public static function documentation_link() {
		$url = self::add_utm_query_args(self::get_docs_url( 'collection' ), 'documentation-link');

		return sprintf(
			'<a href="%s" class="button button-secondary" target="_blank">%s</a>',
			$url,
			esc_html__( 'Read Documentation', 'iconic-wds-gcal' )
		);
	}

	/**
	 * Get account button.
	 *
	 * @return string
	 */
	public static function account_link() {
		$url = self::add_utm_query_args(self::$args['account_link'], 'account-link');

		return sprintf(
			'<a href="%s" class="button button-secondary" target="_blank">%s</a>',
			esc_url($url),
			esc_html__( 'Manage Your Account', 'iconic-wds-gcal' )
		);
	}

	/**
	 * Enqueue scripts.
	 */
	public static function enqueue_scripts() {
		if ( ! self::is_settings_page() && ! self::is_settings_page( '-account' ) ) {
			return;
		}

		// Required for the custom sidebar region.
		wp_enqueue_style( 'wp-block-styles' );
		wp_enqueue_style( 'wp-block-library' );
		wp_enqueue_style( 'wp-block-library-theme' );

		add_filter( 'should_load_separate_core_block_assets', '__return_true' );
		wp_enqueue_global_styles();
	}

	/**
	 * Add link to settings page.
	 *
	 * @param array $links Array of plugin links.
	 *
	 * @return array
	 */
	public static function plugin_action_links( $links ) {
		if ( empty( self::$settings_framework->settings_page ) ) {
			return $links;
		}

		$settings_path = sprintf( 'admin.php?page=%s', self::$settings_framework->settings_page['slug'] );

		$action_links = array(
			'settings' => '<a href="' . admin_url( $settings_path ) . '" aria-label="' . esc_attr__( 'View settings', 'iconic-wds-gcal' ) . '">' . esc_html__( 'Settings', 'iconic-wds-gcal' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Add UTM query args to URL
	 *
	 * @param string      $url      The URL
	 * @param string      $campaign The campaign
	 * @param string      $medium   The medium. Default: `Plugin`
	 * @param string      $content  The content. Default: plugin slug
	 * @param string|null $source   The source
	 * @return string
	 */
	protected static function add_utm_query_args($url, $campaign, $medium = 'Plugin', $content = 'iconic-wds-gcal', $source = null) {
		$args = [
			'utm_source' => $source ?? self::$args['utm_source'],
			'utm_medium' => $medium,
			'utm_campaign' => $campaign,
			'utm_content' => $content,
		];

		$new_url = add_query_arg($args, $url);

		return $new_url;
	}
}
