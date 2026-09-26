<?php
/**
 * Integration class file.
 *
 * @since 1.0
 *
 * @package LearnDash\The_Events_Calendar
 */

namespace LearnDash\The_Events_Calendar;

use TEC\Tickets\Commerce\Attendee;
use TEC\Tickets\Commerce\Order;
use TEC\Tickets\Commerce\Status\Status_Interface as Status;
use Tribe__Template;
use Tribe__Utils__Array as Arr;
use Tribe\Events\Virtual\Metabox as VirtualEventsMetabox;
use WP_Error;
use WP_Post;
use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Integration class.
 *
 * @since 1.0
 */
class Integration {
	/**
	 * Meta key and values pair for show embed to LearnDash students field.
	 *
	 * @since 1.0
	 *
	 * @var array<string, string>
	 */
	public static $meta_values = [
		'value_show_embed_to_learndash_students'     => 'learndash-students',
		'value_show_embed_to_learndash_students_any' => 'any',
		'value_show_embed_to_learndash_students_associated' => 'associated',
	];

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		// Metabox.
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_meta_boxes' ], 10, 3 );

		// LearnDash enrollment/unenrollment.
		add_action( 'tec_tickets_commerce_order_status_transition', [ $this, 'order_status_transition' ], 10, 3 );
		add_action( 'pre_post_update', [ $this, 'attendee_before_update' ], 10, 2 );
		add_action( 'before_delete_post', [ $this, 'attendee_before_delete' ], 10, 2 );

		// Virtual events hook.
		add_action( 'tribe_template_entry_point:events-virtual/admin-views/virtual-metabox/container/compatibility/event-tickets/show-to:before_show_to_list_end', [ $this, 'render_show_to_options' ], 20, 3 );

		// Events CalendarPro hook.
		add_action( 'tribe_template_entry_point:events-pro/admin-views/virtual-metabox/container/compatibility/event-tickets/show-to:before_show_to_list_end', [ $this, 'render_show_to_options' ], 20, 3 );

