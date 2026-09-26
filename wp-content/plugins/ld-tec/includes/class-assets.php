<?php
/**
 * Assets class file.
 *
 * @since 1.0
 *
 * @package LearnDash\The_Events_Calendar
 */

namespace LearnDash\The_Events_Calendar;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Assets class.
 *
 * @since 1.0
 */
class Assets {
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'deregister_admin_scripts' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ], 1 );
	}

	/**
	 * Deregister admin scripts.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function deregister_admin_scripts() {
		$screen = get_current_screen();

		if (
			isset( $screen->id ) && $screen->id === 'tribe_events'
			&& isset( $screen->base ) && $screen->base === 'post'
		) {
			wp_deregister_script( 'learndash-select2-jquery-script' );
			wp_deregister_style( 'learndash-select2-jquery-style' );
		}
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {
		$screen = get_current_screen();

		if (
			isset( $screen->id ) && $screen->id === 'tribe_events'
			&& isset( $screen->base ) && $screen->base === 'post'
		) {
			$suffix = learndash_min_asset();

			wp_register_script( 'learndash-tec-select2', LEARNDASH_TEC_PLUGIN_URL . 'lib/select2/select2.min.js', [ 'jquery' ], '4.0.13', true );
			wp_register_style( 'learndash-tec-select2', LEARNDASH_TEC_PLUGIN_URL . 'lib/select2/select2.min.css', [], '4.0.13', 'all' );

			wp_enqueue_script( 'learndash-tec-event', LEARNDASH_TEC_PLUGIN_URL . 'dist/js/admin/event' . $suffix . '.js', [ 'learndash-tec-select2' ], LEARNDASH_TEC_VERSION, true );
			wp_enqueue_style( 'learndash-tec-admin', LEARNDASH_TEC_PLUGIN_URL . 'dist/css/admin/styles' . $suffix . '.css', [ 'learndash-tec-select2' ], LEARNDASH_TEC_VERSION, 'all' );
		}
	}
}
