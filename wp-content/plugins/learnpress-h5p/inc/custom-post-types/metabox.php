<?php

use LearnPress\H5P\Model\H5PPostModel;

/**
 * Metabox in LP4
 */
class LP_H5P_Meta_Box {
	public static function output( $post ) {
		$post_id      = $post->ID;
		$h5pPostModel = H5PPostModel::find( $post_id, true );
		if ( ! $h5pPostModel ) {
			return;
		}

		$h5p_chosen = array( '' => '' );
		$edit_link  = '';

		if ( ! lp_h5p_count_h5p_items() ) {
			$edit_link = sprintf(
				__( 'There is no items to select. Create %s.', 'learnpress-h5p' ),
				sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'admin.php?page=h5p_new' ),
					__( 'here', 'learnpress-h5p' )
				)
			);
		}

		$h5p_id_assigned = $h5pPostModel->get_h5p_interact();
		if ( ! empty( $h5p_id_assigned ) ) {
			$h5p_chosen[ $h5p_id_assigned ] = lp_h5p_get_content_title( $h5p_id_assigned );
			$edit_link                      = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				admin_url( 'admin.php?page=h5p&task=show&id=' . $h5p_id_assigned ),
				__( 'Edit the H5P item', 'learnpress-h5p' )
			);
		}

		wp_nonce_field( 'learnpress_save_meta_box', 'learnpress_meta_box_nonce' );
		$data_struct = [
			'urlApi'      => get_rest_url( null, 'lp/h5p/v1/get-h5p-item' ),
			'dataType'    => 'h5p',
			'keyGetValue' => [
				'value'      => 'ID',
				'text'       => '{{title}}',
				'key_render' => [
					'ID'    => 'ID',
					'title' => 'title',
				],
			],
			'setting'     => [
				'placeholder' => esc_html__( 'Choose H5P', 'learnpress' ),
			],
		];

		$h5p_select_field     = new LP_Meta_Box_Select_Field(
			esc_html__( 'Interact H5P', 'learnpress-h5p' ),
			$edit_link,
			'',
			[
				'options'           => [],
				'tom_select'        => true,
				'custom_attributes' => [ 'data-struct' => htmlentities2( json_encode( $data_struct ) ) ],
			]
		);
		$h5p_select_field->id = '_lp_h5p_interact';
		?>

		<div class="lp-meta-box lp-meta-box--h5p">
			<div class="lp-meta-box__inner">
				<?php

				$h5p_select_field->output( $post_id );

				lp_meta_box_text_input_field(
					array(
						'id'          => '_lp_passing_grade',
						'label'       => esc_html__( 'Passing Grade (%)', 'learnpress-h5p' ),
						'description' => esc_html__( 'Requires user reached this point to pass this h5p content item.', 'learnpress-h5p' ),
						'type'        => 'number',
						'default'     => 50,
						'style'       => 'width: 80px',
					)
				);
				?>
			</div>
		</div>
		<?php
	}
}
