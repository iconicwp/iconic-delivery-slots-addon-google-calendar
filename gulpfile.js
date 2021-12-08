/**
 * Load the "Iconic Plugin" Gulp module.
 */
require( 'iconic-plugin' )( {
	plugin_data: require( './plugin-data.json' ),
	plugin_id: 1038.1,
	plugin_name: 'Google Calendar addon for WooCommerce Delivery Slots',
	plugin_filename: 'iconic-wds-google-calendar',
	premium_suffix: true,
	textdomain: 'jckwds',
	is_envato_constant: false,
	is_upload: true,
	class_prefix: 'Iconic_WDS_Gcal_',
	deps: {
		// 'src' : 'dest'
		'vendor/iconicwp/iconic-core/class-core-licence.php': 'inc',
		'vendor/iconicwp/iconic-core/class-core-settings.php': 'inc',
		'vendor/iconicwp/iconic-core/class-core-helpers.php': 'inc',
		'vendor/iconicwp/iconic-core/class-core-cross-sells.php': 'inc',
		'vendor/iconicwp/iconic-core/class-core-autoloader.php': 'inc',
		'vendor/iconicwp/iconic-core/plugin-icon.png': 'assets/img',
		'vendor/iconicwp/workflows/release/release.yml': '.github/workflows',
		'vendor/iconicwp/workflows/phpcs/auto_assign.yml': '.github/workflows',
		'vendor/iconicwp/workflows/phpcs/phpcs.yml': '.github/workflows',
		'vendor/iconicwp/workflows/phpcs/phpcs.xml': '.',
		'vendor/iconicwp/workflows/changelog/changelog.yml': '.github/workflows',
		'vendor/iconicwp/workflows/templates/ISSUE_TEMPLATE/bug.md': '.github/ISSUE_TEMPLATE',
		'vendor/iconicwp/workflows/templates/ISSUE_TEMPLATE/feature.md': '.github/ISSUE_TEMPLATE',
		'vendor/iconicwp/workflows/templates/PULL_REQUEST_TEMPLATE.md': '.github',
		'vendor/iconicwp/iconic-core/plugin-icon.png': 'assets/img',
	}
} );