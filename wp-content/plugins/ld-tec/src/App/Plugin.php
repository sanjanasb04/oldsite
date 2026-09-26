<?php
/**
 * Plugin service provider class file.
 *
 * @since 1.0.2
 *
 * @package LearnDash\The_Events_Calendar
 */

namespace LearnDash\The_Events_Calendar;

use StellarWP\Learndash\lucatume\DI52\ServiceProvider;
use StellarWP\Learndash\lucatume\DI52\ContainerException;

/**
 * Plugin service provider class.
 *
 * @since 1.0.2
 */
class Plugin extends ServiceProvider {
	/**
	 * Register service provider.
	 *
	 * @since 1.0.2
	 *
	 * @throws ContainerException If the service provider is not registered.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->container->register( Admin\Provider::class );
	}
}
