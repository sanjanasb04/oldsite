<?php
/**
 * Class LP_H5P_Admin_Settings
 */
class LP_H5P_Admin_Settings extends LP_Abstract_Settings_Page {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id   = 'h5p';
		$this->text = __( 'H5P', 'learnpress-h5p' );
		//add_action( 'learn-press/update-settings/updated', array( $this, 'update_settings' ) );
		parent::__construct();
	}

	public function get_settings( $section = '', $tab = '' ) {
		return apply_filters(
			'learn-press/admin/h5p-settings/general',
			array(
				array(
					'type' => 'title',
				),
				array(
					'title'   => esc_html__( 'H5P Button Complete', 'learnpress-h5p' ),
					'id'      => 'h5p_button_complete',
					'default' => 'yes',
					'type'    => 'checkbox',
					'desc'    => esc_html__( 'Enable/Disable', 'learnpress-h5p' ),
				),
				array(
					'type' => 'sectionend',
				),
			)
		);
	}

	public function update_settings( $input ) {
		$page              = filter_input( INPUT_GET, 'page' );
		$tab               = filter_input( INPUT_GET, 'tab' );
		$learn_press_h5p   = isset( $_POST['learn_press_collections'] ) ? $_POST['learn_press_h5p'] : array();
		$lp_settings_nonce = filter_input( INPUT_POST, 'lp-settings-nonce' );
		if ( $page == 'learn-press-settings' && $tab == 'h5p' && ! empty( $learn_press_h5p ) && ! empty( $lp_settings_nonce ) ) {
			set_transient( 'learn-press-h5p-flush-rewrite-rules', true );
		}
	}
}

return new LP_H5P_Admin_Settings();
