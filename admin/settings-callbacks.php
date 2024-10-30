<?php
/**
 * Created by PhpStorm.
 * User: Silas
 * Date: 8-12-2017
 * Time: 16:35
 *
 * MailCamp - Callback functions
 */

// exit if file is called directly
if ( ! defined('ABSPATH')) {

    exit;

}

/**
 * default callback options
 *
 * @since 1.0.0
 * @return array
 */
function mailcamp_options_default()
{

    return [
        'api_path'                    => '',
        'api_username'                => '',
        'api_token'                   => '',
        'mailcamp_field_shortcode'    => '',
        'widget_gdpr_url'             => 'http'.(isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/privacy-policy',
        'gdpr_description'            => __('Sign up for the newsletter and receive information about our services, products and promotions every 4 weeks. For more information, please refer to our <a href="https://www.mailcamp.nl/2017/12/04/nieuwe-europese-privacy-wetgeving/">Privacy Statement</a>.',
            'mailcamp'
        ),
        'gdpr_checkbox_text'          => __('I agree the {{GDPR Privacy Policy}}', 'mailcamp'),
        'mailcamp_lists'              => '',
        'mailcamp_field_email'        => __('email', 'mailcamp'),
        'mailcamp_field_submit'       => __('subscribe', 'mailcamp'),
        'custom_confirm_mail'         => '',
        'custom_confirm_mail_enabled' => [__('on', 'mailcamp') => true, __('off', 'mailcamp') => false],
        'wc_signup_enabled'           => [__('on', 'mailcamp') => true, __('off', 'mailcamp') => false],
        'wc_signup_checkbox_default'  => [__('checked', 'mailcamp') => true, __('unchecked', 'mailcamp') => false],
        'wc_signup_position'          => 'woocommerce_after_order_notes',
        'wc_signup_custom_message'    => __('I would like to receive the newsletter'),
        'wc_signup_double_optin'      => [__('on', 'mailcamp') => true, __('off', 'mailcamp') => false],
        'wc_signup_list'              => '',
    ];


}

/**
 * @since 1.0.0
 * @param $args
 */
function mailcamp_callback_field_shortcode($args)
{

    $page    = isset($args['page']) ? $args['page'] : '';
    $options = get_option($page, mailcamp_options_default());

    $id    = isset($args['id']) ? $args['id'] : '';
    $label = isset($args['label']) ? $args['label'] : '';

    $value = isset($options[$id]) && ! empty($options[$id]) ? sanitize_text_field($options[$id]) : $args['shortcode'];

    if (empty($value)) {
        $label = __('Note: Your shortcode will appear above when this form is saved.', 'mailcamp');
    }

    echo '<code>['.$value.']</code>&nbsp;&nbsp;<button class="button button-primary" data-copy="['.$value.']">'.__(
            'Copy Shortcode',
            'mailcamp'
        ).'</button><input type="hidden" name="'.$page.'['.$id.']" value="'.$value.'">';
    echo '<br /><label><i>'.$label.'</i></label>';

}

/**
 * @since 1.0.0
 */
function mailcamp_callback_section_info_apicredentials()
{

    echo __(
        '<p>You can find this in the  \'User Account ⇨ Change User\' section under the tab \'User Permissions\'. Make sure that \'Enable the XML API\' is checked and saved.</p>',
        'mailcamp'
    );
}

/**
 * @since 1.0.0
 */
function mailcamp_callback_section_info_rss_settings()
{

    echo __(
        '<p>You can find this in the  \'User Account ⇨ Change User\' section under the tab \'User Permissions\'. Make sure that \'Enable the XML API\' is checked and saved.</p>',
        'mailcamp'
    );
}

/**
 * @since 1.0.0
 */
function mailcamp_callback_section_info_gdpr()
{

    echo '<p>'.__(
            'Ensure that you comply with the GDPR legislation. You must indicate on each enrollment or change form how often you will mail, what you will use the requested data for, how long you will retain personal data and what the content of the newsletters will be. That\'s a lot of information! Refer in the introduction to your privacy statement so that you are legally covered.',
            'mailcamp'
        ).'</p>';

}

/**
 * @since 1.0.0
 */
function mailcamp_callback_section_info_form_settings()
{

    echo __('<p>Create your subscription form here.</p>', 'mailcamp');

}

/**
 * @since 1.0.0
 */
function mailcamp_callback_section_info_get_lists()
{

    echo __('<p>Select the list and fields you want to use in your form</p>', 'mailcamp');

}

/**
 * @param $args
 * @since 1.0.0
 */
function mailcamp_callback_field_api_status($args)
{

    if ($args['status'] === true) {
        $class = 'success';
        $msg   = __('CONNECTED', 'mailcamp');
        $info  = '';
    } else {
        $class = 'danger';
        $msg   = __('NOT CONNECTED', 'mailcamp');
        $info  = $args['info'];
    }

    echo '<span class="status '.$class.'">'.$msg.'</span>';
    echo '<br /><label><i>'.$info.'</i></label>';

}

/**
 * @param $args
 * @since 1.0.0
 */
function mailcamp_callback_field_lists($args)
{

    $page     = isset($args['page']) ? $args['page'] : '';
    $options  = get_option($page, mailcamp_options_default());
    $id       = isset($args['id']) ? $args['id'] : '';
    $mc_lists = $args['mc_lists'];

    echo '<select name="'.$page.'['.$id.']">';

    foreach ($mc_lists->item as $list) {

        if (isset($options[$id]) && $options[$id] == $list->listid) {
            $select = 'selected';
        } else {
            $select = '';
        }

        echo '<option name="'.$page.'['.$id.']" value="'.$list->listid.'" '.$select.' >'.htmlspecialchars(
                $list->name
            ).'</option>';
    }

    echo '</select>';
    echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="'.__(
            'Fetch Fields',
            'mailcamp'
        ).'"></p>';

}

