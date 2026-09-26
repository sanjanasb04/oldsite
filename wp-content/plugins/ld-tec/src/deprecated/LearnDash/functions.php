<?php
/**
 * File for deprecated functions.
 *
 * @since 1.0.2
 *
 * @package LearnDash\The_Events_Calendar\Deprecated
 */

namespace LearnDash;

/**
 * The main function for returning The_Events_Calendar instance.
 *
 * @since 1.0
 * @deprecated 1.0.2
 *
 * @return The_Events_Calendar The one and only true The_Events_Calendar instance.
 */
function the_events_calendar() {
	_deprecated_function( __FUNCTION__, '1.0.2' );

	return The_Events_Calendar::instance();
}
