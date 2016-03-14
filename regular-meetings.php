<?php
/*
 * Plugin Name:       Regular Meetings
 * Plugin URI:        
 * Description:       Stores details of regular meetings or events and displays a list of them via a shortcode
 * Version:           0.2.0
 * Author:            Daniel Scott	
 * Author URI:        http://plugins.drdscott.com
 * Text Domain:       regular-meetings-text-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}


// turn on error checking...
ini_set('display_errors',1);
error_reporting(E_ALL);


require_once plugin_dir_path( __FILE__ ) . 'includes/class-regular-meetings-plugin.php';

	$dsrm = new DS_Regular_Meetings_Plugin();
	$dsrm->run();

