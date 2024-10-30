<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://mailcamp.nl
 * @since      1.0.0
 *
 * @package Mailcamp
 * @subpackage Mailcamp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package Mailcamp
 * @subpackage Mailcamp/admin
 * @author Silas de Rooy <silasderooy@gmail.com>
 */
class Mailcamp_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version The current version of this plugin.
	 */
	private $version;

    /**
     * Mailcamp_Admin constructor.
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		if ( is_admin() ) {

			// include dependencies
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings-callbacks.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings-validate.php';

		}
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mailcamp-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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


		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mailcamp-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add submenu settings page
     *
     * @since 1.0.0
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'MailCamp for WP Settings', 'mailcamp' ),
			__( 'MailCamp for WP Settings', 'mailcamp' ),
			'manage_options',
			'mailcamp',
			[ $this, 'display_settings_api' ],
			plugin_dir_url( __FILE__ ) . '/img/favicon.png',
			'99.68491'
		);
		// add submenu form page
		add_submenu_page(
			'mailcamp',
			__( 'Form Settings', 'mailcamp' ),
			__( 'MailCamp Form', 'mailcamp' ),
			'manage_options',
			'mailcamp-form',
			[ $this, 'display_settings_form' ]
		);
		// add submenu rss page
		add_submenu_page(
			'mailcamp',
			__( 'RSS Settings', 'mailcamp' ),
			__( 'MailCamp RSS', 'mailcamp' ),
			'manage_options',
			'mailcamp-rss',
			[ $this, 'display_settings_rss' ]
		);
        // add submenu woocommerce page
        add_submenu_page(
            'mailcamp',
            __( 'WooCommerce Settings', 'mailcamp' ),
            __( 'MailCamp for WooCommerce', 'mailcamp' ),
            'manage_options',
            'mailcamp-wc',
            [ $this, 'display_settings_wc' ]
        );
	}

	/**
	 * display the plugin settings page
     * @since 1.0.0
	 */
	public function display_settings_api() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}


		// add error/update messages

		// check if the user have submitted the settings
		// wordpress will add the "settings-updated" $_GET parameter to the url
//        if (isset($_GET['settings-updated'])) {
		// add settings saved message with the class of "updated"
//            add_settings_error('mailcamp_messages', 'mailcamp_message', __('Settings Saved', 'mailcamp'), 'updated');
//        }

		// show error/update messages
//        settings_errors('mailcamp_messages');
		?>
        <div class="wrap">
            <h1><?= esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
				<?php
				// output security fields for the registered setting "wporg"
				settings_fields( 'mailcamp_options_api' );
				// output setting sections and their fields
				do_settings_sections( 'mailcamp' );
				// output save settings button
				submit_button();
				?>
            </form>
        </div>
		<?php
	}

    /**
     * @since 1.0.0
     */
	public function register_settings_api() {

		$options  = get_option( 'mailcamp_options_api' ) ?: [];
		$required = [ 'api_path', 'api_username', 'api_token' ];
		// only keep the required api credentials
		$api_credentials = array_intersect_key( $options, array_flip( $required ) );
		$api_credentials = array_filter( $api_credentials );

		if ( count( $api_credentials ) === 3 ) {
			// All required api credentials exist!
			$mc_api         = new MailCamp_Api( $api_credentials );
			$api_connection = $mc_api->connection();
		} else {
			$api_connection         = new stdClass();
			$api_connection->status = false;
			$api_connection->data   = __( 'Check API Credentials', 'mailcamp' );
		}
        $connection_status = false;
		if ( $api_connection->status === true ) {
		    $connection_status = true;
			// Wordpress and MailCamp are connected through MailCamp API
			$lists        = $mc_api->lists();
			$option_lists = [];
			if(!empty($lists->item)){
                foreach ( $lists->item as $key => $list ) {
					$option_lists[] = [
						'listid'           => intval( $list->listid ),
						'listname'         => htmlentities( $list->name ),
						'shortcode'        => 'mailcamp_list_' . intval( $list->listid ),
						'subscribecount'   => (int)$list->subscribecount,
						'unsubscribecount' => (int)$list->unsubscribecount,
						'bouncecount'      => (int)$list->bouncecount,
						'spf'              => (int)$list->spf,
						'dkim'             => (int)$list->dkim,
                    ];
                }
                update_option( 'mailcamp_options_lists', $option_lists );
            }
		}

		register_setting(
			'mailcamp_options_api',
			'mailcamp_options_api',
			'mailcamp_callback_validate_options'
		);


		add_settings_section(
			'section_api_details',
			__( 'MailCamp API Connection details', 'mailcamp' ),
			'mailcamp_callback_section_info_apicredentials',
			'mailcamp'
		);

		add_settings_field(
			'api_status',
			__( 'API status', 'mailcamp' ),
			'mailcamp_callback_field_api_status',
			'mailcamp',
			'section_api_details',
			[
				'status' => $connection_status,
				'info'   => $api_connection->data,
			]
		);

		add_settings_field(
			'api_path',
			__( 'API path', 'mailcamp' ),
			'mailcamp_callback_field_text',
			'mailcamp',
			'section_api_details',
			[
				'id'    => 'api_path',
				'label' => __( 'The XML path looks something like this: http://www.yourdomain.com/mailcamp/xml.php', 'mailcamp' ),
				'page'  => 'mailcamp_options_api',
			]
		);

		add_settings_field(
			'api_username',
			__( 'API username', 'mailcamp' ),
			'mailcamp_callback_field_text',
			'mailcamp',
			'section_api_details',
			[
				'id'    => 'api_username',
				'label' => __( 'The \'Username\' can be found in the same section respectively under the title \'XML Username\'.', 'mailcamp' ),
				'page'  => 'mailcamp_options_api',
			]
		);

		add_settings_field(
			'api_token',
			__( 'API token', 'mailcamp' ),
			'mailcamp_callback_field_text',
			'mailcamp',
			'section_api_details',
			[
				'id'    => 'api_token',
				'label' => __( 'The \'usertoken\' can be found in the same section respectively under the title \'XML token\'.', 'mailcamp' ),
				'page'  => 'mailcamp_options_api',
				'type'  => 'password'
			]
		);

	}

	/**
	 * display the plugin settings page
     * @since 1.0.0
	 */
	public function display_settings_form() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}


		// add error/update messages

		// check if the user have submitted the settings
		// wordpress will add the "settings-updated" $_GET parameter to the url
