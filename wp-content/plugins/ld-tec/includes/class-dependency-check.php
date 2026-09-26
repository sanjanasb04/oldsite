<?php
/**
 * Legacy dependency checker class file.
 *
 * @since 1.0
 * @deprecated 1.0.2
 *
 * @package LearnDash\The_Events_Calendar
 */

namespace LearnDash\The_Events_Calendar;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

_deprecated_file(
	__FILE__,
	'1.0.2',
	esc_html(
		LEARNDASH_TEC_PLUGIN_PATH . 'src/App/Dependency_Checker.php'
	)
);

/**
 * Set up LearnDash Dependency Check.
 *
 * @since 1.0
 * @deprecated 1.0.2
 */
class Dependency_Check extends Dependency_Checker {}
