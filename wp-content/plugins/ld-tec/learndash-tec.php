<?php
/**
 * Plugin Name: LearnDash LMS - The Events Calendar Integration
 * Plugin URI: https://www.learndash.com/
 * Description: Integrate LearnDash LMS with The Events Calendar
 * Version: 1.0.3
 * Requires PHP: 7.4
 * Requires at least: 6.6
 * Tested up to: 6.9
 * Author: LearnDash
 * Author URI: https://www.learndash.com/
 * Text Domain: learndash-tec
 * Domain Path: languages
 *
 * @package LearnDash\The_Events_Calendar
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

use LearnDash\Core\App;
use LearnDash\Core\Autoloader;
use LearnDash\The_Events_Calendar\Assets;
use LearnDash\The_Events_Calendar\Dependency_Checker;
use LearnDash\The_Events_Calendar\Integration;
use LearnDash\The_Events_Calendar\Plugin;

define( 'LEARNDASH_TEC_VERSION', '1.0.3' );
define( 'LEARNDASH_TEC_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_TEC_URL', plugins_url( '/', __FILE__ ) );
define( 'LEARNDASH_TEC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); // Kept for backward compatibility.
define( 'LEARNDASH_TEC_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // Kept for backward compatibility.
define( 'LEARNDASH_TEC_FILE', __FILE__ );

add_action(
	'plugins_loaded',
	function () {
		/**
		 * Filters the languages directory path for the plugin.
		 *
		 * @since 1.0
		 *
		 * @param string $lang_dir The languages directory path.
		 *
		 * @return string
		 */
		$lang_dir = apply_filters(
			'learndash_tec_languages_directory',
			plugin_basename(
				__DIR__
			) . '/languages'
		);

		load_plugin_textdomain(
			'learndash-tec',
			false,
			$lang_dir
		);
	}
);

$learndash_tec_dependency_checker = new Dependency_Checker();

$learndash_tec_dependency_checker->set_dependencies(
	[
		'sfwd-lms/sfwd_lms.php'                       => [
			'label'            => '<a href="https://www.learndash.com" target="_blank">' . __( 'LearnDash LMS', 'learndash-tec' ) . '</a>',
			'class'            => 'SFWD_LMS',
			'version_constant' => 'LEARNDASH_VERSION',
			'min_version'      => '4.7.0',
		],
		'the-events-calendar/the-events-calendar.php' => [
			'label'       => '<a href="https://theeventscalendar.com/" target="_blank">' . __( 'The Events Calendar', 'learndash-tec' ) . '</a>',
			'min_version' => '5.12.0',
		],
		'event-tickets/event-tickets.php'             => [
			'label'       => '<a href="https://theeventscalendar.com/products/wordpress-event-tickets/" target="_blank">' . __( 'Event Tickets', 'learndash-tec' ) . '</a>',
			'min_version' => '5.2.0',
		],
		'event-tickets-plus/event-tickets-plus.php'   => [
			'label'       => '<a href="https://theeventscalendar.com/products/wordpress-event-tickets/" target="_blank">' . __( 'Event Tickets Plus', 'learndash-tec' ) . '</a>',
			'min_version' => '5.0.0',
		],
	]
);

$learndash_tec_dependency_checker->set_message(
	esc_html__( 'LearnDash LMS - The Events Calendar Integration requires the following plugin(s) to be active:', 'learndash-tec' )
);

add_action(
	'learndash_init',
	function () use ( $learndash_tec_dependency_checker ) {
		// If plugin requirements aren't met, don't run anything else to prevent possible fatal errors.
		if ( ! $learndash_tec_dependency_checker->check_dependency_results() || php_sapi_name() === 'cli' ) {
			return;
		}

		learndash_tec_extra_includes();
		learndash_tec_extra_autoloading();

		learndash_register_provider( Plugin::class );
	}
);

/**
 * Include extra files.
 *
 * @since 1.0.2
 *
 * @return void
 */
function learndash_tec_extra_includes(): void {
	include LEARNDASH_TEC_PLUGIN_PATH . 'includes/class-assets.php';
	include LEARNDASH_TEC_PLUGIN_PATH . 'includes/class-integration.php';

	App::setVar( 'learndash_tec_assets', new Assets() );
	App::setVar( 'learndash_tec_integration', new Integration() );
}

/**
 * Sets up the autoloader for extra classes, which are not in the src/App directory.
 *
 * @since 1.0.2
 *
 * @return void
 */
function learndash_tec_extra_autoloading(): void {
	$autoloader = Autoloader::instance();

	// Iterate through all files under ./src/deprecated.
	$iterator = new RecursiveDirectoryIterator( LEARNDASH_TEC_DIR . 'src/deprecated/' );
	$files    = new RecursiveIteratorIterator( $iterator, RecursiveIteratorIterator::SELF_FIRST );

	foreach ( $files as $file ) {
		if (
			! $file instanceof SplFileInfo
			|| ! $file->isFile()
			|| $file->getExtension() !== 'php'
		) {
			continue;
		}

		if ( strstr( $file->getRealPath(), 'functions' ) ) {
			// If this was named functions.php in any directory, load it.
			include_once $file->getRealPath();
		} else {
			// Construct the proper Class Name based on the file path.
			$class_name = str_replace(
				'/',
				'\\',
				(string) preg_replace(
					'/.*?src\/deprecated\/(.*?)\.php/',
					'$1',
					$file->getRealPath()
				)
			);

			$autoloader->register_class( $class_name, $file->getRealPath() );
		}
	}

	$autoloader->register_autoloader();
}
