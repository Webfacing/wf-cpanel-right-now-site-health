=== WebFacing - Disk Usage from cPanel® on Dashboard &amp; in Site Health ===
Contributors: knutsp
Donate link: https://webfacing.eu/
Tags: disk-space, security, isp, cpanel
Requires at least: 5.2
Tested up to: 5.7.2
Stable tag: 2.2.1
Requires PHP: 7.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows disk usage information, recommendations and alerts in your dashboard At a Glance widget and on Site Health panel tabs.
Also includes a Site Health test for HTTPS only, and an Email routing test,
both with information about the possible issue, recommendation and actions.
Worth noting is that the disk space used/available are the figures for your hosting account, not for the entire (shared) server. 

== Description ==

🕸️ By [WebFacing](https://webfacing.eu/). Shows disk usage information, recommendations and alerts, plus number of email accounts,
in your admin Dashboard At a Glance widget and on Site Health panels.
Includes an email routing test (when server is not authority, routing must be remote).
Also includes a Site Health test for HTTPS only
(http should not be allowed, but forwarded to https) with information about the issue, recommendation and actions.
Made with a little help from [cPanel, L.L.C., USA](http://www.cpanel.net/) and [PRO ISP AS, Norway](https://proisp.eu/) - many thanks.

This plugin is a candidate to be recommended to all customers by [PRO ISP AS, Norway](https://proisp.eu/).

See also [WebFacing – Email Accounts in cPanel®](https://wordpress.org/plugins/wf-cpanel-email-accounts/)

## This plugin adds the following:

### Dashboard - At a Glance (widget)
 * One item showing used disk space
 * One item showing number of email accounts (site domain only)
 * One line widget footer line mentioning the name of your web hosting provider, and maximum disk space for current plan/account (on PRO ISP only)

### Tools - Site Health panel

#### Status (tab)

* A disk space test with explaining text and possible actions (cPanel® only) with following result types and actions
	* Good (less than 90%)
	* Recommended (over 90%, but less than 95%)
	* Critical (over 95%)
* A HTTPS only test with explaining text (with HTTPS enabled only), actions with link to a relevant guide (special guide in case PRO ISP), and with the following result types and actions
	* Good (http loopback requests are rejected)
	* Recommended fix (http loopback requests successful)
* An Email Routing test for the site domain with explaining text and possible actions (cPanel® only) with following result types and actions
	* Good (local server is authoritative or Email routing is Remote)
	* Critical (local server is not authoritative and Email routing is Local)

#### Info (tab)

* A disk space section containing (cPanel® only)

	* cPanel® user name (private)
	* cPanel® user created (private)
	* Two Factor Authentication enabled in cPanel®?
	* Max disk space available
	* Total disk space used
		* Disk used by media files
		* Disk space used by mail
	* Is Local DNS authoritative?
		* MX server is self
		* Email Routing is local?
	* Main domain in cPanel®
		* Addon domains
		* Parked domains
	* MySQL® Disk Usage
	* CPU Usage
	* Entry Processes
	* Physical Memory Usage
	* In/Out Operations Per Second (IOPS)
	* In/Out Usage
	* Number of Processes
	* Email accounts disk space used (all under the site domain)
	* Email forwarding destinations (all under the site domain)
	* Contact email addresses in cPanel®

* Adds one line to the WordPress Constant values section

	* `WP_DISABLE_FATAL_ERROR_HANDLER`
	* `ALLOW_UNFILTERED_UPLOADS`
	* `AUTOMATIC_UPDATER_DISABLED`
	* `WP_AUTO_UPDATE_CORE`
	* `ALLOW_UNFILTERED_UPLOADS`
	* `CORE_UPGRADE_SKIP_NEW_BUNDLED`
	* `DISALLOW_FILE_MODS`
	* `DISALLOW_FILE_EDIT`
	* `SAVEQUERIES`
	* `WP_POST_REVISIONS`
	* `WF_DEV_LOGIN`
	* `WF_DEBUG`

## Translation ready, ready translations are

* Norwegian (bokmål)

## Debug setting

For ekstra debug information, add this line to your `wp-config.php` or in another plugin:
```
const WF_DEBUG = true;
```
## Known limitations

* Links to documentation to resolve reported issues are shown to PRO ISP AS customers only.
* This plugin will probably not report much if the site is not on a cPanel® managed server, but will do the test for HTTPS only.
* This plugin was originally written for **PHP 7.4** (recommended) and later, and for WordPress 5.2 (Site Health introduced) and later. It's namespaced, has static classes and uses typed class properties. Also available for PHP 7.2, since typed properties are removed in this wporg edition.

== Frequently Asked Questions ==

= Does this plugin add database tables, store options or adding lines to ´wp-config.php´? =

No, not, none.

= Does it require my login information to cPanel®? =

No.

= Does it work on other web hosts than PRO ISP? =

Yes, at least on some, but not tested much. Please report your experience. Use Reviews, Support or GitHub.

= Does it work without cPanel®? =

Very, very limited. The 'HTTPS only' security test should work, and disk used info, but max space test will not be performed and the result will just show 'N/A'.

= Can I contribute to this plugin? =

Yes, visit it's [Github repo](https://github.com/Nettvendt/wf-cpanel-right-now-site-health) and create an issue, clone it and/or file a pull request.

== Screenshots ==

1. Dashboard - At a Glance
2. Site Health Disk Space Test
3. Site Health Email Routing Test
4. Site Health HTTPS only test
5. Site Health Info section

== Changelog ==

= 2.2.1 =

* Translation fixes

= 2.2 =

* Less strict cPanel® features check on load
* Add dead domains to Site Health Info tab
* Count addon, parked and dead domains as label suffix
* Include main email account in email account count in Dashboard - At a Glance widget
* More translation contexts

= 2.1 =

* Add Site Health cPanel® Info tab main account disk usage
* Add Site Health cPanel® Info tab maximum emails sending frequency per hour
* Add cPanel® version info in Site Health Info tab
* Tidy up Site Health for cPanel® entries in Info tab
* Reorder, and make more logically hierarchical, Site Health for cPanel® Info tab
* Remove Site Health cPanel® forwarders in Info tab (install my other plugin 'WebFacing – Email Accounts in cPanel®' to list them)
* Better handling of IDN domains where overlooked
* A few extra, useful WordPress constants in Site Health Info tab, but removed WP_ENVIRONMENT_TYPE as redundant
* Recommending my other plugin 'WebFacing – Email Accounts in cPanel®' in Dashboard - At a glance widget

= 2.0.1 =

* Fixed a bug (oversight) in 2.0 that alerted about email routing in the case that the MX-record points to self. In that case, no worry.

= 2.0 =

* Email accounts number (and size as tooltip) in Dashboard Right Now widget
* New test for Email Routing under Site Health Status tab
* More constants under WordPress Constants in Site Health Info tab
* A lot more information in cPanel® & Disk Usage in Site Health Info tab

= 1.6.2 =

* Fix for fatal error when undefined constant in PHP 7.4

= 1.6.1 =

* Urgent: Safeguard against PHP fatal errors when installed on a site not using cPanel®

= 1.6 =

* March 11, 2021
* In case on PRO ISP AS: Added link to PRO ISP's support article for enabling HTTPS in cPanel® in Site Health - Status - Security
* Database disk space shown in Dashboard widget tooltip
* A few more useful constants in Site Health - Info - WordPress Constants
* Correct language neutral values in Site Health - Info for debug copy results
* Some minor translation fixes

= 1.5.3 =

* Bugfix: Database disk space was counted twice, leading to too high value for total disk space used

= 1.5.2 =

* Added Database disk usage to Site Health Info tab

= 1.5 =

* Partly rewritten to use more cPanel® <code>uapi</code> calls
* Removed cPanel® <code>Quota</code> calls
* Introducing some cPanel® Usage Statistics parametres, like CPU Usage and number of Entry Processes, in Site Health Info tab
* Better caching of values in short lived transients
* Added <code>DISALLOW_FILE_EDIT</code> to Site Health Info WordPress Constants

= 1.4.1 =

* Spelling error for Pro Premium package.
* Tested for WP 5.6
* Some minor text changes in ´readme.txt´.
* Some old code cleanup.

= 1.4 =

* Added detection for Enterprise hosting packages at PRO ISP.

= 1.3 =

* Get disk used also when not on cPanel®.

= 1.2 =

* Switched to new `quota` command on cPanel® for disk space max & used. Thanks to [@proisp](https://profiles.wordpress.org/proisp/) for implementing it.

= 1.1 =

* Cap check for showing cPanel® username in At a Glance.

= 1.0 =

* Initial release, Sep 2020.