/**
 * @param $args
 * @since 1.0.0
 */
function mailcamp_callback_field_lists_fields($args)
{
    $page    = isset($args['page']) ? $args['page'] : '';
    $options = get_option($page, mailcamp_options_default());

	save_selected_field_details();

    $label   = isset($args['label']) ? $args['label'] : '';
    $id      = isset($args['id']) ? $args['id'] : '';

    $mc_list_fields = $args['list_fields'];

    $unselected = '<select multiple id="dropdown" data-deselected size="20" name="'.$page.'['.$id.'][]" size="70" style="width: 200px">';
    foreach ($mc_list_fields as $field) {

        if ( ! isset($options[$id]) || ! in_array($field->fieldid, $options[$id])) {
            $unselected .= '<option name="'.$page.'['.$id.'][]"
					value="'.$field->fieldid.'" />'.htmlspecialchars($field->name).'</option>';
        }

    }
    $unselected .= '</select>';

    $selected = '<select multiple id="dropdown" data-selected size="20" name="'.$page.'['.$id.'][]" size="70" style="width: 200px">';

    $mc_list_fields_arr = [];
    foreach ($mc_list_fields as $value) {
        $mc_list_fields_arr[$value->fieldid] = $value;
    }

    if (isset($options[$id])) {
        foreach ($options[$id] as $fieldid) {
            $selected .= '<option name="'.$page.'['.$id.'][]"
					value="'.$fieldid.'" selected/>'.htmlspecialchars(
                    $fieldid == 'email' ? 'email' : $mc_list_fields_arr[$fieldid]->name
                ).'</option>';
        }
    }

    echo '<label for="'.$page.'_'.$id.'">'.$label.'</label>';

    $selected .= '</select>';
    echo "<div>
			<div style='float: left'>$unselected</div>
			<div style='float: left; padding: 180px 50px;'><< >></div>
			<div style='float: left'>$selected</div>
			</div>";

}

/**
 * @param $args
 * @since 1.0.0
 */
function mailcamp_callback_field_text($args)
{
    $page    = isset($args['page']) ? $args['page'] : '';
    $type    = isset($args['type']) ? $args['type'] : 'text';
    $options = get_option($page, mailcamp_options_default());

    $id    = isset($args['id']) ? $args['id'] : '';
    $label = isset($args['label']) ? $args['label'] : '';

    $defaults = mailcamp_options_default();
    $default  = $defaults[$id];

    $value = isset($options[$id]) ? sanitize_text_field($options[$id]) : (@$args['value'] ?: $default);

    echo '<input id="'.$page.'_'.$id.'" name="'.$page.'['.$id.']"
    type="'.$type.'" size="70" value="'.htmlspecialchars($value).'"><br />';
    echo '<label for="'.$page.'_'.$id.'">'.$label.'</label>';

}

/**
 * @param $args
 * @since 1.0.0
 */
