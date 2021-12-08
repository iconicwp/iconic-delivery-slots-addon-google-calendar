(function( $, document ) {

	var iconic_wds_gcal_admin = {
		/**
		 * On load.
		 */
		on_load: function () {
			iconic_wds_gcal_admin.hide_authenticate_button();
			iconic_wds_gcal_admin.copy_redirect_url_to_clipboard();
		},

		/**
		 * Hide authenticate button when Client ID or secret changes.
		 */
		hide_authenticate_button: function () {
			$( '#integrations_google_api, #integrations_google_secret' ).change( function () {
				if ( $( '.iconic_wds_gcal_auth_btn' ).length ) {
					$( '.iconic_wds_gcal_auth_btn' ).parent().parent().hide();
				}
			} );
		},

		/**
		 * Copy redirect URL to clipboard when clicked on thecopy button.
		 */
		copy_redirect_url_to_clipboard: function () {
			$( '.iconic-wds-gcal-redirect-copy' ).click( function () {
				navigator.clipboard.writeText( $( '#integrations_google_redirect_url' ).val() );
			} );
		}
	};


	$( window ).load( iconic_wds_gcal_admin.on_load );

}( jQuery, document ));