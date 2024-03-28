<?php
/**
 * WDS Ajax class.
 *
 * @package Iconic_WDS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WDS Google Calendar Integration.
 */
class Iconic_WDS_Gcal_Google_Calendar {

	/**
	 * Key used to store the Google Calendar token in the database.
	 *
	 * @var string
	 */
	const TOKEN_OPTION_KEY = 'wds_google_access_token';

	/**
	 * Key used to store the event ID in order postmeta.
	 */
	const CALENDAR_ID_META_KEY = '_iconic_wds_gcal_event_id';

	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'authenticate' ), 11 );
		add_action( 'init', array( __CLASS__, 'disconnect' ), 11 );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'order_status_changed' ), 10, 3 );
		add_action( 'save_post', array( __CLASS__, 'timeslot_changed' ), 20, 3 );
		add_action( 'deleted_post', array( __CLASS__, 'delete_event_on_order_deleted' ), 10, 1 );
	}

	/**
	 * Return Google API client.
	 */
	public static function get_client() {
		$client = new Google\Client();
		$config = array(
			'client_id'        => Iconic_WDS_Core_Settings::get_setting_from_db( 'integrations', 'google_api' ),
			'client_secret'    => Iconic_WDS_Core_Settings::get_setting_from_db( 'integrations', 'google_secret' ),
			'redirect_uris'    => array( self::get_redirect_url() ),
			'application_name' => 'Iconic WDS',
		);
		$client->setAuthConfig( $config );
		$client->setAccessType( 'offline' );
		$client->addScope( Google\Service\Calendar::CALENDAR_EVENTS );
		$client->addScope( Google\Service\Calendar::CALENDAR_READONLY );
		$client->setIncludeGrantedScopes( true );
		$client->setApprovalPrompt( 'force' );

		$access_token = get_option( self::TOKEN_OPTION_KEY, false );

		if ( $access_token && ! isset( $access_token['error'] ) ) {
			$client->setAccessToken( $access_token );
		}

		try {
			// If the token has expired.
			if ( $client->isAccessTokenExpired() && $client->getRefreshToken() ) {
				$access_token = $client->fetchAccessTokenWithRefreshToken( $client->getRefreshToken() );
				if ( ! isset( $access_token['error'] ) ) {
					update_option( self::TOKEN_OPTION_KEY, $access_token );
					self::log( 'Token refreshed' );
					self::log( print_r( $access_token, true ) );
				} else {
					self::log( 'Couldn\'t refresh access token.' );
					self::log( print_r( get_option( self::TOKEN_OPTION_KEY, false ), true ) );
					self::log( print_r( $access_token, true ) );
				}
			}
		} catch ( Exception $e ) {
			self::log( 'Couldnt refresh access token.' );
			self::log( print_r( get_option( self::TOKEN_OPTION_KEY, false ), true ) );
			self::log( print_r( $access_token, true ) );
			self::log( $e->getMessage() );
		}

		return $client;
	}

	/**
	 * Get auth button.
	 *
	 * @return void
	 */
	public static function get_auth_button() {
		$nonce = wp_create_nonce( 'wds-google-calendar-nonce' );
		if ( self::is_connection_active() ) {
			$url = self::get_redirect_url( 'disconnect', $nonce ) . '&_nonce=' . $nonce;
			echo sprintf( '<div class="iconic_wds_gcal_connected"><span>Connected.<span> <a href="%s" class="button iconic_wds_gcal_disconnect_btn">%s</a></div>', esc_attr( $url ), esc_html__( 'Disconnect', 'jckwds' ) );
		} else {
			$url = self::get_redirect_url( 'connect', $nonce ) . '&_nonce=' . $nonce;
			echo sprintf( '<a href="%s" class="button iconic_wds_gcal_auth_btn">%s</a>', esc_attr( $url ), esc_html__( 'Authenticate', 'jckwds' ) );
		}
	}

	/**
	 * Generate Redirect URI field.
	 *
	 * @param array $args Argument.
	 */
	public static function generate_redirect_url_field( $args ) {
		$redirect_url = self::get_redirect_url();
		echo '<input type="text" readonly name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . esc_attr( $redirect_url ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="regular-text ' . esc_attr( $args['class'] ) . '" />';
		echo sprintf( '<button type="button" class="button iconic-wds-gcal-redirect-copy">%s</button>', esc_html__( 'Copy', 'jckwds' ) );
	}

	/**
	 * Get redirect URL.
	 *
	 * @param string $action Action to perform.
	 */
	public static function get_redirect_url( $action = 'connect' ) {
		$key = sprintf( 'iconic_wds_gcal_callback_%s', $action );
		return site_url( "/?$key=1" );
	}

	/**
	 * Authenticate.
	 */
	public static function authenticate() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Cannot pass nonce in the callback URL.
		if ( ! isset( $_GET['iconic_wds_gcal_callback_connect'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$client  = self::get_client();
		$session = get_transient( 'iconic_wds_gcal_authenticate' );
		$code    = filter_input( INPUT_GET, 'code' );

		if ( empty( $session ) || get_current_user_id() !== intval( $session ) || empty( $code ) ) {
			$auth_url = $client->createAuthUrl();
			set_transient( 'iconic_wds_gcal_authenticate', get_current_user_id(), 60 * 5 );
			// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- need to redirect to Google to authenticate.
			wp_redirect( $auth_url );
			exit;
		}

		delete_transient( 'iconic_wds_gcal_calendar_list' );
		$access_token = $client->fetchAccessTokenWithAuthCode( $code );
		delete_transient( 'iconic_wds_gcal_authenticate' );
		update_option( self::TOKEN_OPTION_KEY, $access_token );
		wp_safe_redirect( admin_url( 'admin.php?page=jckwds-settings' ) );
		exit;
	}

	/**
	 * Disconnect the connection.
	 *
	 * @return void
	 */
	public static function disconnect() {
		if ( ! isset( $_GET['iconic_wds_gcal_callback_disconnect'] ) ) {
			return;
		}

		// verify nonce.
		if ( ! isset( $_GET['_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_nonce'] ), 'wds-google-calendar-nonce' ) ) {
			return;
		}

		delete_option( self::TOKEN_OPTION_KEY );
		// TODO delete calendar setting too.
		wp_safe_redirect( admin_url( 'admin.php?page=jckwds-settings' ) );
		exit;
	}

	/**
	 * Is connection active?
	 *
	 * @return bool
	 */
	public static function is_connection_active() {
		$access_token = get_option( self::TOKEN_OPTION_KEY, false );

		if ( ! $access_token || isset( $access_token['error'] ) ) {
			return false;
		}

		$client_id     = Iconic_WDS_Core_Settings::get_setting_from_db( 'integrations', 'google_api' );
		$client_secret = Iconic_WDS_Core_Settings::get_setting_from_db( 'integrations', 'google_secret' );

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get all calender for the user.
	 *
	 * @param bool $force_update Force update.
	 *
	 * @return array
	 */
	public static function get_calendars( $force_update = false ) {
		$calendar_list = get_transient( 'iconic_wds_gcal_calendar_list' );

		if ( ! $force_update && false !== $calendar_list ) {
			return $calendar_list;
		}

		$client_id = Iconic_WDS_Core_Settings::get_setting_from_db( 'integrations', 'google_api' );
		$calendars = array(
			'' => esc_html__( '--Select a Calendar--', 'jckwds' ),
		);

		if ( empty( $client_id ) ) {
			return array();
		}

		try {
			$client        = self::get_client();
			$service       = new Google_Service_Calendar( $client );
			$calendar_list = $service->calendarList->listCalendarList(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			foreach ( $calendar_list as $calendar ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				if ( 'owner' !== $calendar->accessRole ) {
					continue;
				}

				$calendars[ $calendar->getId() ] = $calendar->getSummary();
			}

			set_transient( 'iconic_wds_gcal_calendar_list', $calendars, 24 * 60 * 60 );
		} catch ( Exception $ex ) {
			$calendars = array();
		}

		return $calendars;
	}

	/**
	 * Update/Delete event to Google Calendar.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $from     From status.
	 * @param string $to       To status.
	 *
	 * @return void
	 */
	public static function order_status_changed( $order_id, $from, $to ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$calendar_id = self::get_calendar_id();

		if ( ! $calendar_id ) {
			return;
		}

		if ( 'cancelled' === $to || 'refunded' === $to ) {
			self::delete_event( $order );
		} else {
			self::update_event( $order );
		}
	}

	/**
	 * Create/Update event to Google Calendar.
	 *
	 * @param int $order Order ID.
	 *
	 * @return string|bool
	 */
	public static function update_event( $order ) {
		if ( ! $order ) {
			return false;
		}

		$event_id = get_post_meta( $order->get_id(), self::CALENDAR_ID_META_KEY, true );

		if ( empty( $event_id ) ) {
			self::create_event( $order );
		} else {
			self::edit_event( $event_id, $order );
		}

		return $event_id;
	}

	/**
	 * Create event in Google calendar.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return string|bool Event ID.
	 */
	public static function create_event( $order ) {
		$calendar_id = self::get_calendar_id();
		$timestmap   = $order->get_meta( 'jckwds_timestamp' );

		if ( empty( $calendar_id ) || empty( $timestmap ) ) {
			return false;
		}

		try {
			$client  = self::get_client();
			$service = new Google_Service_Calendar( $client );

			$event = new Google_Service_Calendar_Event();
			$event = self::prepare_event( $event, $order );

			$event = $service->events->insert( $calendar_id, $event );

			if ( $event ) {
				update_post_meta( $order->get_id(), self::CALENDAR_ID_META_KEY, $event->getId() );
			}

			return $event->getId();
		} catch ( Exception $ex ) {
			self::log( $ex->getMessage() );

			return false;
		}
	}

	/**
	 * Edit event in Google calendar.
	 *
	 * @param string   $event_id Event ID.
	 * @param WC_Order $order    Order.
	 *
	 * @return bool|Event
	 */
	public static function edit_event( $event_id, $order ) {
		$calendar_id = self::get_calendar_id();
		$timestmap   = $order->get_meta( 'jckwds_timestamp' );

		if ( empty( $calendar_id ) || empty( $timestmap ) || empty( $event_id ) ) {
			return false;
		}

		$client = self::get_client();

		// Update event in google calendar.
		$service = new Google_Service_Calendar( $client );
		$event   = $service->events->get( $calendar_id, $event_id );
		$event   = self::prepare_event( $event, $order );

		try {
			$event = $service->events->patch( $calendar_id, $event_id, $event );

			return $event;
		} catch ( Exception $ex ) {
			self::log( $ex->getMessage() );

			return false;
		}
	}

	/**
	 * Prepare event object.
	 *
	 * @param Object   $event Event object.
	 * @param WC_Order $order Order.
	 *
	 * @return Object
	 */
	public static function prepare_event( $event, $order ) {
		global $iconic_wds;

		$calendar_id     = self::get_calendar_id();
		$timestamp_start = $order->get_meta( 'jckwds_timestamp' );
		$timestamp_end   = $timestamp_start;
		$db_row          = Iconic_WDS_Reservations::get_reservation_for_order( $order->get_id() );

		$summary     = self::replace_placeholders( $iconic_wds->settings['integrations_google_setting_event_title'], $order, 'summary' );
		$description = self::replace_placeholders( $iconic_wds->settings['integrations_google_setting_event_description'], $order, 'description' );

		$event->setSummary( $summary );
		$event->setLocation( wp_strip_all_tags( str_replace( '<br/>', "\n", $order->get_formatted_shipping_address() ) ) );
		$event->setDescription( $description );

		$start = new Google_Service_Calendar_EventDateTime();
		$start->setDateTime( gmdate( 'c', $timestamp_start ) );
		$start->setTimeZone( 'UTC' );

		// Get end timestamp.
		if ( $db_row && $db_row->endtime && $db_row->starttime ) {
			$starttime     = DateTime::createFromFormat( 'Hi', $db_row->starttime, wp_timezone() );
			$endtime       = DateTime::createFromFormat( 'Hi', $db_row->endtime, wp_timezone() );
			$diff          = $endtime->getTimestamp() - $starttime->getTimestamp();
			$timestamp_end = $timestamp_start + $diff;
		}

		$end = new Google_Service_Calendar_EventDateTime();
		$end->setDateTime( gmdate( 'c', $timestamp_end ) );
		$end->setTimeZone( 'UTC' );

		$event->setStart( $start );
		$event->setEnd( $end );

		return $event;
	}

	/**
	 * Update event if timeslot has changed.
	 *
	 * @param int     $order_id Order ID.
	 * @param WP_Post $post     Post object.
	 * @param bool    $update   Whether this is an existing post being updated..
	 *
	 * @return void
	 */
	public static function timeslot_changed( $order_id, $post, $update ) {
		if ( 'shop_order' !== $post->post_type ) {
			return;
		}

		$date_changed = filter_input( INPUT_POST, 'jckwds-date-changed' );

		if ( empty( $order_id ) || empty( $date_changed ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		self::update_event( $order );
	}

	/**
	 * Get calendar ID.
	 * Call this function after settings have been initialized (init hook, priority 10).
	 *
	 * @return string|false
	 */
	public static function get_calendar_id() {
		global $iconic_wds;
		return isset( $iconic_wds->settings['integrations_google_select_calendar'] ) ? $iconic_wds->settings['integrations_google_select_calendar'] : false;
	}

	/**
	 * Delete event.
	 *
	 * @param WC_Order $order Order.
	 *
	 * @return void
	 */
	public static function delete_event( $order ) {
		$calendar_id = self::get_calendar_id();
		$event_id    = $order->get_meta( self::CALENDAR_ID_META_KEY );

		if ( empty( $event_id ) || empty( $calendar_id ) ) {
			return;
		}

		$client  = self::get_client();
		$service = new Google_Service_Calendar( $client );

		$service->events->delete( $calendar_id, $event_id );

		delete_post_meta( $order->get_id(), self::CALENDAR_ID_META_KEY );
	}

	/**
	 * Delete event if order is deleted.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public static function delete_event_on_order_deleted( $order_id ) {
		$post_type = get_post_type( $order_id );

		if ( 'shop_order' !== $post_type && 'shop_order_refund' !== $post_type ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		self::delete_event( $order );
	}

	/**
	 * Replace placeholders.
	 *
	 * @param string   $string  The subject in which we want to replace placeholders.
	 * @param WC_Order $order   Order.
	 * @param string   $context Context.
	 *
	 * @return string
	 */
	public static function replace_placeholders( $string, $order, $context = '' ) {
		$string = str_replace( '{SITE_NAME}', get_bloginfo( 'name' ), $string );
		$string = str_replace( '{ORDER_NUMBER}', $order->get_order_number(), $string );
		$string = str_replace( '{ORDER_DATE_TIME}', $order->get_date_created()->format( 'Y-m-d H:i:s' ), $string );
		$string = str_replace( '{DELIVERY_DATE_TIME}', $order->get_meta( 'jckwds_date' ) . ' ' . $order->get_meta( 'jckwds_timeslot' ), $string );
		$string = str_replace( '{CUSTOMER_NAME}', $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(), $string );
		$string = str_replace( '{CUSTOMER_EMAIL}', $order->get_billing_email(), $string );
		$string = str_replace( '{CUSTOMER_ADDRESS}', wp_strip_all_tags( str_replace( '<br/>', "\n", $order->get_formatted_billing_address() ) ), $string );
		$string = str_replace( '{CUSTOMER_PHONE}', $order->get_billing_phone(), $string );
		$string = str_replace( '{NOTE}', $order->get_customer_note(), $string );

		if ( false !== strpos( $string, '{CART_ITEMS}' ) ) {
			$cart_items = '';

			foreach ( $order->get_items() as $item ) {
				$cart_items .= $item['name'] . ' x ' . $item['qty'] . ', ';
			}

			$cart_items = trim( $cart_items, ', ' );

			$string = str_replace( '{CART_ITEMS}', $cart_items, $string );
		}

		/**
		 * Event description after placeholders have been replaced.
		 *
		 * @since 0.1.0.
		 */
		return apply_filters( 'iconic_wds_gcal_replace_placeholder', $string, $order, $context );
	}

	/**
	 * Log error.
	 *
	 * @param string $message Message.
	 *
	 * @return void
	 */
	public static function log( $message ) {
		$logger = wc_get_logger();
		$logger->info( $message, array( 'source' => 'iconic-wds-gcal' ) );
	}

}
