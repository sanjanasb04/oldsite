<?php

use LearnPress\H5P\Model\H5PPostModel;
use LearnPress\H5P\Model\UserH5PModel;
use LearnPress\Models\CourseModel;
use LearnPress\Models\UserModel;

defined( 'ABSPATH' ) || exit();

$course_id   = get_the_ID();
$current_h5p = LP_Global::course_item();
$h5p_id      = $current_h5p ? $current_h5p->get_id() : 0;

if ( empty( $course_id ) || empty( $current_h5p ) ) {
	return;
}

$h5pPostModel = H5PPostModel::find( $h5p_id, true );
if ( ! $h5pPostModel instanceof H5PPostModel ) {
	return;
}

$courseModel = CourseModel::find( $course_id, true );
$userModel   = UserModel::find( get_current_user_id(), true );
if ( ! $courseModel instanceof CourseModel
	|| ! $userModel instanceof UserModel ) {
	return;
}

$userH5PModel = UserH5PModel::find( $userModel->get_id(), $courseModel->get_id(), $current_h5p->get_id(), true );
if ( ! $userH5PModel instanceof UserH5PModel ) {
	return;
}

$user_item_id = $userH5PModel->get_user_item_id();
$score        = $userH5PModel->get_meta_value_from_key( 'score', 0 );
$max_score    = $userH5PModel->get_meta_value_from_key( 'max_score', 0 );

if ( ! $max_score ) {
	$h5p_id_assigned = $h5pPostModel->get_h5p_interact();
	if ( $h5p_id_assigned ) {
		$h5p_plugin_admin_class = 'H5P_Plugin_Admin';
		if ( class_exists( $h5p_plugin_admin_class ) ) {
			$h5p_plugin = $h5p_plugin_admin_class::get_instance();
			$h5p_result = $h5p_plugin->get_results( $h5p_id_assigned, $userModel->get_id(), 0, 1, 1 );
			if ( isset( $h5p_result[0] ) ) {
				$score     = $h5p_result[0]->score;
				$max_score = $h5p_result[0]->max_score;
			}
		}
	}
}

$graduation = $userH5PModel->get_graduation();

$result_grade = array(
	'user_mark' => floatval( $score ),
	'mark'      => floatval( $max_score ),
	'grade'     => $graduation,
	'result'    => $max_score ? ( $score / $max_score ) * 100 : 0,
);
?>

<div class="h5p-result <?php echo esc_attr( $result_grade['grade'] ); ?>">

	<h5><?php esc_html_e( 'Congratulation, you already completed this!', 'learnpress-h5p' ); ?></h5>

	<div class="result-grade">
		<span class="result-achieved"><?php echo esc_html( $result_grade['user_mark'] ); ?></span>
		<span class="result-require"><?php echo esc_html( $result_grade['mark'] ); ?></span>
		<p class="result-message">
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: graduation result */
					__( 'Your result is <strong>%s</strong>', 'learnpress-h5p' ),
					empty( $result_grade['grade'] )
						? esc_html__( 'Ungraded', 'learnpress-h5p' )
						: esc_html( $userH5PModel->get_string_i18n( $result_grade['grade'] ) )
				)
			);
			?>
		</p>
	</div>
</div>