function mailcamp_callback_field_radio($args)
{
    $page    = isset($args['page']) ? $args['page'] : '';
    $options = get_option($page, mailcamp_options_default());

    $id = isset($args['id']) ? $args['id'] : '';

    $defaults = mailcamp_options_default();
    $default  = $defaults[$id];

    $value = isset($options[$id]) ? sanitize_text_field($options[$id]) : (@$args['value'] ?: $default);

    foreach ($defaults[$id] as $label => $option_value) {
        echo '<input type="radio" id="'.$page.'_'.$id.$label.'" name="'.$page.'['.$id.']" value="'.(int)$option_value.'" '.((int)$option_value == $value ? 'checked="checked"' : '').' />'."\t";
        echo '<label for="'.$page.'_'.$id.'">'.$label.'</label>'."\t\n<br />";
    }

}

/**
 * @param $args
 * @since 1.0.0
 */
function mailcamp_callback_field_wysiwyg($args)
{

    $page    = isset($args['page']) ? $args['page'] : '';
    $options = get_option($page, mailcamp_options_default());
    $id      = isset($args['id']) ? $args['id'] : '';
    $label   = isset($args['label']) ? $args['label'] : '';

    $allowed_tags = wp_kses_allowed_html('post');

    $defaults = mailcamp_options_default();
    $default  = $defaults[$id];

    // TODO: Why does this function not exist? Do i have to use wp_kses_post_deep instead?
    if (function_exists('stripslashes_deep')) {
        // TODO: remove the @
        $removed_slashes = stripslashes_deep(@$options[$id]);
        $default         = stripslashes_deep($default);
    } else {
        $removed_slashes = map_deep($options[$id], 'stripslashes_from_strings_only');
        $default         = map_deep($default, 'stripslashes_from_strings_only');
    }


    $value = isset($options[$id]) ? wp_kses($removed_slashes, $allowed_tags) : $default;

    $settings = [
        'textarea_name' => $page.'['.$id.']',
    ];

    echo '<label for="'.$page.'_'.$id.'">'.$label.'</label>';
    echo wp_editor($value, $page.'_'.$id, $settings);

}

function save_selected_field_details(){
	$options_api  = get_option('mailcamp_options_api');
	$options_form = get_option('mailcamp_options_form');
	$listid = $options_form['mailcamp_lists'];

	// TODO: get form where $options_form['mailcamp_field_shortcode'] == $this_shortcode

	$options  = array_merge($options_api, $options_form);
	$required = ['api_path', 'api_username', 'api_token'];
	// only keep the required api credentials
	$api_credentials = array_intersect_key($options, array_flip($required));

	// All required api credentials exist!
	$mc_api = new MailCamp_Api($api_credentials);

	$custom_fields = $mc_api->fields($listid);
	$fields = [];
	if ($mc_api->result->status) {
		$custom_fields = json_decode(json_encode($custom_fields))->item;

		// create array of custom fields that are saved and add the details fetched by MailCamp Api
		$mailcamp_list_fields = @$options['mailcamp_list_fields'] ?: [];
		if ( ! empty($mailcamp_list_fields)) {
			foreach ($custom_fields as $field) {
				if (in_array($field->fieldid, $mailcamp_list_fields)) {
					$fields[] = $field;
				}
			}
		}
	}

	update_option('mailcamp_options_form', array_merge($options_form, ['custom_fields' => $fields]));
}

/** Since 1.5.3 */
function mailcamp_callback_section_info_wc() {
    echo '<p>'.__('Manage WooCommerce integration', 'mailcamp').'</p>';

    if(!class_exists('WooCommerce' )) {
        echo '<div class="notice error"><p>' . __('The WooCommerce plugin is currently not installed and/or activated. <a href="plugins.php" style="color: #ffffff;">Manage Plugins</a>', 'mailcamp') . '</p></div>';
    }
}

/** @since 1.5.3 */
function mailcamp_callback_wc_enabled()
{
    $option  = get_option('mailcamp_options_wc');
    $defaults = mailcamp_options_default();

    $value = isset($option['wc_signup_enabled']) ? sanitize_text_field($option['wc_signup_enabled']) : false;

    foreach ($defaults['wc_signup_enabled'] as $label => $option_value) {
        echo '<input type="radio" id="mailcamp_options_wc_'.$label.'" name="mailcamp_options_wc[wc_signup_enabled]" value="'.(int)$option_value.'" '.((int)$option_value == $value ? 'checked="checked"' : '').' />'."\t";
        echo '<label for="mailcamp_options_wc_'.$label.'">'.$label.'</label><br>' . "\t";
    }
}

