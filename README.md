# cPanel Disk Usage in Site Health &amp; Right Now
🕸️ By WebFacing. Shows disk usage information and alerts in your admin Dashboard Right Now widget and on Site Health panels. Made with help from PRO ISP.
## This plugin adds the following
### Dashboard - Right Now (widget)
 * One item showing used disk space (on a cPanel kontrolled server only)
 * A widget footer line mentioning name of web hosting provider, and maximum disk space for current plan/account (on PRO ISP only)
### Tools - Site Health panels
#### Status (tab)
 * A disk space check with explaining text and possible actions (cPanel only) with following results
   * Good (less than 90%)
   * Recommended (over 90%, but less than 95%)
   * Critical (over 95%)
 * A HTTPS only test wih explaining text (when HTTPS is set as home URL only), actions with link to relevant resources and help (special link for PRO ISP), and with the following results
  * Good (http requests are rejected)
  * Recommended (http requsts return status 2xx)
#### Info (tab)
 * A disk space section containing (cPanel only)
   * Max disk space available
   * Total disk space used
    * &ndash; Disk used by media files
     * &ndash; Disk space used by mail
 * Adds one line to the WordPress Constant values section
   * WF_DEBUG
## Known limitations
 * The amoumt of used disk space reored will not include other databases than the currently used the WordPress site. The reorted value nay be too low in such cases.
 * This plugin will not repor much if site is not on a cPanel managed server, but will test for hHTTPS only.
 * This plugin is written for PHP 7.4 or newer, and for WordPress 5.4 or newer.
 
