=== cPanel® Disk Usage, HTTPS only check in Site Health & Right Now Dash ===
Contributors: knutsp
Donate link: https://webfacing.eu/
Tags: disk-space, https-only, isp
Requires at least: 5.4
Tested up to: 5.5.1
Stable tag: trunk
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows disk usage information, recommendations and alerts in your dashboard Right Now widget and on Site Health panels. Also includes a Site Health test for HTTPS only, with information about the possible issue, recommendation and actions.

== Description ==

# cPanel&reg; Disk Usage, HTTPS only check in Site Health &amp; Right Now Dash - for WordPress
🕸️ By WebFacing. Shows disk usage information, recommendations and alerts in your admin Dashboard Right Now widget and on Site Health panels. Also includes a Site Health test for HTTPS only (http should not be allowed, but forwarded to https) with information about the issue, recommendation and actions. Made with a little help from [cPanel, L.L.C., USA](http://www.cpanel.net/) and [PRO ISP AS, Norway](https://proisp.eu/) - many thanks.
## This plugin adds the following
### Dashboard - Right Now (widget)
 * One item showing used disk space (on a cPanel&reg; controlled server only)
 * A widget footer line mentioning name of web hosting provider, and maximum disk space for current plan/account (on PRO ISP only)

### Tools - Site Health panels
#### Status (tab)
 * A disk space test with explaining text and possible actions (cPanel&reg; only) with following result types and actions
   * Good (less than 90%)
   * Recommended (over 90%, but less than 95%)
   * Critical (over 95%)
 * A HTTPS only test with explaining text (with HTTPS enabled only), actions with link to a relevant guide (special guide in case PRO ISP), and with the following result types and actions
  * Good (http loopback requests are rejected)
  * Recommended (http loopback requests successful)

#### Info (tab)
 * A disk space section containing (cPanel&reg; only)
   * Max disk space available
   * Total disk space used
    * &ndash; Disk used by media files
     * &ndash; Disk space used by mail
 * Adds one line to the WordPress Constant values section
   * WF_DEBUG
   
## Translation ready, ready translations are
 * Norwegian (bokmål)
 
## Debug and simulation setting
To simulate high disk space utilization, report fictional, random high results close to upper limit, add this line to your `wp-config.php` or in another plugin:
```
const WF_DEBUG = true;
```
## Known limitations
 * The amoumt of used disk space reported will not include space taken by other databases than the site's own WordPress database. The reported value may therefore be inaccurate or too low in some cases (accounts with more databases of any type).
 * This plugin will probably not report much if the site is not on a cPanel&reg; managed server, but will do the test for HTTPS only.
 * This plugin is written for **PHP 7.4** and later, and for WordPress 5.4 and later. It's namespaced, has static classes and uses typed class properties.

== Frequently Asked Questions ==

= Does this plugin add dtabase tables or options? =

No, none.

= Does it require my login information to cPanel? =

No.

= Does it work without cPanel? =

Yes, but max space will be N/A, also used spaces(s) may be unknown. But the 'HTTPS only' test will work.

== Screenshots ==

1. Dashboard - At a Glance
2. Site Health Test for HTTPS only
3. Site Health Disk Space Test
4. Site Health Info section

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
Now finally released, working and stable.
