<?php

/**
 * Class H5PPostModel Post Model
 *
 * @package LearnPress/Classes
 * @version 1.0.0
 * @since 4.0.4
 */

namespace LearnPress\H5P\Model;

use LearnPress\Models\PostModel;
use LP_Post_Type_Filter;

class H5PPostModel extends PostModel {
	/**
	 * @var string Post Type
	 */
	public $post_type = LP_H5P_CPT;

	/**
	 * Const meta key
	 */
	const META_KEY_H5P_INTERACT  = '_lp_h5p_interact';
	const META_KEY_PASSING_GRADE = '_lp_passing_grade';

	/**
	 * Get post lp h5p by ID
	 *
	 * @param int $post_id
	 * @param bool $check_cache
	 *
	 * @return false|static
	 */
	public static function find( int $post_id, bool $check_cache = false ) {
		$filter_post            = new LP_Post_Type_Filter();
		$filter_post->ID        = $post_id;
		$filter_post->post_type = LP_H5P_CPT;

		return self::get_item_model_from_db( $filter_post );
	}

	/**
	 * Get the H5P assigned to LP H5P item.
	 *
	 * @return int
	 */
	public function get_h5p_interact(): int {
		return (int) $this->get_meta_value_by_key( self::META_KEY_H5P_INTERACT, 0 );
	}

	/**
	 * Get max mark of assignment
	 *
	 * @return float
	 */
	public function get_passing_grade(): float {
		return (float) $this->get_meta_value_by_key( self::META_KEY_PASSING_GRADE, 50 );
	}
}
