<?php
/**
 * View: Virtual Events Metabox LearnDash options Show To section.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/learndash/tec/admin-views/compatibility/event-tickets/show-to.php
 *
 * See more documentation about our views templating system.
 *
 * @since 1.0.4
 * @version 1.0.4
 *
 * @link http://evnt.is/1aiy
 *
 * @var string                $metabox_id  The current metabox id.
 * @var WP_Post               $post        The current event post object, as decorated by the `tribe_get_event` function.
 * @var array<string, string> $meta_values Values of the options added in virtual events show embed to metabox.
 *
 * @see tribe_get_event() For the format of the event object.
 *
 * @package LearnDash\The_Events_Calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<li>
	<label for="<?php echo esc_attr( "{$metabox_id}-show-to-learndash-students" ); ?>">
		<input
			id="<?php echo esc_attr( "{$metabox_id}-show-to-learndash-students" ); ?>"
			name="<?php echo esc_attr( "{$metabox_id}[show-embed-to][]" ); ?>"
			type="checkbox"
			value="<?php echo esc_attr( $meta_values['value_show_embed_to_learndash_students'] ); ?>"
			<?php
			// @phpstan-ignore-next-line -- Valid custom property in TEC event post.
			checked( in_array( $meta_values['value_show_embed_to_learndash_students'], $post->virtual_show_embed_to, true ) );
			?>
		/>
		<?php
		echo esc_html(
			_x(
				'LearnDash Enrolled Students',
				'Show virtual content to LearnDash enrolled students.',
				'learndash-tec'
			)
		);
		?>
	</label>
	<ul class="learndash-options">
		<li class="option">
			<label for="learndash-option-any">
				<input
					type="radio"
					id="learndash-option-any"
					name="<?php echo esc_attr( "{$metabox_id}[show-embed-to-learndash-students]" ); ?>"
					value="<?php echo esc_attr( $meta_values['value_show_embed_to_learndash_students_any'] ); ?>"
					<?php
					// @phpstan-ignore-next-line -- Valid custom property in TEC event post.
					checked( $meta_values['value_show_embed_to_learndash_students_any'], $post->virtual_show_embed_to_learndash_students );
					?>
				>
				<?php
				printf(
					// Translators: %1$s and %2$s are replaced with the custom labels for courses and groups.
					esc_html__( 'Any %1$s or %2$s. (Default)', 'learndash-tec' ),
					esc_html( learndash_get_custom_label_lower( 'course' ) ),
					esc_html( learndash_get_custom_label_lower( 'group' ) )
				);
				?>
			</label>
		</li>
		<li class="option">
			<label for="learndash-option-associated">
				<input
					type="radio"
					id="learndash-option-associated"
					name="<?php echo esc_attr( "{$metabox_id}[show-embed-to-learndash-students]" ); ?>"
					value="<?php echo esc_attr( $meta_values['value_show_embed_to_learndash_students_associated'] ); ?>"
					<?php
					// @phpstan-ignore-next-line -- Valid custom property in TEC event post.
					checked( $meta_values['value_show_embed_to_learndash_students_associated'], $post->virtual_show_embed_to_learndash_students );
					?>
				>
				<?php
				echo wp_kses(
					sprintf(
						// Translators: %1$s and %2$s are replaced with the custom labels for courses and groups, and %3$s is a link anchor.
						__(
							'Associated %1$s and/or %2$s with this event. <a href="%3$s">Click here</a> to associate them.',
							'learndash-tec'
						),
						learndash_get_custom_label_lower( 'courses' ),
						learndash_get_custom_label_lower( 'groups' ),
						'#learndash-tec'
					),
					[ 'a' => [ 'href' => [] ] ]
				);
				?>
			</label>
		</li>
	</ul>
</li>
