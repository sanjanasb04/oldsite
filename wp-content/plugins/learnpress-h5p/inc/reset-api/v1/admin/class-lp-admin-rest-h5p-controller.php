<?php

defined( 'ABSPATH' ) || exit();

class LPResetAdminH5pController {

	private static $instance;
	/**
	 * @var string
	 */
	public $namespace = 'lp/h5p/v1';

	/**
	 * @var string
	 */
	public $rest_base = '';

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'get-h5p-item',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'get_h5p_item' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
	}

	/**
	 * Check permission for REST API endpoint
	 *
	 * @return bool
	 */
	public function permission_callback() {
		return current_user_can( 'edit_posts' );
	}

	public function get_h5p_item( WP_REST_Request $request ) {
		$response = new LP_REST_Response();

		try {
			$lp_db         = LP_Database::getInstance();
			$wpdb          = $lp_db->wpdb;
			$params        = $request->get_params();
			$search_string = LP_Helper::sanitize_params_submitted( $params['search'] ?? '' );
			$current_ids   = (int) LP_Helper::sanitize_params_submitted( $params['current_ids'] ?? '' );
			$number        = LP_Helper::sanitize_params_submitted( $params['number'] ?? 20 );

			$params                   = $request->get_params();
			$response->data->h5p      = array();
			$filter                   = new LP_Post_Type_Filter();
			$filter->fields[]         = 'hc.title AS title';
			$filter->fields[]         = 'hc.id AS id';
			$filter->collection       = $wpdb->prefix . 'h5p_contents';
			$filter->collection_alias = 'hc';
			$filter->where[]          = $wpdb->prepare( 'AND hc.title LIKE %s', "%{$search_string}" );
			$filter->join[]           = "LEFT JOIN {$wpdb->prefix}h5p_libraries hl ON hl.id = hc.library_id";
			$filter->join[]           = "LEFT JOIN {$wpdb->prefix}h5p_contents_tags ct ON ct.content_id = hc.id";
			$filter->join[]           = "LEFT JOIN {$wpdb->prefix}h5p_tags t ON ct.tag_id = t.id";
			$filter->join[]           = "LEFT JOIN {$wpdb->prefix}h5p_contents_tags ct2 ON ct2.content_id = hc.id";
			$filter->group_by         = 'hc.id';
			$filter->order_by         = 'hc.updated_at';
			$filter->limit            = '20';
			$filter                   = apply_filters( 'lp/h5p/query/filter/get_h5p_item', $filter );

			// Get only users selected.
			$h5ps_selected = [];
			if ( ! empty( $current_ids ) ) {
				$filter_current_ids          = clone $filter;
				$filter_current_ids->where[] = $wpdb->prepare( 'AND hc.id IN ( %s )', $current_ids );
				$h5ps_selected               = $lp_db->execute( $filter_current_ids );

				$filter->where[] = $wpdb->prepare( 'AND hc.id NOT IN ( %s )', $current_ids );
			}

			$h5ps_data = $lp_db->execute( $filter );
			$h5ps_data = array_merge( $h5ps_data, $h5ps_selected );
			$h5ps_data = array_map(
				function ( $h5p ) {
					return [
						'title' => $h5p->title,
						'ID'    => $h5p->id,
					];
				},
				$h5ps_data
			);

			$response->data->h5p = $h5ps_data;
			$response->status    = 'success';
		} catch ( Exception $e ) {
			$response->message = $e->getMessage();
		}

		return $response;
	}
}

LPResetAdminH5pController::instance();
