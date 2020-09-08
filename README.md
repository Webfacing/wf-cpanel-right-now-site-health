# cPanel Disk Usage, HTTPS only check in Site Health &amp; Right Now Dash - for WordPress
🕸️ By WebFacing. Shows disk usage information, recommendations and alerts in your admin Dashboard Right Now widget and on Site Health panels. Also includes a Site Health test for HTTPS only (http should not be allowed, but forwarded to https) with information about the issue, recommendation and actions. Made with a little help from [PRO ISP AS, Norway](https://proisp.eu/) - many thanks.
## This plugin adds the following
### Dashboard - Right Now (widget)
 * One item showing used disk space (on a cPanel kontrolled server only)
 * A widget footer line mentioning name of web hosting provider, and maximum disk space for current plan/account (on PRO ISP only)
### Tools - Site Health panels
#### Status (tab)
 * A disk space test with explaining text and possible actions (cPanel only) with following result types and actions
   * Good (less than 90%)
   * Recommended (over 90%, but less than 95%)
   * Critical (over 95%)
 * A HTTPS only test with explaining text (with HTTPS enabled only), actions with link to a relevant guide (special guide in case PRO ISP), and with the following result types and actions
  * Good (http loppback requests are rejected)
  * Recommended (http loopback requests successful)
#### Info (tab)
 * A disk space section containing (cPanel only)
   * Max disk space available
   * Total disk space used
    * &ndash; Disk used by media files
     * &ndash; Disk space used by mail
 * Adds one line to the WordPress Constant values section
   * WF_DEBUG
## Known limitations
 * The amoumt of used disk space reported will not include space taken by other databases than the site's own WordPress database. The reported value may therefore be inaccurate or too low in some cases (accounts with more databases of any type).
 * This plugin will probably not report much if the site is not on a cPanel managed server, but will do the test for HTTPS only.
 * This plugin is written for **PHP 7.4** and later, and for WordPress 5.4 and later. It's namespaced, static classes and uses typed class properties.