//        if (isset($_GET['settings-updated'])) {
		// add settings saved message with the class of "updated"
//            add_settings_error('mailcamp_messages', 'mailcamp_message', __('Settings Saved', 'mailcamp'), 'updated');
//        }

		// show error/update messages
//        settings_errors('mailcamp_messages');
		?>
        <div class="wrap">
            <h1><?= esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
				<?php
				// output security fields for the registered setting "wporg"
				settings_fields( 'mailcamp_options_form' );
				// output setting sections and their fields
				do_settings_sections( 'mailcamp-form' );
				// output save settings button
				submit_button();
				?>
            </form>
        </div>
		<?php
	}

    /**
     * @since 1.0.0
     */
	public function register_settings_form() {

		$options_api       = get_option( 'mailcamp_options_api' ) ?: [];
		$options_form      = get_option( 'mailcamp_options_form' ) ?: [];
		$options_shortcode = get_option( 'mailcamp_options_shortcode' ) ?: [];

		$options = array_merge( $options_api, $options_form );

		$required_list_details = [ 'mailcamp_lists', 'mailcamp_list_fields' ];
		$list_details          = array_intersect_key( $options_form, array_flip( $required_list_details ) );
		// create shortcode for this list if only the form is created
		$shortcode = ( ! empty( $options_form ) ? 'mailcamp_form_' . substr( md5( serialize( $list_details ) ), 0, 10 ) : '' );
		// save shortcode if the form is created and the short code is not saved yet
		if ( ! in_array( $shortcode, $options_shortcode ) && ! empty( $shortcode ) ) {
			$options_shortcode = array_merge( $options_shortcode, [ $shortcode ] );
			update_option( 'mailcamp_options_shortcode', $options_shortcode );
		}

		$required_credentials = [ 'api_path', 'api_username', 'api_token' ];
		// only keep the required api credentials
		$api_credentials = array_intersect_key( $options, array_flip( $required_credentials ) );

		if ( count( $api_credentials ) === 3 ) {
			// All required api credentials exist!
			$mc_api         = new MailCamp_Api( $api_credentials );
			$api_connection = $mc_api->connection();
		} else {
			$api_connection         = new stdClass();
			$api_connection->status = false;
			$api_connection->data   = __( 'Check API credentials', 'mailcamp' );
		}

		register_setting(
			'mailcamp_options_form',
			'mailcamp_options_form',
			'mailcamp_callback_validate_options'
		);

		add_settings_section(
			'section_form_settings',
			__( 'MailCamp Form details', 'mailcamp' ),
			'mailcamp_callback_section_info_form_settings',
			'mailcamp-form'
		);

		add_settings_field(
			'mailcamp_field_shortcode',
			__( 'Shortcode', 'mailcamp' ),
			'mailcamp_callback_field_shortcode',
			'mailcamp-form',
			'section_form_settings',
			[
				'id'        => 'mailcamp_field_shortcode',
				'shortcode' => $shortcode,
				'label'     => '<p>' . __( 'Use the shortcode above to place this form on your site', 'mailcamp' ) . '</p>',
				'page'      => 'mailcamp_options_form',
			]
		);

		add_settings_field(
			'mailcamp_field_email',
			__( 'Email Label', 'mailcamp' ),
			'mailcamp_callback_field_text',
			'mailcamp-form',
			'section_form_settings',
			[
				'id'    => 'mailcamp_field_email',
				'label' => '<p>' . __( 'Field name for the email field. eg \'email\' or \'email address\'', 'mailcamp' ) . '</p>',
				'page'  => 'mailcamp_options_form',
			]
		);

		add_settings_field(
			'mailcamp_field_submit',
			__( 'Submit Label', 'mailcamp' ),
			'mailcamp_callback_field_text',
			'mailcamp-form',
			'section_form_settings',
			[
				'id'    => 'mailcamp_field_submit',
				'label' => '<p>' . __( 'Name for the submit button. eg inschrijven or subscribe', 'mailcamp' ) . '</p>',
				'page'  => 'mailcamp_options_form',
			]
		);

		add_settings_section(
			'section_gdpr_info',
			__( 'GDPR / AVG Legislation Requirements', 'mailcamp' ),
			'mailcamp_callback_section_info_gdpr',
			'mailcamp-form'
		);

		add_settings_field(
			'gdpr_checkbox',
			__( 'GDPR checkbox text', 'mailcamp' ),
			'mailcamp_callback_field_text',
			'mailcamp-form',
			'section_gdpr_info',
			[
				'id'    => 'gdpr_checkbox_text',
				'label' => '<p>' . __( 'Short description about validating the GDPR Privacy Policy. <br /><strong>Optional: </strong>U can use <code>{{content}}</code> as an anchor that links to the GDPR url.', 'mailcamp' ) . '</p>',
				'page'  => 'mailcamp_options_form',
			]
		);

		add_settings_field(
			'widget_gdpr_url',
			__( 'GDPR url', 'mailcamp' ),
			'mailcamp_callback_field_text',
			'mailcamp-form',
			'section_gdpr_info',
			[
				'id'    => 'widget_gdpr_url',
				'label' => '<p>' . __( 'The url used in the widget form or/and checkbox text', 'mailcamp' ) . '</p>',
				'page'  => 'mailcamp_options_form',
			]
		);

		add_settings_field(
			'gdpr_description',
			__( 'GDPR description', 'mailcamp' ),
			'mailcamp_callback_field_wysiwyg',
			'mailcamp-form',
			'section_gdpr_info',
			[
				'id'    => 'gdpr_description',
				'label' => '<p><strong> ' . __( 'More info', 'mailcamp' ) . ' > </strong><a href="https://www.mailcamp.nl/2017/12/04/nieuwe-europese-privacy-wetgeving/" target="_blank">https://www.mailcamp.nl/2017/12/04/nieuwe-europese-privacy-wetgeving/</a></p>',
				'page'  => 'mailcamp_options_form',
			]
		);

		if ( $api_connection->status === true ) {
			add_settings_section(
				'section_api_connection',
				__( 'Form Settings', 'mailcamp' ),
				'mailcamp_callback_section_info_get_lists',
				'mailcamp-form'
			);

			$lists = $mc_api->lists();

			add_settings_field(
				'mailcamp_lists',
				__( 'List', 'mailcamp' ),
				'mailcamp_callback_field_lists',
				'mailcamp-form',
				'section_api_connection',
				[
					'id'       => 'mailcamp_lists',
					'label'    => '<p>' . __( 'Select a list', 'mailcamp' ) . '</p>',
					'mc_lists' => $lists,
					'page'     => 'mailcamp_options_form',
				]
			);

			if ( isset( $options['mailcamp_lists'] ) && ! empty( $options['mailcamp_lists'] ) ) {

				$listid      = $options['mailcamp_lists'];
				$list_fields = $mc_api->fields( $listid );
				if ( $mc_api->result->status && ! empty( $list_fields ) ) {
					$list_fields = json_decode( json_encode( $list_fields ) )->item;
				} else {
					$list_fields = [];
				}

				add_settings_field(
					'mailcamp_lists_fields',
					__( 'Extra fields', 'mailcamp' ),
					'mailcamp_callback_field_lists_fields',
					'mailcamp-form',
					'section_api_connection',
					[
						'id'          => 'mailcamp_list_fields',
						'label'       => '<p>' . __( 'Select the additional fields you want to use on the left. The selected fields appear on the right.', 'mailcamp' ) . '</p>',
						'list_fields' => $list_fields,
						'page'        => 'mailcamp_options_form',
					]
				);

			}

			add_settings_field(
				'custom_confirm_mail_enabled',
				__( 'Custom Confirm enabled', 'mailcamp' ),
				'mailcamp_callback_field_radio',
				'mailcamp-form',
				'section_api_connection',
				[
					'id'     => 'custom_confirm_mail_enabled',
					'page'   => 'mailcamp_options_form',
				]
			);

			add_settings_field(
				'custom_confirm_mail',
				__( 'Custom Confirm Mail', 'mailcamp' ),
				'mailcamp_callback_field_wysiwyg',
				'mailcamp-form',
				'section_api_connection',
				[
					'id'    => 'custom_confirm_mail',
					'label' => '<p>' . __( 'Insert your HTML here, use <code>{{confirmlink}}</code> on the place where you want to use the confirmlink', 'mailcamp' ) . '</p>',
					'page'  => 'mailcamp_options_form',
				]
			);
		}

	}

	/**
	 * display the plugin rss page
     * @since 1.0.0
	 */
	public function display_settings_rss() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}//		$mailcamp_lists = get_option( 'mailcamp_options_lists' ) ?: [];

		$options_api          = get_option( 'mailcamp_options_api' ) ?: [];
		$required_credentials = [ 'api_path', 'api_username', 'api_token' ];
		// only keep the required api credentials
		$api_credentials = array_intersect_key( $options_api, array_flip( $required_credentials ) );

		if ( count( $api_credentials ) === 3 ) {
			// All required api credentials exist!
			$mc_api         = new MailCamp_Api( $api_credentials );
			$api_connection = $mc_api->connection();
		} else {
			$api_connection         = new stdClass();
			$api_connection->status = false;
			$api_connection->data   = __( 'Check API credentials', 'mailcamp' );
		}

		?>
        <div class="wrap">
            <h1><?= esc_html( get_admin_page_title() ); ?></h1>

			<?php
			if ( $api_connection->status === true ) {
				// Wordpress and MailCamp are connected through MailCamp API
				$lists        = $mc_api->lists();
				$option_lists = [];
				foreach ( $lists->item as $key => $list ) {
					$option_lists[] = [
						'listid'           => intval( $list->listid ),
						'listname'         => htmlentities( $list->name ),
						'shortcode'        => 'mailcamp_list_' . intval( $list->listid ),
						'subscribecount'   => (int)$list->subscribecount,
						'unsubscribecount' => (int)$list->unsubscribecount,
						'bouncecount'      => (int)$list->bouncecount,
						'spf'              => (int)$list->spf,
						'dkim'             => (int)$list->dkim,
					];
				}
				update_option( 'mailcamp_options_lists', $option_lists );

				$mailcamp_lists = get_option( 'mailcamp_options_lists' ) ?: [];; ?>
                <div class="mc4wp-lists-overview">
                    <p><?php echo sprintf( __( '%s lists found', 'mailcamp' ), count( $mailcamp_lists ) ); ?></p>
                    <table class="widefat striped">
                        <thead>
                        <tr>
                            <th><?php echo __( 'listid', 'mailcamp' ); ?></th>
                            <th><?php echo __( 'listname', 'mailcamp' ); ?></th>
                            <th><?php echo __( 'shortcode', 'mailcamp' ); ?></th>
                            <th></th>
                            <th><?php echo __( 'subscribecount', 'mailcamp' ); ?></th>
                            <th><?php echo __( 'unsubscribecount', 'mailcamp' ); ?></th>
                            <th><?php echo __( 'bouncecount', 'mailcamp' ); ?></th>
                            <th><?php echo __( 'spf', 'mailcamp' ); ?></th>
                            <th><?php echo __( 'dkim', 'mailcamp' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						if ( count( $mailcamp_lists ) > 0 ) {
							foreach ( $mailcamp_lists as $listid => $list ) {
								; ?>
                                <tr>
                                    <td><?php echo $list['listid']; ?></a><span class="row-actions alignright"></span>
                                    </td>
                                    <td><?php echo $list['listname']; ?></a><span class="row-actions alignright"></span>
                                    </td>
                                    <td><code><?php echo mailcamp_print_shortcode( $list['shortcode'] ); ?></code>&nbsp;&nbsp;
                                    </td>
                                    <td><button
                                                class="button button-primary"
                                                data-copy="<?php echo mailcamp_print_shortcode( $list['shortcode'] ); ?>"><?php echo __( 'Copy Shortcode', 'mailcamp' ); ?></button></td>
                                    <td><?php echo $list['subscribecount']; ?></a><span
                                                class="row-actions alignright"></span></td>
                                    <td><?php echo $list['unsubscribecount']; ?></a><span
                                                class="row-actions alignright"></span></td>
                                    <td><?php echo $list['bouncecount']; ?></a><span
                                                class="row-actions alignright"></span>
                                    </td>
                                    <td><?php echo $list['spf']; ?></a><span class="row-actions alignright"></span></td>
                                    <td><?php echo $list['dkim']; ?></a><span class="row-actions alignright"></span>
                                    </td>
                                </tr>
								<?php
							}
						}; ?>

                        </tbody>
                    </table>
                </div>
				<?php
			}
			?>
        </div>
		<?php
	}

    /**
     * @since 1.0.0
     */
	public function register_settings_rss() {

		$options_api    = get_option( 'mailcamp_options_api' ) ?: [];
		$mailcamp_lists = get_option( 'mailcamp_options_lists' ) ?: [];

		$options              = array_merge( $options_api, $mailcamp_lists );
		$required_credentials = [ 'api_path', 'api_username', 'api_token' ];
		// only keep the required api credentials
		$api_credentials = array_intersect_key( $options, array_flip( $required_credentials ) );

		if ( count( $api_credentials ) === 3 ) {
			// All required api credentials exist!
			$mc_api         = new MailCamp_Api( $api_credentials );
			$api_connection = $mc_api->connection();
		} else {
			$api_connection         = new stdClass();
			$api_connection->status = false;
			$api_connection->data   = __( 'Check API credentials', 'mailcamp' );
		}

		if ( $api_connection->status === true ) {
			// Wordpress and MailCamp are connected through MailCamp API
			register_setting(
				'mailcamp_options_lists',
				'mailcamp_options_lists',
				'mailcamp_callback_validate_options'
			);

			add_settings_section(
				'section_rss_settings',
				__( 'MailCamp Form details', 'mailcamp' ),
				'mailcamp_callback_section_info_rss_settings',
				'mailcamp-rss'
			);

			foreach ( $mailcamp_lists as $listid => $list ) {
				add_settings_field(
					'mailcamp_field_rss_lists' . $listid,
					$list['listname'],
					'mailcamp_callback_field_shortcode',
					'mailcamp-rss',
					'section_rss_settings',
					[
						'id'        => 'mailcamp_field_rss_lists' . $listid,
						'shortcode' => $list['shortcode'],
						'listid'    => $listid,
						'label'     => '<p>' . __( 'Use the shortcode above to place this RSS feed on your site', 'mailcamp' ) . '</p>',
						'page'      => 'mailcamp_options_lists',
					]
				);
			}
		}
	}

    /**
     * display the plugin settings page
     * @since 1.0.0
     */
    public function display_settings_wc()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <?php settings_errors();?>
            <h1><?= esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'mailcamp_options_wc' );
                // output setting sections and their fields
                do_settings_sections( 'mailcamp-wc' );
                // output save settings button
                submit_button();
                ?>
            </form>

        </div>
        <?php
    }

	/** Since 1.5.3 */
	public function register_settings_wc() {

        add_settings_section(
            'section_wc_settings',
            __( 'MailCamp for Woocommerce', 'mailcamp' ),
            'mailcamp_callback_section_info_wc',
            'mailcamp-wc'
        );

        register_setting(
            'mailcamp_options_wc',
            'mailcamp_options_wc',
            'mailcamp_callback_validate_options'
        );

        add_settings_field(
            'wc_signup_enabled',
            __( 'WooCommerce integration enabled', 'mailcamp' ),
            'mailcamp_callback_wc_enabled',
            'mailcamp-wc',
            'section_wc_settings'
        );

        if(get_option('mailcamp_options_wc') !== null &&
            isset(get_option('mailcamp_options_wc')['wc_signup_enabled']) &&
            (int) get_option('mailcamp_options_wc')['wc_signup_enabled'] === 1
        ) {
            add_settings_field(
                'wc_signup_position',
                __('Checkbox layout position', 'mailcamp'),
                'mailcamp_callback_wc_signup_position',
                'mailcamp-wc',
                'section_wc_settings'
            );

            add_settings_field(
                'wc_signup_checkbox_default',
                __('Checkbox default value', 'mailcamp'),
                'mailcamp_callback_signup_checkbox_default',
                'mailcamp-wc',
                'section_wc_settings'
            );

            add_settings_field(
                'wc_signup_custom_message',
                __( 'Custom signup message', 'mailcamp' ),
                'mailcamp_callback_signup_custom_message',
                'mailcamp-wc',
                'section_wc_settings',
                [
                    'id'    => 'wc_signup_custom_message',
                    'page'  => 'mailcamp_options_wc',
                ]
            );

            // check for API connection
            $options_api    = get_option('mailcamp_options_api') ?: [];
            $mailcamp_lists = get_option('mailcamp_options_lists') ?: [];
            $mailcamp_wc    = get_option('mailcamp_options_wc');
            $options              = array_merge( $options_api, $mailcamp_lists );
            $required_credentials = [ 'api_path', 'api_username', 'api_token' ];
            $api_credentials = array_intersect_key( $options, array_flip( $required_credentials ) );

            if ( count( $api_credentials ) === 3 ) {
                // All required api credentials exist!
                $mc_api         = new MailCamp_Api( $api_credentials );
                $api_connection = $mc_api->connection();
            } else {
                $api_connection         = new stdClass();
                $api_connection->status = false;
                $api_connection->data   = __( 'Check API credentials', 'mailcamp' );
            }

            if ( $api_connection->status === true ) {

                add_settings_section(
                    'section_wc_lists',
                    __('List settings', 'mailcamp'),
                    'mailcamp_callback_section_info_wc_list',
                    'mailcamp-wc'
                );

                register_setting(
                    'mailcamp_options_wc_fields',
                    'mailcamp_options_wc_fields',
                    'mailcamp_callback_validate_options'
                );

                $lists = $mc_api->lists();

                add_settings_field(
                    'wc_signup_list',
                    __( 'List', 'mailcamp' ),
                    'mailcamp_callback_field_lists',
                    'mailcamp-wc',
                    'section_wc_lists',
                    [
                        'id'       => 'wc_signup_list',
                        'label'    => '<p>' . __( 'Select a list', 'mailcamp' ) . '</p>',
                        'mc_lists' => $lists,
                        'page'     => 'mailcamp_options_wc',
                    ]
                );

                if ( $mailcamp_wc !== null && isset($mailcamp_wc['wc_signup_list']) && is_numeric($mailcamp_wc['wc_signup_list'])) {

                    $listid = $mailcamp_wc['wc_signup_list'];
                    $list_fields = $mc_api->fields($listid);

                    if ($mc_api->result->status && !empty($list_fields)) {

                        $list_fields = json_decode(json_encode($list_fields))->item;

                    } else {
                        $list_fields = [];
                    }

                    update_option('mailcamp_options_wc_fields', $list_fields);

                }

                add_settings_field(
                    'wc_signup_double_optin',
                    __('Double opt-in subscribers', 'mailcamp'),
                    'mailcamp_callback_double_optin',
                    'mailcamp-wc',
                    'section_wc_lists'
                );

                add_settings_field(
                    'mailcamp_lists_fields',
                    __( 'Mapped WooCommerce fields', 'mailcamp' ),
                    'mailcamp_callback_wc_fields',
                    'mailcamp-wc',
                    'section_wc_lists',
                    [
                        'id'          => 'wc_signup_fields',
                        'label'       => '<p>' . __( 'Select the WooCommerce fields you want to map on the left. Select the MailCamp fields on the right.', 'mailcamp' ) . '</p>',
                        'list_fields' => $list_fields ?? [],
                        'page'        => 'mailcamp_options_wc',
                    ]
                );

                add_settings_field(
                    'wc_mapped_fields',
                    __( 'Mapped WooCommerce fields', 'mailcamp' ),
                    'mailcamp_mapped_wc_fields',
                    'mailcamp-wc',
                    'section_wc_settings'
                );

            }
        }

    }
}
