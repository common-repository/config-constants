=== Config Constants ===
Contributors: dgwyer
Tags: WP_DEBUG, wp-config. wp-config.php, debug, mode, constant, config, admin, toggle, file, update
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 0.2

Modify WP_DEBUG and other WordPress constants directly in the WordPress admin rather than manually editing them via wp-config.php!

== Description ==

NOTE: This Plugin is still in beta. It's recommended that you don't run this Plugin on production sites yet, and don't forget to backup your wp-config.php file.

WordPress constants such as WP_DEBUG can be defined in wp-config.php but have to be edited manually every time you need to make a change. But no longer! You can now edit several common wp-config.php constants directly from within the WordPress admin!

The current list of WordPress constants you can modify with Config Constants are:

* WP_DEBUG
* WP_DEBUG_LOG
* WP_DEBUG_DISPLAY
* SCRIPT_DEBUG
* CONCATENATE_SCRIPTS
* SAVEQUERIES
* DISALLOW_FILE_MODS
* DISALLOW_FILE_EDIT
* WP_ALLOW_REPAIR

More constants will be supported in future versions.

If none of the above constants are defined in wp-config.php then they will be added automatically. Constants already specified in wp-config.php will have their values modified.

Also, a key feature of the Plugin is the support for two-way editing. This means you can still edit wp-config.php constants manually if you wish and your changes will be automatically synchronised with Plugin settings. Likewise, if you update the value of a constant via the Plugin options page then wp-config.php is immediately updated!

Please rate this Plugin if you find it useful. :)

Visit our <a href="http://www.wpgoplugins.com" target="_blank">WordPress plugin site</a>.

== Installation ==

Instructions for installing:

1. In your WordPress admin go to Plugins -> Add New.
2. Enter Config Constants in the text box and click Search Plugins.
3. In the list of Plugins click Install Now next to the Config Constants Plugin.
4. Once installed click to activate.
5. Visit the Plugin options page via Settings -> Config Constants.
6. On the Plugin options page some/all of the supported constants may be initially disabled as you can only modify existing constants defined in wp-config.php. To be able to edit a constant directly in the admin just add it to your wp-config.php file and it will get picked up automatically in the Plugin settings.

== Screenshots ==

1. Where all the magic happens.

== Changelog ==

*0.2*

* Plugin completely overhauled to work with WordPress 4.8 and fix outdated code.

*0.1*

* Initial release.