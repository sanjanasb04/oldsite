<?php
/**
 * Functions for uninstall LearnDash LMS - The Events Calendar Integration
 *
 * @since 1.0.2
 *
 * @package LearnDash\The_Events_Calendar
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

/**
 * Fires on plugin uninstall.
 *
 * @since 1.0.2
 *
 * @return void
 */
do_action( 'learndash_tec_uninstall' );
