<?php
/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://mailcamp.nl
 * @since             1.0.0
 * @package           Mailcamp
 *
 * @wordpress-plugin
 * Plugin Name:       MailCamp
 * Plugin URI:        https://mailcamp.nl/ecommerce/koppel-wordpress-plugin-aan-mailinglijst-in-mailcamp/
 * Description:       MailCamp form plugin. A simple plugin that adds a highly effective subscription form to your site.
 * Version:           1.6.1
 * Author:            Silas de Rooy
 * Author URI:        https://mailcamp.nl
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mailcamp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined('WPINC')) {
    die;
}

/**
 * Currently pligin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('MAILCAMP_VERSION', '1.6.1');
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mailcamp-activator.php
 */
function mailcamp_activate()
{
    require_once plugin_dir_path(__FILE__).'includes/class-mailcamp-activator.php';
    Mailcamp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mailcamp-deactivator.php
 */
function mailcamp_deactivate()
{
    require_once plugin_dir_path(__FILE__).'includes/class-mailcamp-deactivator.php';
    Mailcamp_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'mailcamp_activate');
register_deactivation_hook(__FILE__, 'mailcamp_deactivate');

define('MAILCAMP_ROOT_PATH', plugin_dir_path(__FILE__));

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__).'includes/class-mailcamp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function mailcamp_run()
{

    $plugin = new Mailcamp();
    $plugin->run();

}

mailcamp_run();