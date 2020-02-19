<?php
/**
 * Integrate FeedBlitz and WPForms
 *
 * @package    Integrate_FeedBlitz_WPForms
 * @since      1.0.0
 * @copyright  Copyright (c) 2017, Bill Erickson
 * @license    GPL-2.0+
 */

class Integrate_FeedBlitz_WPForms {

    /**
     * Primary Class Constructor
     *
     */
    public function __construct() {

        add_filter( 'wpforms_builder_settings_sections', array( $this, 'settings_section' ), 20, 2 );
        add_filter( 'wpforms_form_settings_panel_content', array( $this, 'settings_section_content' ), 20 );
        add_action( 'wpforms_process_complete', array( $this, 'send_data_to_feedblitz' ), 10, 4 );

    }

    /**
     * Add Settings Section
     *
     */
    function settings_section( $sections, $form_data ) {
        $sections['be_feedblitz'] = __( 'FeedBlitz', 'integrate-feedblitz-wpforms' );
        return $sections;
    }


    /**
     * emma Settings Content
     *
     */
    function settings_section_content( $instance ) {
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-be_feedblitz">';
        echo '<div class="wpforms-panel-content-section-title">' . __( 'FeedBlitz', 'integrate-feedblitz-wpforms' ) . '</div>';

		wpforms_panel_field(
			'text',
			'settings',
			'be_feedblitz_api_key',
			$instance->form_data,
			__( 'API Key', 'integrate-feedblitz-wpforms' )
		);

        wpforms_panel_field(
            'text',
            'settings',
            'be_feedblitz_list_id',
            $instance->form_data,
            __( 'List ID', 'integrate-feedblitz-wpforms' )
        );

        wpforms_panel_field(
            'select',
            'settings',
            'be_feedblitz_field_email',
            $instance->form_data,
            __( 'Email Address', 'integrate-feedblitz-wpforms' ),
            array(
                'field_map'   => array( 'email' ),
                'placeholder' => __( '-- Select Field --', 'integrate-feedblitz-wpforms' ),
            )
        );

		wpforms_panel_field(
              'select',
              'settings',
              'be_feedblitz_field_first_name',
              $instance->form_data,
              __( 'First Name', 'integrate-feedblitz-wpforms' ),
              array(
                  'field_map'   => array( 'text', 'name' ),
                  'placeholder' => __( '-- Select Field --', 'integrate-feedblitz-wpforms' ),
              )
          );


        echo '</div>';
    }

    /**
     * Integrate WPForms with FeedBlitz
     *
     */
    function send_data_to_feedblitz( $fields, $entry, $form_data, $entry_id ) {

        $api_key = $list_id = false;
        if( !empty( $form_data['settings']['be_feedblitz_api_key'] ) )
            $api_key = $form_data['settings']['be_feedblitz_api_key'];
        if( !empty( $form_data['settings']['be_feedblitz_list_id'] ) )
            $list_id = intval( $form_data['settings']['be_feedblitz_list_id'] );

        if( ! ( $api_key && $list_id ) )
            return;

		// Filter for limiting integration
        if( ! apply_filters( 'be_feedblitz_process_form', true, $fields, $form_data ) )
            return;

		$url = 'https://app.feedblitz.com/f/?SimpleApiSubscribe&key=' . $api_key . '&listid=' . $list_id;

        $email_field_id = intval( $form_data['settings']['be_feedblitz_field_email'] );
		$email = !empty( $email_field_id ) && !empty( $fields[ $email_field_id ]['value'] ) ? esc_html( $fields[ $email_field_id]['value'] ) : false;
		if( !empty( $email ) )
			$url .= '&email=' . $email;

		$first_name_field_id = intval( $form_data['settings']['be_feedblitz_field_first_name'] );
		$first_name = !empty( $first_name_field_id ) && !empty( $fields[ $first_name_field_id ]['value'] ) ? esc_html( $fields[ $first_name_field_id ]['value'] ) : false;
		if( !empty( $first_name ) )
			$url .= '&First_Name=' . $first_name;

		$args = [];

		$request = wp_remote_get( $url, $args );

    }

}
new Integrate_FeedBlitz_WPForms;
