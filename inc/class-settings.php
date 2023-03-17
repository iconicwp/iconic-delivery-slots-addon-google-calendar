<?php 

/**
 * Validate settings.
 */
class Iconic_WDS_Gcal_Settings {
	/**
	 * Init.
	 */
	public static function run() {
		add_filter( 'jckwds_settings_validate', array( __CLASS__, 'validate_settings' ), 10, 1 );
	}

	/**
	 * Validate settings.
	 *
	 * @param array $settings Settings.
	 */
	public static function validate_settings( $settings ) {
		if ( isset( $settings['integrations_google_api'] ) ) {
			$settings['integrations_google_api'] = trim( $settings['integrations_google_api'] );
		}

		if ( isset( $settings['integrations_google_secret'] ) ) {
			$settings['integrations_google_secret'] = trim( $settings['integrations_google_secret'] );
		}

		$db_client_id = Iconic_WDS_Core_Settings::get_setting_from_db( 'integrations', 'google_api' );
		$db_secret    = Iconic_WDS_Core_Settings::get_setting_from_db( 'integrations', 'google_secret' );

		$aaa = $settings['integrations_google_api'];
		if ( $db_client_id !== $settings['integrations_google_api'] || $db_secret !== $settings['integrations_google_secret'] ) {
			$settings['integrations_google_select_calendar'] = '';
			delete_option( Iconic_WDS_Gcal_Google_Calendar::TOKEN_OPTION_KEY );

			Iconic_WDS_Gcal_Google_Calendar::log( 'Client ID/Secret mismatch. Deleting the token.' );
			Iconic_WDS_Gcal_Google_Calendar::log( '$db_client_id: ' . $db_client_id );
			Iconic_WDS_Gcal_Google_Calendar::log( '$settings[integrations_google_api]: ' . $settings['integrations_google_api'] );
			Iconic_WDS_Gcal_Google_Calendar::log( '$db_secret: ' . $db_secret );
			Iconic_WDS_Gcal_Google_Calendar::log( '$settings[integrations_google_secret]: ' . $settings['integrations_google_secret'] );
		}

		return $settings;
	}
}
