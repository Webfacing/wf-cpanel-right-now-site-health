<?php
namespace WebFacing\cPanel;

/**
 * cPanel Information Right Now
 *
 * @package         	WebFacingcPanelPlugin
 * @author          	knutsp <knut@sparhell.no>
 * @copyright       	© 2020 Knut Sparhell, Nettvendt/IT-ing Sparhell, Norway
 * @license         	GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:     	WebFacing - Disk Usage from cPanel® on Dashboard & in Site Health 
 * Description:     	🕸️ By WebFacing. Shows your disk usage information, used and max allowed, plus alerts, in your admin Dashboard At a Glance widget and as test and info in Site Health tabs, including space used by your media uploads and cPanel® mail accounts. Also adds a HTTPS only test to Site Health. Made with help from PRO ISP and cPanel®.
 * Plugin URI:      	https://webfacing.eu/
 * Version:         	2.0
 * Author:          	Knut Sparhell
 * Author URI:      	https://profiles.wordpress.org/knutsp/
 * License:         	GPL v2 or later
 * License URI:     	https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP:    	7.4
 * Requires at least:   5.4.2
 * Tested up to:    	5.5.1
 * Domain Path:     	/languages
 * Text Domain:     	wf-cpanel-right-now-site-health
 */

/**
 * Exit if accessed directly
 */
if ( ! \class_exists( '\WP' ) ) {
	exit;
}

/**
 * Define a non-magic constant inside the namespace pointing to this main plugin file
 */
const PLUGIN_FILE = __FILE__;

if ( is_admin() ) {
	require_once 'compat-functions.php';
	require_once 'lib/convert.php';
	require_once 'src/Plugin.php';
	require_once 'src/RightNow.php';
	require_once 'src/SiteHealth.php';

	if ( ! \function_exists( '\get_plugin_data' ) ) {
		require_once \ABSPATH . 'wp-admin/includes/plugin.php';
	}

	Plugin::init();
}
