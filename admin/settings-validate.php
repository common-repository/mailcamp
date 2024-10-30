<?php
/**
 * Created by PhpStorm.
 * User: Silas
 * Date: 12-12-2017
 * Time: 13:50
 *
 * MailCamp - Validate Settings
 */

// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * validate plugin settings
 *
 * @since 1.0.0
 * @param $input
 * @return mixed
 */
function mailcamp_callback_validate_options( $input ) {

	// api path
	if ( isset( $input['api_path'] ) ) {

		$input['api_path'] = esc_url( $input['api_path'] );

	}

	// api token
	if ( isset( $input['api_token'] ) ) {

		$input['api_token'] = sanitize_text_field( $input['api_token'] );

	}

	// api username
	if ( isset( $input['api_username'] ) ) {

		$input['api_username'] = sanitize_text_field( $input['api_username'] );

	}

	// gdpr description
	if ( isset( $input['gdpr_description'] ) ) {

		$input['gdpr_description'] = wp_kses_post( $input['gdpr_description'] );

	}

    // woocommerce integration
    if ( isset( $input['wc_signup_enabled'] ) ) {

        $input['wc_signup_enabled'] = sanitize_text_field( $input['wc_signup_enabled'] );

    }

    // woocommerce signup position
    if ( isset( $input['wc_signup_position'] ) ) {

        $input['wc_signup_position'] = sanitize_text_field( $input['wc_signup_position'] );

    }

    // woocommerce signup custom message
    if ( isset( $input['wc_signup_custom_message'] ) ) {

        $input['wc_signup_custom_message'] = sanitize_text_field( $input['wc_signup_custom_message'] );

    }

    // woocommerce signup checkbox default
    if ( isset( $input['wc_signup_checkbox_default'] ) ) {

        $input['wc_signup_checkbox_default'] = sanitize_text_field( $input['wc_signup_checkbox_default'] );

    }

    // signup list id
    if ( isset( $input['wc_signup_list'] ) ) {

        $input['wc_signup_list'] = sanitize_text_field( $input['wc_signup_list'] );

    }

    // woocommerce field mappings
    if ( isset( $input['wc_mapped_fields'] ) ) {

        $fields = $input['wc_mapped_fields'];
        foreach ($fields as $key => $value) {
            if($value === '' || !is_numeric($value)) {
                $input['wc_mapped_fields'] = array_diff($fields, [$key => $value]);
            }
        }

    }

	return $input;
}