# MailCamp #
Contributors: mailcamp
Donate link: https://mailcamp.nl/contact/
Tags: mailcamp,email marketing,email,newsletter,marketing
Requires at least: 4.9.1
Tested up to: 6.6
Stable tag: 1.6.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Short Description ##
Quickly add a MailCamp signup form to your WordPress site to enhance your email marketing efforts.

## Description ##

### Use the MailCamp plugin to quickly add a MailCamp signup form shortcode to your WordPress 4.9.8 or higher site. ###
After installation, log in with your API credentials, select your MailCamp list, fetch the list fields, copy the shortcode and add it to your site. Simply set up in 5 minutes.

WordPress.com compatibility is limited to Business tier users only. [Connect your MailCamp list to your wordpress site](https://mailcamp.nl/ecommerce/koppel-wordpress-plugin-aan-mailinglijst-in-mailcamp/).

## Installation ##

This section describes how to install the plugin and get started using it.

### Version 1.6.0 ###
1. Unzip our archive and upload the entire mailcamp directory to your `/wp-content/plugins/ directory`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Navigate to **MailCamp Settings** click **MailCamp Settings**.
4. Enter your MailCamp API credentials and let the plugin verify it.
5. Select the list where you want to send new MailCamp subscribers.
6. Optional: add newsletter RSS feeds to your website

## Changelog ##

### 1.6.1 ###
* improved - reduced api calls

### 1.6.0 ###
* improved - reduced api calls

### 1.5.9 ###
* improved - changed labels etc for better WCAG 2.1 compliancy

### 1.5.8 ###
* improved - RSS archive performance improvement

### 1.5.7 ###
* bugfix - fixed PHP 8.1 notice and warning

### 1.5.6 ###
* bugfix - fixed newsletter archive via updated RSS link

### 1.5.5 ###
* bugfix - custom number field crashes form
* bugfix - WooCommerce signup bug
* improved - Woocommerce settings UI
* improved - updated translations

### 1.5.4 ###
* improved - improved WooCommerce integration

### 1.5.3 ###
* improved - added WooCommerce integration
* improved - updated translations

### 1.5.2 ###
* bugfix - field orders

### 1.5.1 ###
* fixed notice - undefined $anchor

### 1.5.0 ###
* improved - doing no more api calls on public page load when form is not used

### 1.4.3 ###
* bugfix - translation

### 1.4.2 ###
* changed - showing custom fields in widget extra fix

### 1.4.1 ###
* improved - custom confirm mail

### 1.4.0 ###
* changed - showing custom fields in widget

### 1.3.9 ###
* bugfix - fixed confirm mail not receiving

### 1.3.8 ###
* bugfix - fixed custom confirm mail

### 1.3.7 ###
* bugfix - undefined variable notice fixed
* changed - changed some translations

### 1.3.6 ###
* bugfix - api connection issue solved
* bugfix - added rule when api connection failed to prevent error

### 1.3.5 ###
* bugfix - some field values where seen as date fields, what caused problem with adding the subscriber

### 1.3.4 ###
* bugfix - MailCamp changed the IsSubscriberOnList response, we change this plugin to stay compatible with the MailCamp API

### 1.3.3 ###
* fixed typo - in plugin description

### 1.3.2 ###
* bugfix - typo in fetching newsletter rss data

### 1.3.1 ###
* fixed issue - Generic function (and/or define) names
* fixed issue - Calling core loading files directly
* fixed issue - Undocumented use of a 3rd Party or external service

### 1.3.0 ###
* bugfix - changed custom ajax flow to the wordpress way

### 1.2.3 ###
* bugfix - insertSubscriber: add to autoresponder

### 1.0 ###
* developed - plugin created

## Internationalization (i18n) ##
Currently we have the plugin configured so it can be easily translated and the following languages supported:

* nl_NL - Dutch (thanks to [Silas de Rooy](https://mailcamp.nl/) for contributing)

If your language is not listed above, feel free to create a translation. Here are the basic steps:

1. Copy "mailcamp.po" to "mailcamp-LANG_COUNTRY.po" - fill in LANG and COUNTRY with whatever you use for WPLANG in wp-config.php
2. Grab a translation editor. [POedit](http://www.poedit.net/) works for us
3. Translate each line - if you need some context, you can ask us by mail at support@mailcamp.nl
4. Add the appropriately named files to the /po/ directory and edit the /readme.txt to include how you'd like to be attributed
5. Send us your files to support@mailcamp.nl or the developer silas@mailcamp.nl

## Screenshots ##

1. When you have successfully installed the plugin
2. MailCamp API Connection details example
3. Select the list you want to use for your form
4. Select the field you want from the list you just selected
5. Copy the shortcode and paste it in your site
6. Example form on your site
7. RSS list