		add_action( 'tribe_events_virtual_update_post_meta', [ $this, 'save_show_to_options_data' ], 10, 2 );
		add_filter( 'tribe_post_type_events_properties', [ $this, 'filter_event_properties' ], 30, 2 );
		add_filter( 'tribe_events_virtual_show_virtual_content', [ $this, 'show_virtual_content' ], 20, 2 );
	}

	/**
	 * Gets associated LearnDash course_ids from an event.
	 *
	 * @since 1.0
	 *
	 * @param int $event_id Event ID.
	 *
	 * @return int[] List of course_ids.
	 */
	public function get_associated_courses( $event_id ) {
		$courses = get_post_meta( $event_id, '_learndash_tec_courses', true );

		if ( empty( $courses ) ) {
			$courses = [];
		}

		return (array) $courses;
	}

	/**
	 * Gets associated LearnDash group_ids from an event.
	 *
	 * @since 1.0
	 *
	 * @param int $event_id Event ID.
	 *
	 * @return int[] List of group_ids
	 */
	public function get_associated_groups( $event_id ) {
		$groups = get_post_meta( $event_id, '_learndash_tec_groups', true );

		if ( empty( $groups ) ) {
			$groups = [];
		}

		return (array) $groups;
	}

	/**
	 * Gets associated LearnDash course_ids and group_ids from an event.
	 *
	 * @since 1.0
	 *
	 * @param int $event_id Event ID.
	 *
	 * @return array List of course_ids and group_ids
	 */
	public function get_associated_objects( $event_id ) {
		return array_merge( $this->get_associated_courses( $event_id ), $this->get_associated_groups( $event_id ) );
	}

	/**
	 * Toggles user LearnDash course/group access.
	 *
	 * @since 1.0
	 *
	 * @param int    $user_id   User ID.
	 * @param int    $object_id LearnDash course or group ID.
	 * @param bool   $remove    Whether to enroll or unenroll.
	 * @param string $order_id  Order ID.
	 *
	 * @return void
	 */
	public function toggle_user_access( $user_id, $object_id, $remove = false, $order_id = null ) {
		if ( ! empty( $order_id ) ) {
			$post_type = get_post_type( $object_id );

			if ( ! $remove ) {
				$this->increment_object_access_counter( $object_id, $user_id, $order_id );
			} else {
				$this->decrement_object_access_counter( $object_id, $user_id, $order_id );
			}

			$access_counter = $this->get_objects_access_counter( $user_id );

			if ( 'sfwd-courses' === $post_type ) {
				if ( ! $remove ) {
					ld_update_course_access( $user_id, $object_id );
				} elseif ( ! isset( $access_counter[ $object_id ] ) || empty( $access_counter[ $object_id ] ) ) {
						ld_update_course_access( $user_id, $object_id, $remove );
				}
			} elseif ( 'groups' === $post_type ) {
				if ( ! $remove ) {
					ld_update_group_access( $user_id, $object_id );
				} elseif ( ! isset( $access_counter[ $object_id ] ) || empty( $access_counter[ $object_id ] ) ) {
						ld_update_group_access( $user_id, $object_id, $remove );
				}
			}
		}
	}

	/**
	 * Toggles user LearnDash course/group access by an event ID.
	 *
	 * @since 1.0
	 *
	 * @param int     $user_id   User ID.
	 * @param int     $event_id  Event ID.
	 * @param boolean $remove    Whether to enroll or unenroll.
	 * @param int     $order_id  Order ID.
	 * @param string  $ticket_id Ticket ID.
	 *
	 * @return void
	 */
	public function toggle_user_access_by_event_id( $user_id, $event_id, $remove, $order_id, $ticket_id ) {
		$associated_objects = $this->get_associated_objects( $event_id );

		foreach ( $associated_objects as $object_id ) {
			$this->toggle_user_access( $user_id, $object_id, $remove, $order_id . '-' . $ticket_id );
		}
	}

	/**
	 * Adds enrolled object record to a user.
	 *
	 * @since 1.0
	 *
	 * @param int    $object_id ID of an object.
	 * @param int    $user_id   ID of a user.
	 * @param string $order_id  ID of an order.
	 *
	 * @return array<int, array<string>> Course access counter array.
	 */
	public function increment_object_access_counter( $object_id, $user_id, $order_id ) {
		$objects = $this->get_objects_access_counter( $user_id );

		if ( isset( $objects[ $object_id ] ) && ! is_array( $objects[ $object_id ] ) ) {
			$objects[ $object_id ] = [];
		}

		if ( ! isset( $objects[ $object_id ] ) || array_search( $order_id, $objects[ $object_id ] ) === false ) {
			// Add order ID to object access counter.
			$objects[ $object_id ][] = $order_id;
		}

		update_user_meta( $user_id, '_learndash_woocommerce_enrolled_objects_access_counter', $objects );

		return $objects;
	}

	/**
	 * Deletes enrolled object record from a user.
	 *
	 * @since 1.0
	 *
	 * @param int    $object_id ID of an object.
	 * @param int    $user_id   ID of a user.
	 * @param string $order_id  ID of an order.
	 *
	 * @return array<int, array<string>> Course access counter array.
	 */
	public function decrement_object_access_counter( $object_id, $user_id, $order_id ) {
		$objects = $this->get_objects_access_counter( $user_id );

		if ( isset( $objects[ $object_id ] ) && ! is_array( $objects[ $object_id ] ) ) {
			$objects[ $object_id ] = [];
		}

		if ( isset( $objects[ $object_id ] ) ) {
			$keys = array_keys( $objects[ $object_id ], $order_id );
			if ( is_array( $keys ) ) {
				foreach ( $keys as $key ) {
					unset( $objects[ $object_id ][ $key ] );
				}
			}
		}

		update_user_meta( $user_id, '_learndash_woocommerce_enrolled_objects_access_counter', $objects );

		return $objects;
	}

	/**
	 * Resets object access counter.
	 *
	 * @since 1.0
	 *
	 * @param int $object_id Course ID.
	 * @param int $user_id   User ID.
	 *
	 * @return array<int, array<string>> Course access counter array.
	 */
	public function reset_object_access_counter( $object_id, $user_id ) {
		$objects = $this->get_objects_access_counter( $user_id );

		if ( isset( $objects[ $object_id ] ) ) {
			unset( $objects[ $object_id ] );
		}

		update_user_meta( $user_id, '_learndash_woocommerce_enrolled_objects_access_counter', $objects );

		return $objects;
	}

	/**
	 * Gets user enrolled object access counter.
	 *
	 * @since 1.0
	 *
	 * @param int $user_id ID of a user.
	 *
	 * @return array<int, array<string>> Course access counter array.
	 */
	public function get_objects_access_counter( $user_id ) {
		$objects = get_user_meta( $user_id, '_learndash_woocommerce_enrolled_objects_access_counter', true );

		if ( ! empty( $objects ) ) {
			/**
			 * Unserialized objects.
			 *
			 * @var array<int, array<string>> $objects
			 */
			$objects = maybe_unserialize( $objects );
		} else {
			$objects = [];
		}

		return $objects;
	}

	/**
	 * Registers meta box to tribe_events post_type.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box( 'learndash-tec', __( 'LearnDash Integration', 'learndash-tec' ), [ $this, 'output_learndash_tec_meta_box' ], 'tribe_events', 'normal', 'low' );
	}

	/**
	 * Outputs meta box content.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function output_learndash_tec_meta_box() {
		$event_id = get_the_ID();

		wp_nonce_field( 'learndash_tec_save_meta_box', 'learndash_tec_nonce' );

		$this->output_event_fields( $event_id );
	}

	/**
	 * Outputs meta box fields.
	 *
	 * @since 1.0
	 *
	 * @param int $event_id Event ID.
	 *
	 * @return void
	 */
	public function output_event_fields( $event_id ) {
		$selected_courses = $this->get_associated_courses( $event_id );
		if ( ! is_array( $selected_courses ) || empty( $selected_courses ) ) {
			$selected_courses = [];
		}

		$courses = get_posts(
			[
				'post_type'      => learndash_get_post_type_slug( 'course' ),
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);

		$courses = array_map(
			function ( $course ) {
				return [
					'value' => $course->ID,
					'label' => $course->post_title,
				];
			},
			$courses
		);

		$selected_groups = $this->get_associated_groups( $event_id );
		if ( ! is_array( $selected_groups ) || empty( $selected_groups ) ) {
			$selected_groups = [];
		}

		$groups = get_posts(
			[
				'post_type'      => learndash_get_post_type_slug( 'group' ),
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);

		$groups = array_map(
			function ( $group ) {
				return [
					'value' => $group->ID,
					'label' => $group->post_title,
				];
			},
			$groups
		);

		$fields = [
			[
				'id'       => 'learndash_tec_courses',
				'name'     => 'learndash_tec_courses[]',
				'label'    => __( 'Course(s)', 'learndash-tec' ),
				'class'    => 'select2',
				'options'  => $courses,
				'selected' => $selected_courses,
			],
			[
				'id'       => 'learndash_tec_groups',
				'name'     => 'learndash_tec_groups[]',
				'label'    => __( 'Group(s)', 'learndash-tec' ),
				'class'    => 'select2',
				'options'  => $groups,
				'selected' => $selected_groups,
			],
		];
		?>

		<table id="learndash_integration" class="eventtable">
			<tbody>
			<tr>
				<td class="tribe_sectionheader" colspan="2">
					<h4><?php esc_html_e( 'LearnDash Integration', 'learndash-tec' ); ?></h4>
				</td>
			</tr>
			<?php foreach ( $fields as $field ) : ?>
				<tr class="learndash-selector-wrapper">
					<td><label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label></td>
					<td>
						<?php $this->select_field( $field ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php
	}

	/**
	 * Outputs select field.
	 *
	 * @since 1.0
	 *
	 * @param array<string, mixed> $args Field args.
	 *
	 * @return void
	 */
	public function select_field( $args ) {
		?>
		<select name="<?php echo esc_attr( $args['name'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" class="<?php echo esc_attr( $args['class'] ); ?>" multiple="multiple">
			<?php foreach ( $args['options'] as $option ) : ?>
				<option value="<?php echo esc_attr( $option['value'] ); ?>" <?php echo in_array( $option['value'], $args['selected'] ) ? 'selected="selected"' : ''; ?>><?php echo esc_html( $option['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Saves meta box fields value to database.
	 *
	 * @since 1.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated.
	 *
	 * @return void
	 */
	public function save_meta_boxes( $post_id, $post, $update ) {
		if ( $post->post_type !== 'tribe_events' ) {
			return;
		}

		if ( isset( $_POST['learndash_tec_nonce'] ) && ! wp_verify_nonce( $_POST['learndash_tec_nonce'], 'learndash_tec_save_meta_box' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_tribe_events', $post_id ) ) {
			return;
		}

		if ( tribe_context()->is( 'bulk_edit' ) ) {
			return;
		}

		if ( tribe_context()->is( 'inline_save' ) ) {
			return;
		}

		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$courses = isset( $_POST['learndash_tec_courses'] )
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Input is sanitized after this definition.
			? wp_unslash( $_POST['learndash_tec_courses'] )
			: [];

		array_walk_recursive(
			$courses,
			function ( &$value ) {
				if ( ! is_array( $value ) ) {
					$value = absint( $value );
				}
			}
		);

		$groups = isset( $_POST['learndash_tec_groups'] )
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Input is sanitized after this definition.
			? wp_unslash( $_POST['learndash_tec_groups'] )
			: [];

		array_walk_recursive(
			$groups,
			function ( &$value ) {
				if ( ! is_array( $value ) ) {
					$value = absint( $value );
				}
			}
		);

		update_post_meta( $post_id, '_learndash_tec_courses', $courses );
		update_post_meta( $post_id, '_learndash_tec_groups', $groups );
	}

	/**
	 * Handles LearnDash course/group access update when order status transition happens.
	 *
	 * @since 1.0
	 *
	 * @param Status  $new_status New status object.
	 * @param Status  $old_status Old Status object.
	 * @param WP_Post $order      Order object.
	 *
	 * @return void
	 */
	public function order_status_transition( $new_status, $old_status, $order ) {
		// Necessary to pull complete TEC order data.
		$order = tec_tc_get_order( $order->ID );

		$order_meta = get_post_meta( $order->ID );
		$events     = Arr::get( $order_meta, Order::$events_in_order_meta_key );

		$items = $order->items ?? [];

		$attendees = tribe( Order::class )->get_attendees( $order );
		$attendees = ! empty( $attendees->attendees ) && is_array( $attendees->attendees ) ? $attendees->attendees : [];

		foreach ( $items as $item ) {
			foreach ( $attendees as $attendee ) {
				if ( empty( $attendee['holder_email'] ) ) {
					continue;
				}

				$user_args = [
					'name'  => $attendee['holder_name'],
					'email' => $attendee['holder_email'],
				];

				$user = $this->get_user( $user_args );

				foreach ( $events as $event_id ) {
					$associated_objects = $this->get_associated_objects( $event_id );

					foreach ( $associated_objects as $object_id ) {
						if ( $new_status::SLUG === 'completed' && $old_status::SLUG !== 'completed ' ) {
							$this->toggle_user_access( $user->ID, $object_id, false, $order->ID . '-' . $item['ticket_id'] );
						} elseif ( $new_status::SLUG !== 'completed' && $old_status::SLUG === 'completed' ) {
							$this->toggle_user_access( $user->ID, $object_id, true, $order->ID . '-' . $item['ticket_id'] );
						}
					}
				}
			}
		}
	}

	/**
	 * Handles LearnDash course/group access update when an attendee email is updated.
	 *
	 * @since 1.0
	 *
	 * @param int                  $post_id Post ID.
	 * @param array<string, mixed> $data    Post data.
	 *
	 * @return void
	 */
	public function attendee_before_update( $post_id, $data ) {
		if ( get_post_type( $post_id ) !== 'tec_tc_attendee' ) {
			return;
		}

		if ( isset( $_REQUEST['tribe_tickets'] ) && is_array( $_REQUEST['tribe_tickets'] ) ) {
			$old_attendee = tec_tc_get_attendee( $post_id );
			$event_id     = $old_attendee->event_id;
			$order_id     = $old_attendee->order_id;

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Used request data are sanitized after this definition.
			$tribe_tickets = wp_unslash( $_REQUEST['tribe_tickets'] );

			foreach ( $tribe_tickets as $id => $ticket ) {
				foreach ( $ticket['attendees'] as $attendee_id => $attendee_details ) {
					$name  = sanitize_text_field( $attendee_details['tribe-tickets-plus-ma-name'] );
					$email = sanitize_email( $attendee_details['tribe-tickets-plus-ma-email'] );

					$attendee = tec_tc_get_attendee( $attendee_id );

					if ( ! $attendee instanceof WP_Post ) {
						continue;
					}

					$attendee_object = tribe( Attendee::class );

					if ( ! $attendee_object instanceof Attendee ) {
						continue;
					}

					$ticket_id = $attendee_object->get_ticket_id( $attendee );

					$user_args = [
						'name'  => $name,
						'email' => $email,
					];

					if ( isset( $old_attendee->holder_email ) && $old_attendee->holder_email !== $email ) {
						$new_user = $this->get_user( $user_args );
						$old_user = get_user_by( 'email', $old_attendee->holder_email );

						$this->toggle_user_access_by_event_id( $old_user->ID, $event_id, true, $order_id, $ticket_id );

						$this->toggle_user_access_by_event_id( $new_user->ID, $event_id, false, $order_id, $ticket_id );
					}
				}
			}
		}
	}

	/**
	 * Handles LearnDash course/group access update when an attendee is deleted.
	 *
	 * @since 1.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 *
	 * @return void
	 */
	public function attendee_before_delete( $post_id, $post ) {
		if ( $post->post_type !== 'tec_tc_attendee' ) {
			return;
		}

		$attendee = tec_tc_get_attendee( $post_id );

		if ( ! $attendee instanceof WP_Post ) {
			return;
		}

		$user = get_user_by( 'email', $attendee->holder_email ); // @phpstan-ignore-line -- Valid property from customized attendee WP_Post properties.

		if ( $user ) {
			$event_id        = $attendee->event_id; // @phpstan-ignore-line -- Valid property from customized attendee WP_Post properties.
			$order_id        = $attendee->order_id; // @phpstan-ignore-line -- Valid property from customized attendee WP_Post properties.
			$attendee_object = tribe( Attendee::class );

			if ( ! $attendee_object instanceof Attendee ) {
				return;
			}

			$ticket_id = $attendee_object->get_ticket_id( $attendee );

			$this->toggle_user_access_by_event_id( $user->ID, $event_id, true, $order_id, $ticket_id );
		}
	}

	/**
	 * Renders LearnDash option for Show To virtual event meta box settings.
	 *
	 * @since 1.0
	 *
	 * @param string          $hook_name        Hook name of the template.
	 * @param string          $entry_point_name Entry point name of a template section.
	 * @param Tribe__Template $template         Tribe template object.
	 *
	 * @return void
	 */
	public function render_show_to_options( $hook_name, $entry_point_name, $template ) {
		$event = tribe_get_event();

		$args = [
			'disabled'    => false,
			'post'        => $event,
			'metabox_id'  => VirtualEventsMetabox::$id,
			'meta_values' => static::$meta_values,
		];

		add_filter(
			'tribe_template_path_list',
			function ( $folders ) {
				$folders['learndash_tec_theme'] = [
					'id'       => 'learndash_tec_theme',
					'priority' => 10,
					'path'     => trailingslashit( get_stylesheet_directory() ) . 'learndash/tec/',
				];

				$folders['learndash_tec_plugin'] = [
					'id'       => 'learndash_tec_plugin',
					'priority' => 100,
					'path'     => LEARNDASH_TEC_PLUGIN_PATH . 'templates/',
				];

				return $folders;
			},
			10,
			2
		);

		$template->template( 'events-virtual/admin-views/virtual-metabox/container/compatibility/event-tickets/show-to-learndash', $args, true );
	}

	/**
	 * Saves virtual events metadata.
	 *
	 * @since 1.0
	 *
	 * @param int                  $post_id Post ID.
	 * @param array<string, mixed> $data    Post data.
	 *
	 * @return void
	 */
	public function save_show_to_options_data( $post_id, $data ) {
		$show_embed_to = Arr::get( $data, 'show-embed-to', false );

		if (
			is_array( $show_embed_to )
			&& in_array( 'learndash-students', $show_embed_to )
		) {
			update_post_meta( $post_id, '_learndash_tec_show_embed_to_learndash_students', Arr::get( $data, 'show-embed-to-learndash-students', 'any' ) );
		} else {
			delete_post_meta( $post_id, '_learndash_tec_show_embed_to_learndash_students' );
		}   }

	/**
	 * Filters tribe events post type properties to add our custom properties.
	 *
	 * @since 1.0
	 *
	 * @param array<string, mixed> $props Properties.
	 * @param WP_Post              $post  Post object.
	 *
	 * @return array<string, mixed> Modified properties.
	 */
	public function filter_event_properties( $props, $post ) {
		$value = get_post_meta( $post->ID, '_learndash_tec_show_embed_to_learndash_students', true );

		$props['virtual_show_embed_to_learndash_students'] = $value;

		return $props;
	}

	/**
	 * Filters tribe_events_virtual_show_virtual_content hook based on custom LearnDash show_to setting.
	 *
	 * @since 1.0
	 *
	 * @param bool    $show  Whether to show virtual content or not.
	 * @param WP_Post $event Event post object.
	 *
	 * @return bool
	 */
	public function show_virtual_content( $show, $event ) {
		if (
			is_array( $event->virtual_show_embed_to )
			&& in_array(
				static::$meta_values['value_show_embed_to_learndash_students'],
				$event->virtual_show_embed_to
			)
		) {
			$user = wp_get_current_user();

			if ( $user ) {
				$enrolled_courses = ld_get_mycourses( $user->ID );
				$enrolled_groups  = learndash_get_users_group_ids( $user->ID );
				$enrolled_objects = array_merge( $enrolled_courses, $enrolled_groups );

				if ( $event->virtual_show_embed_to_learndash_students === 'any' ) {
					if ( count( $enrolled_objects ) > 0 ) {
						$show = true;
					}
				} elseif ( $event->virtual_show_embed_to_learndash_students === 'associated' ) {
					$associated_objects = $this->get_associated_objects( $event->ID );
					$intersect          = array_intersect( $associated_objects, $enrolled_objects );

					if ( count( $intersect ) > 0 ) {
						$show = true;
					}
				}
			}
		}

		return $show;
	}

	/**
	 * Gets user object.
	 *
	 * @since 1.0
	 *
	 * @param array<string, mixed> $args Arguments.
	 *
	 * @return WP_User|WP_Error
	 */
	public function get_user( $args = [] ) {
		$user = get_user_by( 'email', $args['email'] );

		if ( ! $user ) {
			$user = $this->create_user( $args );
		}

		return $user;
	}

	/**
	 * Creates a user.
	 *
	 * @since 1.0
	 *
	 * @param array<string, mixed> $args Arguments.
	 *
	 * @return WP_User|WP_Error
	 */
	public function create_user( $args = [] ) {
		preg_match( '/(.*?)@(.*?)/', $args['email'], $email_matches );

		$username = $this->create_username( $email_matches[1] );

		preg_match( '/^(.*?)\s(.*?)$/', $args['name'], $name_matches );

		$first_name = $name_matches[1];
		$last_name  = $name_matches[2];

		$user = wp_insert_user(
			[
				'user_login' => $username,
				'user_email' => $args['email'],
				'user_pass'  => wp_generate_password(),
				'first_name' => $first_name,
				'last_name'  => $last_name,
			]
		);

		if ( ! is_wp_error( $user ) ) {
			$user = get_user_by( 'ID', $user );
		}

		return $user;
	}

	/**
	 * Creates a unique username.
	 *
	 * @since 1.0
	 *
	 * @param string $username Username.
	 *
	 * @return string
	 */
	public function create_username( $username ) {
		if ( username_exists( $username ) ) {
			$username = $username . '-' . substr( str_shuffle( md5( time() ) ), 0, 5 );
			$username = $this->create_username( $username );
		}

		return strtolower( $username );
	}
}
