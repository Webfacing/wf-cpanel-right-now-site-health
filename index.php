<?php
namespace WebFacing\cPanel;

/**
 * cPanel Information At a Glance
 *
 * @package         	WebFacingcPanelPlugin
 * @author          	knutsp <knut@sparhell.no>
 * @copyright       	© 2021 Knut Sparhell, Nettvendt/IT-ing Sparhell, Norway
 * @license         	GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:     	WebFacing - Disk, resource usage and errors from cPanel® on your Dashboard &amp; in Site Health
 * Description:     	🕸️ By WebFacing. Shows your disk usage information, used and max allowed, errors and alerts, in your admin Dashboard At a Glance widget, other resource usage as gauges in a custom dashboard widget, and as test and info in Site Health tabs, including space used by your media uploads and cPanel® mail accounts. Also adds a Email Routing and a HTTPS only test to Site Health. Made with great help from PRO ISP AS, Norway.
 * Plugin URI:      	https://webfacing.eu/
 * Version:         	2.8
 * Author:          	Knut Sparhell
 * Author URI:      	https://profiles.wordpress.org/knutsp/
 * License:         	GPL v2 or later
 * License URI:     	https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP:    	7.3
 * Requires at least:   5.3
 * Tested up to:    	5.8.1
 * Text Domain:     	wf-cpanel-right-now-site-health
 */

/**
 * Exit if accessed directly
 */
if ( ! \class_exists( 'WP' ) ) {
	exit;
}

/**
 * Define a non-magic constant inside the namespace pointing to this main plugin file
 */
const PLUGIN_FILE = __FILE__;

require_once 'compat-functions.php';
require_once 'lib/i18n.php';
require_once 'lib/utils.php';
require_once 'includes/Plugin.php';
require_once 'includes/Glance.php';
require_once 'includes/Charts.php';
require_once 'includes/Health.php';

Plugin::load();

if ( is_admin() ) {
	Plugin::admin_load();
}
