<?php
namespace WebFacing\cPanel;

/**
 * cPanel Information Right Now
 *
 * @package             WebFacingcPanelPlugin
 * @author              knutsp <knut@sparhell.no>
 * @copyright           © 2020 Knut Sparhell, Nettvendt/IT-ing Sparhell, Norway
 * @license	        GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:			cPanel&reg; Disk Usage in Site Health & Right Now
 * Description:			🕸️ By WebFacing. Shows disk usage information and alerts in your admin Dashboard Right Now widget and on Site Health panels. Made with help from PRO ISP.
 * Plugin URI:			https://webfacing.eu/
 * Version:			0.8.2
 * Author:			Knut Sparhell
 * Author URI:			https://profiles.wordpress.org/knutsp/
 * License:	    		GPL v2 or later
 * License URI: 		https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP:                7.4
 * Requires at least:	        5.4
 * Tested up to:		5.5.1
 * Domain Path:                 /languages
 * Text Domain:			wf-cpanel-right-now-site-health
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
