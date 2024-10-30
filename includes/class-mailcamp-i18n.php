<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://mailcamp.nl
 * @since      1.0.0
 *
 * @package Mailcamp
 * @subpackage Mailcamp/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package Mailcamp
 * @subpackage Mailcamp/includes
 * @author Silas de Rooy <silasderooy@gmail.com>
 */
class Mailcamp_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'mailcamp',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
