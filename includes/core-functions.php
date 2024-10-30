<?php
/**
 * Created by PhpStorm.
 * User: Silas
 * Date: 12-12-2017
 * Time: 14:03
 *
 * MailCamp - Core Functionality
 */

// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param $atts
 * @param $content
 * @param $this_shortcode
 *
 * @return string
 * @since 1.0.0
 */
function mailcamp_shortcode_formulier( $atts, $content, $this_shortcode ) {
	// check if form is loaded in the widget
	$widget = explode( '_', $this_shortcode )[0] === 'widget';

	$options_api  = get_option( 'mailcamp_options_api' );
	$options_form = get_option( 'mailcamp_options_form' );
	// TODO: get form where $options_form['mailcamp_field_shortcode'] == $this_shortcode
	$options  = array_merge( $options_api, $options_form );
	$required = [ 'api_path', 'api_username', 'api_token' ];
	// only keep the required api credentials
	$api_credentials = array_intersect_key( $options, array_flip( $required ) );
	if ( count( $api_credentials ) !== 3 ) {
		return '';
	}
	$fields = get_selected_field_details();
	// create the html form
	$html_form            = '';
	$listid               = $options['mailcamp_lists'];
	$mailcamp_list_fields = @$options['mailcamp_list_fields'] ?: [];

	// get the position of the email field (this field is not added yet)
	$email_key_arr = @array_keys( $mailcamp_list_fields, 'email' );
	
	// Create the email field (this field could not be fetched, so we have to create it)
	$email_field            = new stdClass();
	$email_field->fieldid   = 'email';
	$email_field->name      = $options['mailcamp_field_email'];
	$email_field->required  = 1;
	$email_field->fieldtype = 'text';
	// if only the email field is set
	if ( empty( $fields ) ) {
		$fields[] = $email_field;
	} else {
		// add email at the beginning of the fields array (if email field is not saved, we will add it also at the beginning)
		if ( $email_key_arr === null || count( $email_key_arr ) == 0 || current( $email_key_arr ) == 0 ) {
			array_unshift( $fields, $email_field );
		} else {
			// add email at the saved position
			$email_key = current( $email_key_arr );
			$fields    = array_slice( $fields, 0, $email_key, true ) +
			             [ $email_key => $email_field ] +
			             array_slice( $fields, $email_key + 1, count( $fields ) + 1, true );
		}
	}

	$html_form .= '<form class="mailcamp-subscribe-form" method="post" action="" onsubmit="return submitMailCampForm(this)"> ';
	$html_form .= '<fieldset>';
	$html_form .= '<input type="hidden" name="CustomFields[listid]" value="' . $listid . '"/>';
	$html_form .= '<div class="required_info"><span class="required">*</span> ' . __('Required field', 'mailcamp') . '</small></div>';

	if ( isset( $fields ) ) {
		foreach ( $fields as $field ) {

			$html_form .= '<p><label id="CustomFields_' . $field->fieldid . '">' . ucfirst( $field->name ) . ': ' . ((bool) $field->required ? '<span class="required">*</span>' : '') . '</label>';
			switch ( $field->fieldtype ) {
				case 'dropdown':
					$html_form .= '<select id="CustomFields_' . $field->fieldid . '" name="CustomFields[' . $field->fieldid . ']"' . ((bool) $field->required ? ' aria-required="true" required' : '') . '>';
					if ( isset( $field->defaultvalue ) && ! empty( $field->defaultvalue ) && is_string(
							$field->defaultvalue
						) ) {
						$html_form .= '<option value="' . $field->defaultvalue . '">' . $field->defaultvalue . '</option>';
					}
					foreach ( unserialize( $field->fieldsettings )['Value'] as $fieldsetting ) {
						$html_form .= '<option value="' . $fieldsetting . '">' . $fieldsetting . '</option>';
					}
					$html_form .= '</select><br />';
					break;
				case 'checkbox':
					foreach ( unserialize( $field->fieldsettings )['Value'] as $fieldsetting ) {
						$html_form .= '<label for="CustomFields_' . $field->fieldid . '_' . $fieldsetting . '"><input type="checkbox" name="CustomFields[' . $field->fieldid . '][]" id="CustomFields_' . $field->fieldid . '_' . $fieldsetting . '" value="' . $fieldsetting . '"' . ((bool) $field->required ? ' aria-required="true" required' : '') . '> ' . $fieldsetting . '</label>';
					}
					break;
				case 'radiobutton':
					foreach ( unserialize( $field->fieldsettings )['Value'] as $fieldsetting ) {
						$html_form .= '<label for="CustomFields_' . $field->fieldid . '_' . $fieldsetting . '"><input type="radio" name="CustomFields[' . $field->fieldid . ']" id="CustomFields_' . $field->fieldid . '_' . $fieldsetting . '" value="' . $fieldsetting . '"' . ((bool) $field->required ? ' aria-required="true" required' : '') . '> ' . $fieldsetting . '</label>';
					}
					break;
				case 'date':
					$date = is_string( $field->defaultvalue ) ? date( "Y-m-d",
						strtotime( $field->defaultvalue ) ) : date(
						"Y-m-d",
						time()
					);

					$html_form .= '<input name="CustomFields[' . $field->fieldid . ']" id="CustomFields_' . $field->fieldid . '" type="date" value="' . $date . '" onkeydown="return false;" min="' . intval( unserialize( $field->fieldsettings )['Key'][3] ) . '-01-01" max="' . intval( unserialize( $field->fieldsettings )['Key'][4] ) . '-12-31"' . ((bool) $field->required ? ' aria-required="true" required' : '') . ' />';
					break;
				case 'number':
					$html_form .= '<input type="number" name="CustomFields[' . $field->fieldid . ']" id="CustomFields_' . $field->fieldid . '"' . ((bool) $field->required ? ' aria-required="true" required' : '') . ' />';
					break;
				case 'textarea':
					if ( isset( $field->defaultvalue ) && ! empty( $field->defaultvalue ) && is_string(
							$field->defaultvalue
						) ) {
						$html_form .= '<textarea name="CustomFields[' . $field->fieldid . ']" id="CustomFields_' . $field->fieldid . '"' . ((bool) $field->required ? ' aria-required="true" required' : '') . '>' . $field->defaultvalue . '</textarea>';
					}
					break;
				default:
					$fieldtype = ($field->fieldid === 'email' ? 'email' : 'text');
					$html_form .= '<input type="' . $fieldtype . '" name="CustomFields[' . $field->fieldid . ']" id="CustomFields_' . $field->fieldid . '" ' . ((bool) $field->required ? ' aria-required="true" required' : '') . '>';
			}
			$html_form .= '</p>' . "\n";
		}
	}
	
	$submit_text = isset( $options["mailcamp_field_submit"] ) ? $options["mailcamp_field_submit"] : 'subscribe';

	$readmore_pos = strpos( $options['gdpr_description'], '<!--more-->' );
	if ( $widget ) {
		$html_form .= '<br /><div><p><a href="' . $options['widget_gdpr_url'] . '" target="_blank">Privacy Statement</a></p></div>';
	} elseif ( $readmore_pos !== false ) {
		$html_form .= '<div class="mailcamp-gdpr-less"><p>' . substr(
				$options['gdpr_description'],
				0,
				$readmore_pos
			) . '</p></div>';
		$html_form .= '<p><div class="mailcamp-gdpr-more">';
		$html_form .= '' . $options['gdpr_description'] . '';
		$html_form .= '</div>';
		$html_form .= '<a href="javascript:void(0)" onclick="return showMore(this)" class="mailcamp-gdpr-more-link">' . __(
				'Read more',
				'mailcamp'
			) . ' >></a></p>';
	} else {
		$html_form .= '<div class="gdpr_description"><p>' . $options['gdpr_description'] . '</p></div>';
	}

	$html_form .= '<label for="acceptterms"><input type="checkbox" id="acceptterms" name="CustomFields[accepted]" value="check" /> ' . mailcamp_add_anchor(
			$options['gdpr_checkbox_text'],
			$options['widget_gdpr_url']
		) . '</label>';
	$html_form .= '<br /><label class="mailcamp-form-captcha-sum mc-inline" for="captcha"></label><input class="mailcamp-form-captcha-field" id="captcha" type="number" size="4" maxlength="3" min="0" max="100" name="captcha" value="" />';
	$html_form .= '<input type="hidden" name="captcha_val_1" id="captcha_val_1" value=""/>';
	$html_form .= '<input type="hidden" name="captcha_val_2" id="captcha_val_2" value=""/>';
	$html_form .= '<input type="submit" disabled value="' . $submit_text . '" class="mc-btn-submit">';
	$html_form .= '<br /><br /><div class="mailcamp-form-confirm" aria-live="polite"></div>';
	$html_form .= '</fieldset>';
	$html_form .= '</form>';
	$html_form .= '<script>
							var subscribe_url = "' . admin_url( 'admin-ajax.php' ) . '";
							
							// show whole content when \'read more\' button is triggered
							function showMore(this_button) {
								jQuery(this_button).closest(\'form\').find(\'.mailcamp-gdpr-less\').hide();
								jQuery(this_button).closest(\'form\').find(\'.mailcamp-gdpr-more-link\').hide();
								jQuery(this_button).closest(\'form\').find(\'.mailcamp-gdpr-more\').show();
							}
							
							// This function is triggered on the form submission
							function submitMailCampForm(this_form) {
							
							    // accepted GDPR conditions, subscribe
							    if (this_form.elements[\'CustomFields[accepted]\'].checked) {
							        // get values from form
							        // var elements = document.getElementById(\'mailcamp-subscribe-form\');							        
							        var elements = jQuery(this_form).serializeArray();
							        console.log(elements);
							        // // Add to form object
							        var formData = new FormData();
							        for (var i = 0; i < elements.length; i++) {
							            formData.append(elements[i].name, elements[i].value);
							        }
							        formData.append(\'action\', \'add_subscriber_to_list\');
							        // Create the AJAX POST
							        xmlhttp = new XMLHttpRequest();
							        xmlhttp.onreadystatechange = function () {
							            if (this.readyState == 4 && this.status == 200) {
							                console.log(this.responseText);
							                var response_text = JSON.parse(this.responseText);
							
							                response_text = response_text[1];
							
							                jQuery(this_form).find(".mailcamp-form-confirm").html(response_text);
							            }
							        };
							        xmlhttp.open("POST", subscribe_url, true);
							        xmlhttp.send(formData);
							        return false;
							    }
							
							    else {
							        // did not accepted the GDPR conditions
//							        alert(\'Om u in te schrijven dient u akkoord te gaan met bovenstaande GDPR privacywetgeving.\');
							        alert("' . __(
			'To subscribe, you must agree to the above GDPR privacy legislation.',
			'mailcamp'
		) . '");
							        return false;
							    }
							}
                       </script>';


//	}

	return $html_form;
}

// add all saved shortcodes
$shortcodes = get_option( 'mailcamp_options_shortcode' ) ?: [];
if ( count( $shortcodes ) > 0 ) {

	foreach ( $shortcodes as $shortcode ) {
		add_shortcode( $shortcode, 'mailcamp_shortcode_formulier' );
	}

}

function get_selected_field_details() {
	$options_api  = get_option( 'mailcamp_options_api' );
	$options_form = get_option( 'mailcamp_options_form' );

	$fields       = [];
	if ( !isset($options_form['mailcamp_list_fields']) || empty($options_form['mailcamp_list_fields']) ) {
		return $fields;
	}
	
	if ( !isset($options_form['custom_fields']) || empty($options_form['custom_fields']) ) {
		return $fields;
	}

	/*
	 * DO NOT TRY TO GET THE CUSTOM FIELDS IF THEY ARE NOT SET
	if ( !isset($options_form['custom_fields']) ) {

		$listid = $options_form['mailcamp_lists'];

		$options  = array_merge( $options_api, $options_form );
		$required = [ 'api_path', 'api_username', 'api_token' ];
		// only keep the required api credentials
		$api_credentials = array_intersect_key( $options, array_flip( $required ) );

		// All required api credentials exist!
		$mc_api = new MailCamp_Api( $api_credentials );

		$custom_fields = $mc_api->fields( $listid );
		if ( $mc_api->result->status ) {
			$custom_fields = json_decode( json_encode( $custom_fields ) )->item;

			// create array of custom fields that are saved and add the details fetched by MailCamp Api
			$mailcamp_list_fields = @$options['mailcamp_list_fields'] ?: [];
			if ( ! empty( $mailcamp_list_fields ) ) {
				foreach ( $custom_fields as $field ) {
					if ( in_array( $field->fieldid, $mailcamp_list_fields ) ) {
						$fields[] = $field;
					}
				}
			}
		}
		update_option( 'mailcamp_options_form', array_merge( $options_form, [ 'custom_fields' => $fields ] ) );
	}

	$options_form = get_option( 'mailcamp_options_form' );
	*/

	$temp_custom_fields = [];
	foreach($options_form['custom_fields'] as $field){
	    $temp_custom_fields[$field->fieldid] = $field;
	}

    $custom_fields = [];
    foreach($options_form['mailcamp_list_fields'] as $key){
        $custom_fields[] = $temp_custom_fields[$key];
    }

	return $custom_fields;

}

/**
 * Fetch rss xml and displays a list of sent newsletters
 * Each newsletter contains the link to the newsletter
 *
 * @param $atts
 * @param $content
 * @param $this_shortcode
 *
 * @return string
 * @since 1.0.0
 * @updated 1.5.6
 */
function mailcamp_shortcode_rss( $atts, $content, $this_shortcode ) {

	$listid = array_reverse( explode( '_', $this_shortcode ) )[0];

	$options_api = get_option( 'mailcamp_options_api' );
	$required_credentials = [ 'api_path', 'api_username', 'api_token' ];
	// only keep the required api credentials
	$api_credentials = array_intersect_key( $options_api, array_flip( $required_credentials ) );
	if ( count( $api_credentials ) === 3 ) {
		// All required api credentials exist!
		$mc_api         = new MailCamp_Api( $api_credentials );
		$api_connection = $mc_api->connection();
		if ( $api_connection->status === true ) {
			$archives        = $mc_api->getArchives($listid, 100);
			if ($archives->status === false) {
				$error_message = 'Error: '.$archives->data;
				return $error_message;
			}
			$html = '<table class="mc-rss-newsletters">';
			foreach ( $archives->data->item as $key => $newsletter ) {
				$link = str_replace( 'xml.php', '', $options_api['api_path'] ) . 'display.php?List=' . $listid . '&N=' . $newsletter->newsletterid;
				$html .= '<tr>
					<td class="mc-newsletter-startdate">
						<a style="display:block; text-decoration: none;" href="' . $link . '" target="_blank">' . wp_date('d-m-Y', (int)$newsletter->starttime) . '</a>
					</td>
					<td>
						<a style="display:block; text-decoration: none;" href="' . $link . '" target="_blank">' . mailcamp_remove_emoji($newsletter->subject) . '</a>
					</td>							   
				</tr>
							';
			}
			$html .= '</table>';
			return $html;
		}
	}
}

// add all saved
$lists = get_option( 'mailcamp_options_lists' ) ?: [];
if ( count( $lists ) > 0 ) {

	foreach ( $lists as $list ) {
		add_shortcode( $list['shortcode'], 'mailcamp_shortcode_rss' );
	}

}

/**
 * Add anchor to part(between needles) of string
 *
 * @param string $str
 * @param string $url
 * @param string $needle_start
 * @param string $needle_end
 *
 * @return null|string|string[]
 * @since 1.0.0
 */
function mailcamp_add_anchor( $str = '', $url = '', $needle_start = '{{', $needle_end = '}}' ) {
	$pattern = '/\{\{(.*?)\}\}/i';
	preg_match( $pattern, $str, $match );
	if ( isset( $match[1] ) ) {
		$anchor = '<a href="' . $url . '" target="_blank">' . $match[1] . '</a>';
	} else {
	    $anchor = '';
    }

	return preg_replace( $pattern, $anchor, $str );
}

/**
 * @param string $shortcode
 *
 * @return string
 * @since 1.0.0
 */
function mailcamp_print_shortcode( $shortcode = '' ) {
	return '[' . $shortcode . ']';
}

/**
 * Use this function to remove Emoji characters
 * @param type $text
 *
 * @return string
 * @since 1.0.0
 * @updated 1.5.6
 */
function mailcamp_remove_emoji($text) {
	$text = str_replace(['=?UTF-8?Q?', '?='], ['', ''], $text);
	$text = quoted_printable_decode(htmlspecialchars_decode($text, ENT_QUOTES));
	$text = preg_replace('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $text);
	$text = trim($text);

    return $text;
}
