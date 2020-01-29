=== Toolbar flags ===
Contributors: scottjwalter
Tags: security, administration, admin
Requires at least: 2.8
Tested up to: 5.3
Stable tag: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays the status of WP_DEBUG, DISALLOW_FILE_EDIT, and DISALLOW_FILE_MODS in the Toolbar.

== Description ==

A simple plugin to provide a visual reminder of the state of 3 WP flags.  Serves 2 purposes:

1. I wanted to learn more about plugin writing
2. I keep forgetting the setting of those flags as I go about updating sites.

== Installation ==

1. Upload to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.2.1 =
* Cleaned up documentation contact info
* Added Plugin Directory assets

= 1.2.0 =
* Dropped [PressTrends](http://www.presstrends.io/) support
* Renamed based class (prep for core unification)

= 1.1.2 =
* Bumped readme.txt to current release versions
* Non-essential upgrade to re-connect with the SVN deployment process

= 1.1.1  =
* Corrected a "divide by zero" issue inside the [PressTrends](http://www.presstrends.io/) code (which only occurs if you're using WordPress without a blog).
* Fixed version numbering

= 1.0.1 =
* Added [PressTrends](http://www.presstrends.io/) support
* Selected GPLv2 license

= 1.0 =
* Initial version of plugin
