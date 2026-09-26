<?php

namespace LearnPress\H5P\Ajax;

use Exception;
use LearnPress\Ajax\AbstractAjax;
use LearnPress\H5P\Model\H5PPostModel;
use LearnPress\H5P\Model\UserH5PModel;
use LearnPress\Models\CourseModel;
use LearnPress\Models\CourseSectionItemModel;
use LearnPress\Models\CourseSectionModel;
use LearnPress\Models\LessonPostModel;
use LearnPress\Models\PostModel;
use LearnPress\Models\UserItems\UserCourseModel;
use LearnPress\Models\UserItems\UserItemModel;
use LearnPress\Models\UserModel;
use LP_Datetime;
use LP_Helper;
use LP_Request;
use LP_REST_Response;
use LP_Section_DB;
use LP_Section_Items_DB;
use LP_Section_Items_Filter;
use stdClass;
use Throwable;

/**
 * class EditCurriculumAjax
 *
 * This class handles the AJAX request to edit the curriculum of a course.
 *
 * @since 4.0.7
 * @version 1.0.0
 */
class H5PAjax extends AbstractAjax {
	public function user_submit_h5p_when_complete() {
		$response = new LP_REST_Response();

		try {
			$params = wp_unslash( $_REQUEST['data'] ?? '' );
			if ( empty( $params ) ) {
				throw new Exception( 'Error: params invalid!' );
			}

			$params = LP_Helper::json_decode( $params, true );

			$lp_h5p_id = (int) $params['lp_h5p_id'] ?? 0;
			$course_id = (int) $params['course_id'] ?? 0;

			$userModel = UserModel::find( get_current_user_id(), true );
			if ( ! $userModel ) {
				throw new Exception( esc_html__( 'User is invalid!', 'learnpress-h5p' ) );
			}

			$courseModel = CourseModel::find( $course_id, true );
			if ( ! $courseModel ) {
				throw new Exception( esc_html__( 'Course is invalid!', 'learnpress-h5p' ) );
			}

			$h5PPostModel = H5PPostModel::find( $lp_h5p_id, true );
			if ( ! $h5PPostModel ) {
				throw new Exception( esc_html__( 'H5P is invalid!', 'learnpress-h5p' ) );
			}

			$userCourseModel = UserCourseModel::find( $userModel->get_id(), $courseModel->get_id(), true );
			if ( ! $userCourseModel || ! $userCourseModel->has_enrolled() ) {
				throw new Exception( esc_html__( 'You have not enrolled in this course!', 'learnpress-h5p' ) );
			}

			if ( $userCourseModel->has_finished() ) {
				throw new Exception(
					esc_html__(
						'You have finished the course. Please enroll in this course again to start the H5P activity.',
						'clearness-h5p'
					)
				);
			}

			$h5p_id_assigned = $h5PPostModel->get_h5p_interact();
			if ( ! $h5p_id_assigned ) {
				throw new Exception( esc_html__( 'H5P is not assigned to this item!', 'learnpress-h5p' ) );
			}

			$h5p_plugin = \H5P_Plugin_Admin::get_instance();
			$h5p_result = $h5p_plugin->get_results( $h5p_id_assigned, $userModel->get_id(), 0, 1, 1 );
			if ( ! isset( $h5p_result[0] ) || ! $h5p_result[0] instanceof stdClass ) {
				throw new Exception( esc_html__( 'H5P not result', 'learnpress-h5p' ) );
			}

			$h5p_result     = $h5p_result[0];
			$score          = $h5p_result->score;
			$max_score      = $h5p_result->max_score;
			$passing_grade  = $h5PPostModel->get_passing_grade();
			$result_percent = floatval( $score / $max_score ) * 100;
			if ( $result_percent >= $passing_grade ) {
				$graduation = UserItemModel::GRADUATION_PASSED;
			} else {
				$graduation = UserItemModel::GRADUATION_FAILED;
			}

			$userH5PModel = UserH5PModel::find( $userModel->get_id(), $courseModel->get_id(), $h5PPostModel->get_id(), true );
			if ( ! $userH5PModel ) {
				// Create new
			} else {
				// Update
				$userH5PModel->end_time   = gmdate( LP_Datetime::$format, time() );
				$userH5PModel->graduation = $graduation;
				$userH5PModel->status     = UserItemModel::STATUS_COMPLETED;
				$userH5PModel->set_meta_value_for_key( 'score', $score );
				$userH5PModel->set_meta_value_for_key( 'max_score', $max_score );

				$userH5PModel->save();
				$response->status  = 'success';
				$response->message = esc_html__( 'Congratulation! You completed this!', 'learnpress-h5p' );
			}
		} catch ( Throwable $e ) {
			$response->message = $e->getMessage();
		}

		learn_press_set_message(
			[
				'status'  => $response->status,
				'content' => $response->message,
			]
		);

		if ( isset( $params['redirect'] ) ) {
			wp_safe_redirect( LP_Helper::getUrlCurrent() );
			exit;
		} else {
			wp_send_json( $response );
		}
	}
}
