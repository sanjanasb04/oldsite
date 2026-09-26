<?php

use LearnPress\Helpers\Template;

defined( 'ABSPATH' ) || exit();

if ( empty( $course ) || empty( $user ) || empty( $h5p ) ) {
	return;
}

$h5p       = LP_Global::course_item();
$h5p_data  = $user->get_item_data( $h5p->get_id(), $course->get_id() );
$completed = $user->has_completed_item( $h5p->get_id(), $course->get_id() );

if ( $completed ) :
	?>
	<div class="learn-press-message success">
		<?php
		echo sprintf(
			'%s %s',
			esc_html__( 'You have completed this H5P at ', 'learnpress-h5p' ),
			$h5p_data->get_end_time()->format( LP_Datetime::I18N_FORMAT_HAS_TIME )
		)
		?>
	</div>
	<button class="lp-button completed" disabled>
		<i class="lp-icon-check"></i><?php esc_html_e( 'Completed', 'learnpress-h5p' ); ?>
	</button>
	<?php
else :
	$message_confirm_complete_item = sprintf(
		'%s "%s"?',
		__( 'Do you want to complete the H5P', 'learnpress-h5p' ),
		$h5p->get_title()
	);
	$data_send                     = [
		'course_id' => $course->get_id(),
		'lp_h5p_id' => $h5p->get_id(),
		'redirect'  => 1,
	]
	?>
	<form method="post" name="learn-press-form-complete-h5p"
			class="learn-press-form form-button"
			data-title="<?php echo esc_attr( __( 'Complete H5P', 'learnpress-h5p' ) ); ?>"
			data-confirm="<?php echo esc_attr( $message_confirm_complete_item ); ?>">

		<button class="lp-button button-complete-h5p lp-btn-complete-item"
				type="submit"
				id="complete_h5p_button"
				style="display: none;">
			<?php echo esc_html__( 'Complete', 'learnpress-h5p' ); ?>
		</button>
		<input type="hidden" name="lp-load-ajax" value="user_submit_h5p_when_complete">
		<input type="hidden" name="data" value="<?php echo esc_attr( Template::convert_data_to_json( $data_send ) ); ?>">
		<?php wp_nonce_field( 'wp_rest', 'nonce' ); ?>
	</form>
<?php endif; ?>
