# cPanel Disk Usage in Site Health &amp; Right Now
🕸️ By WebFacing. Shows disk usage information and alerts in your admin Dashboard Right Now widget and on Site Health panels. Made with help from PRO ISP.
## This plugin adds the following
### Dashboard - Right Now (widget)
 * One item showing used disk space (on a cPanel kontrolled server only)
 * A sentence mentioning name of web hosting provider and maximum disk space (on PRO ISP only)
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
   * - Disk used by media files
   * - Disk space used by mail
 * Adds one line to the WordPress Constant values section
  * WF_DEBUG