/** @since 1.5.3 */
function mailcamp_callback_wc_signup_position()
{
    $option = get_option('mailcamp_options_wc');

    $positions = [
        'woocommerce_after_order_notes' => __('After the customer order notes field [woocommerce_after_order_notes]', 'mailcamp'),
        'woocommerce_review_order_before_submit' => __('Before "Submit order" button [woocommerce_review_order_before_submit]', 'mailcamp'),
    ];

    echo '<select name="mailcamp_options_wc[wc_signup_position]">';

    foreach ($positions as $position => $label) {
        $select = (isset($option['wc_signup_position']) && $option['wc_signup_position'] === $position) ? 'selected' : '';
        echo '<option name="mailcamp_options_wc[wc_signup_position]" value="'.$position.'" ' . $select . ' >' . $label . '</option>';
    }

    echo '</select>';

    if(isset($option['wc_signup_position']) && $option['wc_signup_position'] === 'woocommerce_review_order_before_submit') {
        echo '<div style="margin-top: 1em;"><strong>' . __('Important note:', 'mailcamp') . '</strong> ' . __('When selecting the \'woocommerce_review_order_before_submit\' layout position make sure to assign a WooCommerce \'Terms and conditions\' page first. Otherwise the newsletter subscription checkbox won\'t be displayed.', 'mailcamp') . ' <a href="?page=wc-settings&tab=advanced">' . __('Manage WooCommerce Settings', 'mailcamp') . '</div>';
    }
}

/** @since 1.5.3 */
function mailcamp_callback_signup_custom_message()
{
    $option  = get_option('mailcamp_options_wc');
    $defaults = mailcamp_options_default();
    $value = isset($option['wc_signup_custom_message']) ? sanitize_text_field($option['wc_signup_custom_message']) : $defaults['wc_signup_custom_message'];

    echo '<input type="text" size="42" id="mailcamp_options_wc_signup_custom_message" name="mailcamp_options_wc[wc_signup_custom_message]" value="' . $value . '" />'."\t\n<br />";
}

/** @since 1.5.3 */
function mailcamp_callback_signup_checkbox_default()
{
    $option  = get_option('mailcamp_options_wc');
    $defaults = mailcamp_options_default();
    $value = isset($option['wc_signup_checkbox_default']) ? sanitize_text_field($option['wc_signup_checkbox_default']) : false;

    foreach ($defaults['wc_signup_checkbox_default'] as $label => $option_value) {
        echo '<input type="radio" id="mailcamp_options_wc_signup_checkbox_default" name="mailcamp_options_wc[wc_signup_checkbox_default]" value="' . (int) $option_value.'" ' . ((int) $option_value == $value ? 'checked="checked"' : '') . ' />' . "\t\n";
        echo '<label for="mailcamp_options_wc_signup_checkbox_default">'.$label.'</label><br>'."\t";
    }
}

/** @since 1.5.3 */
function mailcamp_callback_double_optin() {
    $option  = get_option('mailcamp_options_wc');
    $defaults = mailcamp_options_default();
    $value = isset($option['wc_signup_double_optin']) ? sanitize_text_field($option['wc_signup_double_optin']) : false;

    foreach ($defaults['wc_signup_double_optin'] as $label => $option_value) {
        echo '<label for="mailcamp_options_wc_signup_double_optin">'.$label.'</label>'."\t";
        echo '<input type="radio" id="mailcamp_options_wc_signup_double_optin" name="mailcamp_options_wc[wc_signup_double_optin]" value="' . (int) $option_value.'" ' . ((int) $option_value == $value ? 'checked="checked"' : '') . ' />' . "\t\n<br />";
    }

    if(isset($option['wc_signup_double_optin']) && (int) $option['wc_signup_double_optin'] === 1) {
        echo '<span class="status warning" style="margin-top: 1em;"><strong>' . __('Important note:', 'mailcamp') . '</strong> ' . __('When \'double opt-in\' has been enabled, please add an Autoresponder to the MailCamp list selected above using the filter under Search Options; \'Match Confirmation Status = Unconfirmed\'. The autoresponder must contain a %%confirmlink%% tag as a confirmation link.', 'mailcamp') . '</div></span>';
    }
}

/** Since 1.5.3 */
function mailcamp_callback_section_info_wc_list() {
    echo '<p>'.__('Select the MailCamp list and fields you want to synchronize with WooCommerce', 'mailcamp').'</p>';
}

