<?php
/**
 * Legacy The Events Calendar integration main class.
 *
 * @since 1.0
 * @deprecated 1.0.2
 *
 * @package LearnDash\The_Events_Calendar\Deprecated
 */

namespace LearnDash;

use LearnDash\The_Events_Calendar\Assets;
use LearnDash\The_Events_Calendar\Dependency_Check;
use LearnDash\The_Events_Calendar\Integration;

/**
 * Main The_Events_Calendar class.
 *
 * This main class is responsible for instantiating the class,
 * including the necessary files used throughout the plugin,
 * and loading the plugin translation files.
 *
 * @since 1.0
 * @deprecated 1.0.2
 */
class The_Events_Calendar {
	/**
	 * The one and only true The_Events_Calendar instance.
	 *
	 * @since 1.0
	 * @deprecated 1.0.2
	 *
	 * @var self $instance
	 */
	private static $instance;

	/**
	 * Integration object.
	 *
	 * @since 1.0
	 * @deprecated 1.0.2
	 *
	 * @var Integration
	 */
	public $integration;

	/**
	 * Assets object.
	 *
	 * @since 1.0
	 * @deprecated 1.0.2
	 *
	 * @var Assets
	 */
	public $assets;

	/**
	 * Instantiate the main class.
	 *
	 * This function instantiates the class, initialize all functions and return the object.
	 *
	 * @return self The one and only true The_Events_Calendar instance.
	 * @deprecated 1.0.2
	 *
	 * @since 1.0
	 */
	public static function instance() {
		_deprecated_function( __METHOD__, '1.0.2' );

		if ( ! isset( self::$instance ) && ( ! self::$instance instanceof The_Events_Calendar ) ) {
			self::$instance = new The_Events_Calendar();
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', [ self::$instance, 'load_textdomain' ] );

			self::$instance->check_dependency();

			add_action(
				'plugins_loaded',
				function () {
					if ( Dependency_Check::get_instance()->check_dependency_results() ) {
						self::$instance->includes();
						self::$instance->integration = new Integration();
						self::$instance->assets      = new Assets();
					}
				}
			);
		}

		return self::$instance;
	}

	/**
	 * Function for setting up constants.
	 *
	 * This function is used to set up constants used throughout the plugin.
	 *
	 * @since 1.0
	 * @deprecated 1.0.2
	 */
	public function setup_constants() {
		_deprecated_function( __METHOD__, '1.0.2' );

		// Plugin version.
		if ( ! defined( 'LEARNDASH_TEC_VERSION' ) ) {
			define( 'LEARNDASH_TEC_VERSION', '1.0.1' );
		}

		// Plugin file.
		if ( ! defined( 'LEARNDASH_TEC_FILE' ) ) {
			define( 'LEARNDASH_TEC_FILE', __FILE__ );
		}

		// Plugin folder path.
		if ( ! defined( 'LEARNDASH_TEC_PLUGIN_PATH' ) ) {
			define( 'LEARNDASH_TEC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL.
		if ( ! defined( 'LEARNDASH_TEC_PLUGIN_URL' ) ) {
			define( 'LEARNDASH_TEC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Load text domain used for translation.
	 *
	 * This function loads mo and po files used to translate
	 * text strings used throughout the plugin.
	 *
	 * @since 1.0
	 * @deprecated 1.0.2
	 */
	public function load_textdomain() {
		_deprecated_function( __METHOD__, '1.0.2' );

		// Set filter for plugin language directory.
		$lang_dir = LEARNDASH_TEC_PLUGIN_PATH . 'languages/';

		/**
		 * Filters the languages directory path for the plugin.
		 *
		 * @since 1.0
		 *
		 * @param string $lang_dir The languages directory path.
		 *
		 * @return string
		 */
		$lang_dir = apply_filters( 'learndash_tec_languages_directory', $lang_dir );

		// Load plugin translation file.
		load_plugin_textdomain( 'learndash-tec', false, $lang_dir );

		// include plugin translation class.
		include LEARNDASH_TEC_PLUGIN_PATH . 'includes/class-translations.php';
	}

	/**
	 * Check and set plugin dependencies.
	 *
	 * @return void
	 * @deprecated 1.0.2
	 *
	 * @since 1.0
	 */
	public function check_dependency() {
		_deprecated_function( __METHOD__, '1.0.2' );

		include LEARNDASH_TEC_PLUGIN_PATH . 'includes/class-dependency-check.php';

		Dependency_Check::get_instance()->set_dependencies(
			[
				'sfwd-lms/sfwd_lms.php'           => [
					'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
					'class'       => 'SFWD_LMS',
					'min_version' => '3.6.0',
				],
				'the-events-calendar/the-events-calendar.php' => [
					'label'       => '<a href="https://theeventscalendar.com/">The Events Calendar</a>',
					'min_version' => '5.12.0',
				],
				'event-tickets/event-tickets.php' => [
					'label'       => '<a href="https://theeventscalendar.com/products/wordpress-event-tickets/">Event Tickets</a>',
					'min_version' => '5.2.0',
				],
				'event-tickets-plus/event-tickets-plus.php' => [
					'label'       => '<a href="https://theeventscalendar.com/products/wordpress-event-tickets/">Event Tickets Plus</a>',
					'min_version' => '5.0.0',
				],
			]
		);

		Dependency_Check::get_instance()->set_message(
			__( 'LearnDash LMS - The Events Calendar Integration Add-on requires the following plugin(s) to be active:', 'learndash-tec' )
		);
	}

	/**
	 * Includes all necessary PHP files.
	 *
	 * This function is responsible for including all necessary PHP files.
	 *
	 * @since 1.0
	 * @deprecated 1.0.2
	 */
	public function includes() {
		_deprecated_function( __METHOD__, '1.0.2' );

		include LEARNDASH_TEC_PLUGIN_PATH . '/includes/class-assets.php';
		include LEARNDASH_TEC_PLUGIN_PATH . '/includes/class-integration.php';
	}
}
