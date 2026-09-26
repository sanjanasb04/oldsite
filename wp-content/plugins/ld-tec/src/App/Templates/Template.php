<?php
/**
 * Frontend Template retrieval class.
 *
 * @since 1.0.3
 *
 * @package LearnDash\The_Events_Calendar
 */

namespace LearnDash\The_Events_Calendar\Templates;

use LearnDash\The_Events_Calendar\StellarWP\Templates\Template as StellarWP_Template;

/**
 * Frontend Template retrieval class.
 *
 * @since 1.0.3
 */
class Template extends StellarWP_Template {
	/**
	 * Base template for where to look for template.
	 *
	 * @since 1.0.3
	 *
	 * @var string[]
	 */
	protected array $template_base_path = [ LEARNDASH_TEC_DIR . 'src/views' ];

	/**
	 * Allow changing if class will extract data from the local context.
	 *
	 * @since 1.0.3
	 *
	 * @var boolean
	 */
	protected bool $template_context_extract = true;

	/**
	 * Should we use a lookup into the list of folders to try to find the file.
	 *
	 * @since 1.0.3
	 *
	 * @var boolean
	 */
	protected bool $template_folder_lookup = true;
}