/**
 * @param $args
 * @since 1.5.3
 */
function mailcamp_callback_wc_fields($args)
{
    $options = get_option('mailcamp_options_wc', mailcamp_options_default());

    if(!isset($options['wc_signup_list'])) {
        echo '<span class="status warning" style="margin-bottom: 1em;">' . __('After selecting a list above please click the \'Fetch Fields\' or \'Save Changes\' button. After this you are able to map WooCommerce field data with MailCamp fields.', 'mailcamp') . '</span><br>';
        return;
    }

    echo '<span class="status warning" style="margin-bottom: 1em;">' . __('Pay attention! In case the MailCamp field is a dropdown, radio button, date or checkbox field, it\'s value must match exactly the value from WooCommerce.', 'mailcamp') . '</span><br>';

    $fields = get_option('mailcamp_options_wc_fields', mailcamp_options_default());

    $mappings = get_option('mailcamp_options_wc')['wc_mapped_fields'];

    $fieldNames = [];
    foreach ($fields as $key => $value) {
        $fieldNames[] = ['name' => $value->name, 'fieldid' => $value->fieldid];
    }

    if(class_exists('WooCommerce' ) && class_exists('WC_Checkout' )) {
        $fields = (new WooCommerce())->checkout()->checkout_fields;
    } else {
        $fields = [];
    }

    echo '<table class="widefat striped">
    <thead>
        <tr><td>' . __('Category') . '</td><td>' . __('WooCommerce Field Name', 'mailcamp') . '</td><td>' . __('MailCamp field', 'mailcamp') . '</td></tr>
    </thead>
    <tbody>';

    if(isset($fields['billing'])) {
        echo '<tr style="background-color: #ddd;"><td style="font-weight: bold">' . __('Billing', 'mailcamp') . '</td><td></td><td></td></tr>';

        foreach ($fields['billing'] as $key => $value) {
            echo '<tr><td></td><td>' . $key . '</td><td>' . mailcamp_callback_fields_dropdown($fieldNames, $key, $mappings) . '</td>';
        }

    }

    if(isset($fields['shipping'])) {
        echo '<tr style="background-color: #ddd;"><td style="font-weight: bold">' . __('Shipping', 'mailcamp') . '</td><td></td><td></td></tr>';
        foreach ($fields['shipping'] as $key => $value) {
            echo '<tr><td></td><td>' . $key . '</td><td>' . mailcamp_callback_fields_dropdown($fieldNames, $key, $mappings) . '</td>';
        }
    }

    if(isset($fields['account']) && count($fields['account']) > 0) {
        echo '<tr style="background-color: #ddd;"><td style="font-weight: bold">' . __('Account', 'mailcamp') . '</td><td></td><td></td></tr>';
        foreach ($fields['account'] as $key => $value) {
            echo '<tr><td></td><td>' . $key . '</td><td>' . mailcamp_callback_fields_dropdown($fieldNames, $key, $mappings) . '</td>';
        }
    }

    if(isset($fields['order'])) {
        echo '<tr style="background-color: #ddd;"><td style="font-weight: bold">' . __('Order', 'mailcamp') . '</td><td></td><td></td></tr>';
        foreach ($fields['order'] as $key => $value) {
            echo '<tr><td></td><td>' . $key . '</td><td>' . mailcamp_callback_fields_dropdown($fieldNames, $key, $mappings) . '</td>';
        }
    }

    echo '</tbody></table>';
}

/** @since 1.5.3 */

function mailcamp_callback_fields_dropdown($fields = [], $field = '', $mappings = []) {

        $output = '<select name="mailcamp_options_wc[wc_mapped_fields][' . $field . ']"><option value="">' . __('Select a field', 'mailcamp') . '</option>';

        foreach($fields as $value) {

            $check   = (isset($mappings) && isset($mappings[$field]) && $mappings[$field] === $value['fieldid']);
            $select  = $check ? 'selected' : '';
            $output .= '<option id="mailcamp_options_wc_mapped_field_' . $field . '" name="mailcamp_options_wc[wc_mapped_fields][' . $field .  '][]" value="' . $value['fieldid'] . '" ' . $select . '>' . $value['name'] . '</option>';

        }
        return $output . '</select>';
}

/** @since 1.5.3 */

function mailcamp_mapped_wc_fields()
{
    return '';
}

