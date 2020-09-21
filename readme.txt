=== WebFacing Disk Usage from cPanel® on Dashboard & in Site Health ===
Contributors: knutsp
Donate link: https://webfacing.eu/
Tags: disk-space, security, isp, cpanel
Requires at least: 5.4.2
Tested up to: 5.5.1
Stable tag: 1.3
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows your disk usage information, used and max allowed, plus alerts, in your admin Dashboard At a Glance widget and as test and info in Site Health tabs, including space used by your media uploads and cPanel® mail accounts. Also adds a HTTPS only test to Site Health. Made with help from PRO ISP and cPanel®.
== Description ==

🕸️ By [WebFacing](https://webfacing.eu/). Shows disk usage information, recommendations and alerts in your admin Dashboard Right Now widget and on Site Health panels. Also includes a Site Health test for HTTPS only (http should not be allowed, but forwarded to https) with information about the issue, recommendation and actions. Made with a little help from [cPanel, L.L.C., USA](http://www.cpanel.net/) and [PRO ISP AS, Norway](https://proisp.eu/) - many thanks.

## This plugin adds the following:
### Dashboard - At a Glance (widget)
 * One item showing used disk space (on a cPanel&reg; controlled server only)
 * One line widget footer line mentioning the name of your web hosting provider, and maximum disk space for current plan/account (on PRO ISP only)

### Tools - Site Health panel
#### Status (tab)
* A disk space test with explaining text and possible actions (cPanel&reg; only) with following result types and actions
	* Good (less than 90%)
	* Recommended action (over 90%, but less than 95%)
	* Critical (over 95%)
* A HTTPS only test with explaining text (with HTTPS enabled only), actions with link to a relevant guide (special guide in case PRO ISP), and with the following result types and actions
	* Good (http loopback requests are rejected)
	* Recommended action (http loopback requests successful)

#### Info (tab)
* A disk space section containing (cPanel&reg; only)
	* Max disk space available
	* Total disk space used
		* Disk used by your uploaded media files
		* Disk space used your by mail accounts
* Adds one line to the WordPress Constant values section
	* `WF_DEBUG`

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
* This plugin was originally written for **PHP 7.4** and later, and for WordPress 5.4.2 and later. It's namespaced, has static classes and uses typed class properties. Now available for PHP 7.2, after removing typed properties in this wporg-version.

== Frequently Asked Questions ==

= Does this plugin add database tables or store options? =

No, none.

= Does it require my login information to cPanel&reg;? =

No.

= Does it work without cPanel&reg;? =

Yes, but max space will be N/A, also used spaces(s) may be unknown. But the 'HTTPS only' test will work.

= Can I contribute to this plugin? =

Yes, visit it's [Github repo](https://github.com/Nettvendt/wf-cpanel-right-now-site-health) and create an issue, clone it and/or file a pull request.

== Screenshots ==

1. Dashboard - At a Glance
2. Site Health Test for HTTPS only
3. Site Health Disk Space Test
4. Site Health Info section

== Changelog ==

= 1.3 =

* Get disk used also when not on cPanel&reg;.

= 1.2.1 =

* Sanitize keys for Pro package.

= 1.2 =

* Switched to new `quota` command on cPanel&reg; for disk space max & used. Props to PRO ISP AS for implementing it.

= 1.1 =

* Cap check for showing cPanel&reg; username in At a Glance.

= 1.0 =

* Initial release.
