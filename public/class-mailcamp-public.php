<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://mailcamp.nl
 * @since      1.0.0
 *
 * @package Mailcamp
 * @subpackage Mailcamp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package Mailcamp
 * @subpackage Mailcamp/public
 * @author Silas de Rooy <silasderooy@gmail.com>
 */
class Mailcamp_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string    $plugin_name       The name of the plugin.
	 * @param string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
        $this->addAjaxHooks();
		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mailcamp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mailcamp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mailcamp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mailcamp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mailcamp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mailcamp-public.js', array( 'jquery' ), $this->version, false );
	}

    /**
     * Setup Ajax action hook
     * @since   1.3.0
     */
	public function addAjaxHooks(){
        add_action( 'wp_ajax_nopriv_add_subscriber_to_list', [ $this, 'add_subscriber_to_list' ] );
        add_action( 'wp_ajax_add_subscriber_to_list', [ $this, 'add_subscriber_to_list' ] );
    }

    /**
     * adds the subscriber to a MailCamp list via the MailCamp API
     * @since 1.3.0
     */
	public function add_subscriber_to_list(){
		// check if terms were accepted
		if ( !isset( $_POST["CustomFields"]['accepted'] ) || isset( $_POST["CustomFields"]['accepted'] ) && $_POST["CustomFields"]['accepted'] !== 'check' ) {
			echo json_encode([ false, __('Whoops .. the subscription has failed, contact the administrator of this site', 'mailcamp')]);
			exit;
		}
		// check captcha
		$captcha_answer = (int)$_POST['captcha'];
		$captcha_val_1 = (int)$_POST['captcha_val_1'];
		$captcha_val_2 = (int)$_POST['captcha_val_2'];
		if ($captcha_answer <= 0 || ($captcha_val_1 + $captcha_val_2 !== $captcha_answer)) {
			echo json_encode([ false, __('Whoops .. the subscription has failed, contact the administrator of this site', 'mailcamp')]);
			exit;
		}
        $options = get_option( 'mailcamp_options_api' );

        $required = [ 'api_path', 'api_username', 'api_token' ];
        // only keep the required api credentials
        $api_credentials = array_intersect_key( $options, array_flip( $required ) );

        if ( count( $api_credentials ) === 3 ) {
            // All required api credentials exist!
            $mc_api         = new MailCamp_Api( $api_credentials );
            $mc_api->connection();
        }
        // connected
        if ( $mc_api->connection ) {
            // check if user agree with GDPR law
            if ( isset( $_POST["CustomFields"]['accepted'] ) && $_POST["CustomFields"]['accepted'] == 'check' ) {
                $custom_fields = $_POST["CustomFields"];
                unset( $custom_fields['accepted'] );
                // loop trough all fields
                foreach ( $custom_fields as $key => $field ) {
                    if ( $key == 'listid' ) {
                        $listid = $field;
                    }
                    // validate email address
                    if ( $key == 'email' && filter_var( $field, FILTER_VALIDATE_EMAIL ) === false ) {
                        $result = [ false, "<u><i>$field</i></u> " . __('is not a valid email address','mailcamp') ];
                        echo json_encode( $result );
                        exit;
                    }
                    // mailcamp date format
                    if ( is_string( $field ) && (bool) strtotime( $field ) ) {
                        // $custom_fields[ $key ] = date( "d-m-Y", strtotime( $field ) );
                    }
                }
                // Check if subscriber already exists
                $mc_api->getSubscriberFromList($custom_fields['email'], $custom_fields['listid']);
				$msg = (string)$mc_api->result->data;
                $insert = false;
                // we have to add this || since the MailCamp api has changed for the response on this call
                if($msg !== '' || (isset($mc_api->result->status) && $mc_api->result->status === false)){
                    // insert subscriber
                    $mc_api->insertSubscriber( $custom_fields );
                    $msg = (string)$mc_api->result->data;
                    $insert = true;
                }
                // successful insert
                if ( $mc_api->result->status && is_numeric( $msg ) ) {
                    $subscriberid = intval( $msg );
                    // fetch confirm code, we need this for creating the opt-in confirm link
                    $mc_api->loadSubscriberList( $subscriberid );
                    if ( isset( $mc_api->result->data->confirmcode ) ) {
                        if(!$insert){
                            $confirmdate = (string)$mc_api->result->data->confirmdate;
                            $requestdate = (string)$mc_api->result->data->requestdate;
                            if($confirmdate > 0){
                                // already confirmed inform user
                                $result = [ false, __('You already subscribed to this list', 'mailcamp')];
                                echo json_encode($result); exit;
                            } else {
                                $time = time();
                                // send new confirm mail
                                if(($time - $requestdate) < 30){
                                    $result = [ false, __('We just send you a confirm mail', 'mailcamp')];
                                    echo json_encode($result); exit;
                                }
                            }
                        }

                        $confirmcode = (string)$mc_api->result->data->confirmcode;
                        // the email which subscribed
                        $to_email = $custom_fields['email'];
                        // create the confirmation link
                        $url         = str_replace( '/xml.php', '', $options['api_path'] ) . '/confirm.php';
                        $url_query   = [
                            'E' => $to_email,
                            'L' => $listid,
                            'C' => $confirmcode,
                        ];
                        $confirmlink = $url . '?' . urlencode(http_build_query( $url_query ));

                        // get list details
                        $mc_api->listDetails( $listid );
                        // fetch list details
                        if ( isset( $mc_api->result->data->item ) ) {
                            $result  = $mc_api->result->data->item;
                            $details = [
                                'from_name'      => $result->ownername,
                                'from_address'   => $result->owneremail,
                                'replyto'        => $result->replytoemail,
                                'bounce_address' => $result->bounceemail,
                                'subject'        => __('Please verify your email', 'mailcamp'),
                                'confirm'        => $confirmlink,
								'subscriberid'   => $subscriberid,
								'listid'   		 => (int)$mc_api->result->data->listid,
                            ];
                        }

                        $options_form = get_option('mailcamp_options_form');
                        // if custom html is enabled, send the custom html
                        if($options_form['custom_confirm_mail_enabled'] && !empty($options_form['custom_confirm_mail'])){
	                        $html = str_replace('{{confirmlink}}', $confirmlink, $options_form['custom_confirm_mail']);
                        } else {
                            $html = '
							<html>
								<head>
									<title>'. __('Confirm your registration', 'mailcamp') .'</title>
									<style type="text/css">
									html, body {
										margin: 0;
										padding: 0;
										background-color: #efefef; 
										color: #666;
									}
									</style>
								</head>
								<body>
									<h3>'. __('Confirm your subscription', 'mailcamp') .'</h3>'.
	                                    __('<p>You are just one click away from subscribing to our newsletter. Please click on the link down below to confirm your registration.</p>', 'mailcamp').
	                                    '<p><a href="' . $confirmlink . '">'.__('Click here to confirm your subscription', 'mailcamp').'</a></p>'.
	                                    __('<p>or copy-paste the following URL into the address bar of your browser:</p>', 'mailcamp').
	                                    '<p>' . $confirmlink . '</p>'.
	                                    __('<p>Please note! Do not forget to add our e-mail address to your mailing list or safe list!</p>', 'mailcamp').
	                                    __('<p>If you did not sign up for our newsletter, please ignore this email.</p>', 'mailcamp').
	                                    '</body>
							</html>';
                        }

                        $mail_result = $mc_api->mc_mail( $to_email, $details, $html );
						$mail_result_array = json_decode(json_encode($mail_result), true);

						if (isset($mail_result_array['success']) && (int)$mail_result_array['success'] === 1) {

							$result = [
								true,
								'<table border="0" cellpadding="2" class="myForm">
							<tbody>
								<tr>
									<td>
									<h3>'.__('Your subscription is almost complete...', 'mailcamp').'</h3>
									<p>'.__('An e-mail has been sent to your address. There is a confirmation link in this e-mail. Click on this link to confirm the subscription.', 'mailcamp').'<br /></p>
									<p><strong>'.__('Please note! Do not forget to add our email address to your mailing list or secure list!','mailcamp').'</strong></p>
									</td>
								</tr>
							</tbody>
						</table>'
							];
							
						} elseif (isset($mail_result_array['fail']) && !empty($mail_result_array['fail'])) {
							
							$result = [ false, implode($mail_result_array['fail'][0], ', ')];
						} else {
							
							$result = [ false, __('Whoops .. the subscription has failed, contact the administrator of this site', 'mailcamp')];
						}

					}
                } else {
                    $result = [ $mc_api->result->status, $msg ];
                }
            }

        } else {
            $result = [ false, __('Whoops .. the subscription has failed, contact the administrator of this site', 'mailcamp')];
        }

		echo json_encode( $result ); exit;
    }

    /**
     * Adds a newsletter signup checkbox to the WooCommerce checkout page
     *
     * @since 1.5.3
     */
    public function add_wc_signup_checkbox() {

        $position = (get_option('mailcamp_options_wc') !== null &&
            isset(get_option('mailcamp_options_wc')['wc_signup_position']) &&
            get_option('mailcamp_options_wc')['wc_signup_position'] === 'woocommerce_review_order_before_submit'
        ) ? 'woocommerce_review_order_before_submit' : 'woocommerce_after_order_notes';

        add_action($position, [$this, 'do_wc_signup_checkbox']);
    }

    /**
     * @since 1.5.3
     * @updated 1.5.5
     */
    public function do_wc_signup_checkbox() {

        $checked = get_option('mailcamp_options_wc') !== null && (int) get_option('mailcamp_options_wc')['wc_signup_checkbox_default'];

        $message = (get_option('mailcamp_options_wc') !== null && isset(get_option('mailcamp_options_wc')['wc_signup_custom_message'])) ?
            get_option('mailcamp_options_wc')['wc_signup_custom_message']
            : __('I would like to receive the newsletter');

        woocommerce_form_field( 'wc_signup_checkbox', array(
            'type'	=> 'checkbox',
            'class'	=> ['checkbox-spacing' . $checked],
            'label'	=> $message,
        ), (int) $checked );
    }

    /**
     * Stores user subscription during WC checkout
     *
     * @since 1.5.4
     * @updated 1.5.5
     */
    function update_meta_wc_signup_checkbox($orderId) {

        update_post_meta($orderId, '_wc_signup_checkbox', $_POST['wc_signup_checkbox']);

        //if ($order->get_customer_id()) {
            //update_user_meta($order->get_customer_id(), 'wc_signup_checkbox', $value);
        //}
    }

    /**
     * Handles signup during WC checkout
     *
     * @since 1.5.3
     * @updated 1.5.5
     */
    public function add_wc_handle_signup($order_id) {

        if(get_option('mailcamp_options_wc') !== null && (int) get_option('mailcamp_options_wc')['wc_signup_enabled'] === 1) {

            if(isset(get_option('mailcamp_options_wc')['wc_signup_list']) && is_numeric(get_option('mailcamp_options_wc')['wc_signup_list'])) {

                $order = new WC_Order( $order_id );
                $signup = get_post_meta( $order_id, '_wc_signup_checkbox', true );
                if((int) $signup === 1) {

                    $email_adress = $order->get_billing_email();
                    $signup_list_id = get_option('mailcamp_options_wc')['wc_signup_list'];
                    $signup_fields = isset(get_option('mailcamp_options_wc')['wc_mapped_fields']) && is_array(get_option('mailcamp_options_wc')['wc_mapped_fields'])
                        ? get_option('mailcamp_options_wc')['wc_mapped_fields']
                        : [];
                    $custom_fields = ['listid' => $signup_list_id, 'email' => $email_adress];

                    foreach ($signup_fields as $key => $value) {
                        $wc_value = $this->wc_field_mappings($order)[$key];
                        if (isset($wc_value) && $value !== '') {
                            $custom_fields[$value] = $wc_value;
                        }
                    }

                    if (isset($signup_list_id) && is_numeric($signup_list_id) && isset($email_adress) && is_string($email_adress)) {
                        $this->insertOrUpdateContact($signup_list_id, $email_adress, $custom_fields);
                    }

                }

            }
        }
    }

    /** @since 1.5.3 */
    public function wc_field_mappings($order) {
        return [
            'billing_first_name' => $order->get_billing_first_name(),
            'billing_last_name' => $order->get_billing_last_name(),
            'billing_company' => $order->get_billing_company(),
            'billing_country' => $order->get_billing_country(),
            'billing_address_1' => $order->get_billing_address_1(),
            'billing_address_2' => $order->get_billing_address_2(),
            'billing_postcode' => $order->get_billing_postcode(),
            'billing_city' => $order->get_billing_city(),
            'billing_state' => $order->get_billing_state(),
            'billing_phone' => $order->get_billing_phone(),
            'billing_email' => $order->get_billing_email(),
            'shipping_first_name' => $order->get_shipping_first_name(),
            'shipping_last_name' => $order->get_shipping_last_name(),
            'shipping_company' => $order->get_shipping_company(),
            'shipping_country' => $order->get_shipping_country(),
            'shipping_address_1' => $order->get_shipping_address_1(),
            'shipping_address_2' => $order->get_shipping_address_2(),
            'shipping_city' => $order->get_shipping_city(),
            'shipping_state' => $order->get_shipping_state(),
            'shipping_postcode' => $order->get_shipping_postcode(),
            'order_comments' => $order->get_customer_note(),
        ];
    }

    /** @since 1.5.3 */
    public function insertOrUpdateContact($list_id, $email, $custom_fields) {

        $options = get_option( 'mailcamp_options_api' );
        $required = [ 'api_path', 'api_username', 'api_token' ];
        $api_credentials = array_intersect_key( $options, array_flip( $required ) );

        if ( count( $api_credentials ) === 3 ) {
            $mc_api = new MailCamp_Api( $api_credentials );
            $mc_api->connection();
        }

        // connected
        if ( $mc_api->connection) {

            $mc_api->getSubscriberFromList($email, $list_id);
            $msg = (string)$mc_api->result->data;
            $insert = false;

            // user is not subscribed yet
			if($msg !== '' || (isset($mc_api->result->status) && $mc_api->result->status === false)){

                // check for double opt-in
                if(isset(get_option('mailcamp_options_wc')['wc_signup_double_optin']) && get_option('mailcamp_options_wc')['wc_signup_double_optin'] === '0') {
                    $mc_api->insertSubscriber($custom_fields, 'yes');
                } else {
                    $mc_api->insertSubscriber($custom_fields);
                }

                $msg = (string)$mc_api->result->data;
                $insert = true;
            } else {

                // user exists, update custom fields

            }

            // successful insert
            if ( $mc_api->result->status && is_numeric( $msg ) && $insert === true ) {

            }
        }

    }

}
