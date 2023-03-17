<?php
/**
 * WDS Settings.
 *
 * @package Iconic_WDS_Gcal
 */

add_filter( 'wpsf_register_settings_jckwds', 'jckwds_gcal_settings', 11 );

/**
 * Settings
 *
 * @param array $wpsf_settings Settings.
 *
 * @return array
 */
function jckwds_gcal_settings( $wpsf_settings ) {
	global $iconic_wds;

	if ( ! $iconic_wds || ! function_exists( 'WC' ) ) {
		return $wpsf_settings;
	}

	// Add integrations tab if it doesn't exist already.
	if ( ! jckwds_gcal_search_tab( $wpsf_settings['tabs'], 'integrations' ) ) {
		$wpsf_settings['tabs'][] = array(
			'id'    => 'integrations',
			'title' => __( 'Integrations', 'iconic-wds-gcal' ),
		);
	}

	$document_url = 'https://iconicwp.com/docs/woocommerce-delivery-slots/how-to-integrate-google-calendar-with-iconic-delivery-slots/';

	$gcal_fields = array(
		array(
			'id'       => 'api',
			'title'    => __( 'Client ID', 'iconic-wds-gcal' ),
			'subtitle' => '',
			'type'     => 'text',
			'link'     => array(
				'text'     => __( 'Get your API key', 'iconic-wds-gcal' ),
				'url'      => $document_url,
				'type'     => 'tooltip',
				'external' => true,
			),
		),
		array(
			'id'       => 'secret',
			'title'    => __( 'Client Secret', 'iconic-wds-gcal' ),
			'subtitle' => '',
			'type'     => 'text',
			'link'     => array(
				'text'     => __( 'Get your Secret Key', 'iconic-wds-gcal' ),
				'url'      => $document_url,
				'type'     => 'tooltip',
				'external' => true,
			),
		),
		array(
			'id'       => 'redirect_url',
			'title'    => __( 'Redirect URL', 'iconic-wds-gcal' ),
			'subtitle' => '',
			'type'     => 'custom',
			'output'   => array( 'Iconic_WDS_Gcal_Google_Calendar', 'generate_redirect_url_field' ),
		),
		array(
			'id'       => 'authenication_button',
			'title'    => __( 'Authenticate', 'iconic-wds-gcal' ),
			'subtitle' => '',
			'type'     => 'custom',
			'output'   => array( 'Iconic_WDS_Gcal_Google_Calendar', 'get_auth_button' ),
		),
	);

	if ( Iconic_WDS_Gcal_Google_Calendar::is_connection_active() ) {
		$gcal_fields[] = array(
			'id'       => 'select_calendar',
			'title'    => __( 'Calendar', 'iconic-wds-gcal' ),
			'subtitle' => '',
			'type'     => 'select',
			'choices'  => Iconic_WDS_Gcal_Google_Calendar::get_calendars(),
		);
	}

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'integrations',
		'section_id'          => 'google',
		'section_title'       => __( 'Google Calender Authentication', 'iconic-wds-gcal' ),
		// translators: %s: URL to the document.
		'section_description' => sprintf( __( 'Follow <a href="%s" target="_blank">this guide</a> to create a Google App and generate Client ID and Secret key.', 'iconic-wds-gcal' ), $document_url ),
		'section_order'       => 0,
		'fields'              => $gcal_fields,
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'integrations',
		'section_id'          => 'google_setting',
		'section_title'       => __( 'Google Calender Settings', 'iconic-wds-gcal' ),
		'section_description' => __( 'Available placeholders: {SITE_NAME}, {ORDER_NUMBER}, {ORDER_STATUS}, {ORDER_DATE_TIME}, {DELIVERY_DATE_TIME}, {CUSTOMER_NAME}, {CUSTOMER_EMAIL}, {CUSTOMER_ADDRESS}, {CUSTOMER_PHONE}, {NOTE} and {CART_ITEMS}.', 'jckwds' ),
		'section_order'       => 1,
		'fields'              => array(
			array(
				'id'       => 'event_title',
				'title'    => __( 'Event Title', 'iconic-wds-gcal' ),
				'subtitle' => '',
				'default'  => '{SITE_NAME} - Order #{ORDER_NUMBER}',
				'type'     => 'text',
			),
			array(
				'id'       => 'event_description',
				'title'    => __( 'Event Description', 'iconic-wds-gcal' ),
				'subtitle' => '',
				'default'  => "Order #{ORDER_NUMBER} on {SITE_NAME}.\n\n{CART_ITEMS} ",
				'type'     => 'textarea',
			),
		),
	);

	return $wpsf_settings;
}

/**
 * Find tabs with the given ID.
 *
 * @param array  $tabs Array of tabs.
 * @param string $tab_id Tab ID.
 *
 * @return bool
 */
function jckwds_gcal_search_tab( $tabs, $tab_id ) {
	foreach ( $tabs as $tab ) {
		if ( $tab['id'] === $tab_id ) {
			return $tab;
		}
	}

	return false;
}
