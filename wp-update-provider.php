<?php
/**
* Plugin Name: WP Update Provider
* Description: Provides an update server for WordPress plugins & themes, and manages sites that are updating.
* Version: 0.0.1
* Author: Ed-IT Solutions
* Author URI: http://www.ed-itsolutions.com
**/

// If not in Wordpress die
if (!defined('WPINC')){
	die;
}

require_once plugin_dir_path(__FILE__) . 'lib/events.php';

register_activation_hook(WP_PLUGIN_DIR . '/wp-update-provider/wp-update-provider.php', 'wup_activator');
register_uninstall_hook(WP_PLUGIN_DIR . '/wp-update-provider/wp-update-provider.php', 'wup_deactivator');

require_once plugin_dir_path(__FILE__) . 'lib/class.php';

function wup_run_plugin(){
	$plugin = new WPUpdateProvider();
	$plugin->run();
}

wup_run_plugin();

?>