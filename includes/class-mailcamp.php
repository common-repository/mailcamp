<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://mailcamp.nl
 * @since      1.0.0
 *
 * @package Mailcamp
 * @subpackage Mailcamp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package Mailcamp
 * @subpackage Mailcamp/includes
 * @author Silas de Rooy <silasderooy@gmail.com>
 */
class Mailcamp {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since 1.0.0
	 * @access   protected
	 * @var      Mailcamp_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0.0
	 * @access   protected
	 * @var string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since 1.0.0
	 * @access   protected
	 * @var string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( defined( 'MAILCAMP_VERSION' ) ) {
			$this->version = MAILCAMP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'mailcamp';
		define('MAILCAMP_PLUGIN_NAME', $this->plugin_name);

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Mailcamp_Loader. Orchestrates the hooks of the plugin.
	 * - Mailcamp_i18n. Defines internationalization functionality.
	 * - Mailcamp_Admin. Defines all hooks for the admin area.
	 * - Mailcamp_Public. Defines all hooks for the public side of the site.
	 * - Mailcamp Core Functions. Defines all functions that occur in the admin area and public-facing
	 * sides of the site
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mailcamp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mailcamp-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mailcamp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-mailcamp-public.php';

		/**
		 * The file responsible for defining all functions that occur in the admin area and public-facing
		 * sides of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core-functions.php';

		/**
		 * The class defines all api calls that are necessary to let this plugin communicate with the MailCamp software.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mailcamp-api.php';

		/**
		 * The class defines all widget calls that are necessary to let this plugin create a widget.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mailcamp-widget.php';

		$this->loader = new Mailcamp_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Mailcamp_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function set_locale() {

		$plugin_i18n = new Mailcamp_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
    private function define_admin_hooks() {
        $plugin_admin = new Mailcamp_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');

        // Define hooks
        $hooks = [
            'mailcamp' => 'register_settings_api',
            'mailcamp-form' => 'register_settings_form',
            'mailcamp-rss' => 'register_settings_rss',
            'mailcamp-wc' => 'register_settings_wc',
            'mailcamp_options_api' => 'register_settings_api',
            'mailcamp_options_form' => 'register_settings_form',
            'mailcamp_options_lists' => 'register_settings_rss',
            'mailcamp_options_shortcode' => 'register_settings_shortcode',
            'mailcamp_options_wc' => 'register_settings_wc',
        ];

        // Allowed option pages
        $allowed_option_pages = [
            'mailcamp_options_api',
            'mailcamp_options_form',
            'mailcamp_options_lists',
            'mailcamp_options_shortcode',
            'mailcamp_options_wc',
        ];

        // Get parameters
        $page = $_GET['page'] ?? '';
        $option_page = $_POST['option_page'] ?? '';
        $current_url = $_SERVER['REQUEST_URI'] ?? '';

        // Register hooks if conditions are met
        if (strpos($page, 'mailcamp') !== false || in_array($option_page, $allowed_option_pages)) {
            if (isset($hooks[$page])) {
                $this->loader->add_action('admin_init', $plugin_admin, $hooks[$page]);
            }

            if (isset($hooks[$option_page])) {
                $this->loader->add_action('admin_init', $plugin_admin, $hooks[$option_page]);
            }
        }

        $plugin_widget = new MailCamp_Widget();
    }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_public_hooks() {

		$plugin_public = new Mailcamp_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		/**
         * WooCommerce support
         *
         * @since 1.5.3
         * @changed 1.5.4
         */
        if(get_option('mailcamp_options_wc') !== null &&
            isset(get_option('mailcamp_options_wc')['wc_signup_enabled']) &&
            (int) get_option('mailcamp_options_wc')['wc_signup_enabled'] === 1
        ) {
            $this->loader->add_action( 'woocommerce_init', $plugin_public, 'add_wc_signup_checkbox' );

            $this->loader->add_action('woocommerce_before_thankyou', $plugin_public, 'add_wc_handle_signup');

            $this->loader->add_action('woocommerce_checkout_update_order_meta', $plugin_public, 'update_meta_wc_signup_checkbox');
        }

		$plugin_widget = new MailCamp_Widget();

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Mailcamp_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
