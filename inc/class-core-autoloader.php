<?php
/**
 * Class to automatically load plugin classes in inc/ folder.
 *
 * @package iconic-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_WDS_Gcal_Core_Autoloader' ) ) {
	return;
}

/**
 * Iconic_WDS_Gcal_Core_Autoloader.
 *
 * @class    Iconic_WDS_Gcal_Core_Autoloader
 * @version  1.0.1
 */
class Iconic_WDS_Gcal_Core_Autoloader {
	/**
	 * Single instance of the Iconic_WDS_Gcal_Core_Autoloader object.
	 *
	 * @var Iconic_WDS_Gcal_Core_Autoloader
	 */
	public static $single_instance = null;

	/**
	 * Class args.
	 *
	 * @var array
	 */
	public static $args = array();

	/**
	 * Creates/returns the single instance Iconic_WDS_Gcal_Core_Autoloader object.
	 *
	 * @param array $args Arguments.
	 *
	 * @return Iconic_WDS_Gcal_Core_Autoloader
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
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoloader
	 *
	 * Classes should reside within /inc and follow the format of
	 * Iconic_The_Name ~ class-the-name.php or {{class-prefix}}The_Name ~ class-the-name.php
	 *
	 * @param string $class_name Class Name.
	 */
	private static function autoload( $class_name ) {
		/**
		 * If the class being requested does not start with our prefix,
		 * we know it's not one in our project
		 */
		if ( 0 !== strpos( $class_name, self::$args['prefix'] ) ) {
			return;
		}

		$file_name = strtolower(
			str_replace(
				array( self::$args['prefix'], '_' ),
				array( '', '-' ),
				$class_name
			)
		);

		$file = self::$args['inc_path'] . 'class-' . $file_name . '.php';

		// Include found file.
		if ( file_exists( $file ) ) {
			require $file;

			return;
		}
	}
}
