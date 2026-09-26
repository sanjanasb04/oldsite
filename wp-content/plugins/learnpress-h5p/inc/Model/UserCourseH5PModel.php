<?php

/**
 * Class UserCourseH5PModel
 *
 * @version 1.0.0
 * @since 4.0.7
 */

namespace LearnPress\H5P\Model;

use LearnPress\Models\UserItems\UserCourseModel;

class UserCourseH5PModel extends UserCourseModel {
	/**
	 * Evaluate course by H5P items completed.
	 * total completed h5p/ total hp5 in course.
	 *
	 * @return array
	 */
	public function evaluate_course_by_h5p_items_completed(): array {
		$result_data = $this->result_data_args();

		$count_items_completed = $this->count_items_completed();
		$courseModel           = $this->get_course_model();
		$total_h5p_items       = (int) $courseModel->count_items( LP_H5P_CPT );
		if ( $total_h5p_items === 0 ) {
			return $result_data;
		}
		$total_h5p_completed   = (int) $count_items_completed->lp_h5p_graduation_passed;
		$result                = ( $total_h5p_completed / $total_h5p_items ) * 100;
		$result_data['result'] = $result;
		$passing_condition     = $courseModel->get_passing_condition();
		if ( $result >= $passing_condition ) {
			$result_data['pass']  = 1;
			$result_data['grade'] = LP_COURSE_GRADUATION_PASSED;
		}

		return $result_data;
	}

	/**
	 * Evaluate course by H5P items completed.
	 * total passed h5p/ total hp5 in course.
	 *
	 * @return array
	 */
	public function evaluate_course_by_h5p_items_passed(): array {
		$result_data = $this->result_data_args();

		$count_items_completed = $this->count_items_completed();
		$courseModel           = $this->get_course_model();
		$total_h5p_items       = (int) $courseModel->count_items( LP_H5P_CPT );
		if ( $total_h5p_items === 0 ) {
			return $result_data;
		}
		$total_h5p_passed      = (int) $count_items_completed->lp_h5p_graduation_passed;
		$result                = ( $total_h5p_passed / $total_h5p_items ) * 100;
		$result_data['result'] = $result;
		$passing_condition     = $courseModel->get_passing_condition();
		if ( $result >= $passing_condition ) {
			$result_data['pass']  = 1;
			$result_data['grade'] = LP_COURSE_GRADUATION_PASSED;
		}

		return $result_data;
	}

	/**
	 * Evaluate course by H5P items completed.
	 * total passed h5p and quizzes / total hp5 and quizzes in course.
	 *
	 * @return array
	 */
	public function evaluate_course_by_h5p_quizzes_passed_items(): array {
		$result_data = $this->result_data_args();

		$count_items_completed = $this->count_items_completed();
		$courseModel           = $this->get_course_model();
		$total_quiz_h5p_items  = (int) $courseModel->count_items( LP_H5P_CPT ) + (int) $courseModel->count_items( LP_QUIZ_CPT );
		if ( $total_quiz_h5p_items === 0 ) {
			return $result_data;
		}
		$total_quiz_h5p_passed = (int) $count_items_completed->lp_h5p_graduation_passed + (int) $count_items_completed->lp_quiz_graduation_passed;
		$result                = ( $total_quiz_h5p_passed / $total_quiz_h5p_items ) * 100;
		$result_data['result'] = $result;
		$passing_condition     = $courseModel->get_passing_condition();
		if ( $result >= $passing_condition ) {
			$result_data['pass']  = 1;
			$result_data['grade'] = LP_COURSE_GRADUATION_PASSED;
		}

		return $result_data;
	}

	/**
	 * Default result data args.
	 *
	 * @return array
	 */
	public function result_data_args(): array {
		return array(
			'result' => 0,
			'grade'  => LP_COURSE_GRADUATION_FAILED,
			'status' => $this->get_status(),
			'pass'   => 0,
		);
	}
}
