<?php

/**
 * Class UserH5PModel
 *
 * @package LearnPress/Classes
 * @version 1.0.0
 * @since 4.0.4
 */

namespace LearnPress\H5P\Model;

use DateTime;
use Exception;
use LearnPress\Models\CourseModel;
use LearnPress\Models\CoursePostModel;
use LearnPress\Models\UserItemMeta\UserItemMetaModel;
use LearnPress\Models\UserItems\UserCourseModel;
use LearnPress\Models\UserItems\UserItemModel;
use LearnPress\Models\UserModel;
use LP_Assigment_DB;
use LP_Cache;
use LP_Datetime;
use LP_Helper;
use LP_Quiz;
use LP_User;
use LP_User_Item_Meta_DB;
use LP_User_Item_Meta_Filter;
use LP_User_Items_DB;
use LP_User_Items_Filter;
use LP_WP_Filesystem;
use stdClass;
use Throwable;
use WP_Error;

class UserH5PModel extends UserItemModel {
	/**
	 * Item type lp_h5p
	 *
	 * @var string Item type
	 */
	public $item_type = LP_H5P_CPT;
	/**
	 * Ref type Course
	 *
	 * @var string
	 */
	public $ref_type = LP_COURSE_CPT;

	/**
	 * Constant key meta
	 */
	const META_KEY_RETAKEN_COUNT   = '_lp_assignment_retaken';

	/**
	 * Constant status
	 */

	/**
	 * Get Course of assignment.
	 *
	 * @return false|CourseModel
	 */
	public function get_course_model() {
		return CourseModel::find( $this->ref_id, true );
	}

	/**
	 * Get H5PPostModel of user.
	 *
	 * @return false|H5PPostModel
	 */
	public function get_h5p_model() {
		return H5PPostModel::find( $this->item_id, true );
	}

	/**
	 * Get UserCourseModel.
	 *
	 * @return false|UserCourseModel
	 */
	public function get_user_course_model() {
		return UserCourseModel::find( $this->user_id, $this->ref_id, true );
	}

	/**
	 * Find Assignment Item by user_id, course_id, assignment_id.
	 *
	 * @param int $user_id
	 * @param int $course_id
	 * @param int $lp_h5p_id
	 * @param bool $check_cache
	 *
	 * @return false|UserItemModel|static
	 * @since 4.0.4
	 * @version 1.0.0
	 */
	public static function find( int $user_id, int $course_id, int $lp_h5p_id, bool $check_cache = false ) {
		$filter            = new LP_User_Items_Filter();
		$filter->user_id   = $user_id;
		$filter->item_id   = $lp_h5p_id;
		$filter->item_type = LP_H5P_CPT;
		$filter->ref_id    = $course_id;
		$filter->ref_type  = LP_COURSE_CPT;
		$key_cache         = "userH5PModel/find/{$user_id}/{$lp_h5p_id}/{$filter->item_type}/{$course_id}/{$filter->ref_type}";
		$lpUserH5PCache    = new LP_Cache();

		// Check cache
		if ( $check_cache ) {
			$userH5PModel = $lpUserH5PCache->get_cache( $key_cache );
			if ( $userH5PModel instanceof UserH5PModel ) {
				return $userH5PModel;
			}
		}

		$userH5PModel = static::get_user_item_model_from_db( $filter );
		// Set cache
		if ( $userH5PModel instanceof UserH5PModel ) {
			if ( ! $userH5PModel->meta_data instanceof stdClass ) {
				$userH5PModel->meta_data = new stdClass();
			}
			$lpUserH5PCache->set_cache( $key_cache, $userH5PModel );
		}

		return $userH5PModel;
	}

	/**
	 * Clean caches.
	 *
	 * @return void
	 */
	public function clean_caches() {
		parent::clean_caches();

		// Clear cache user item.
		$lp_cache  = new LP_Cache();
		$key_cache = "userH5PModel/find/{$this->user_id}/{$this->item_id}/{$this->item_type}/{$this->ref_id}/{$this->ref_type}";
		$lp_cache->clear( $key_cache );
	}
}
