=== Plugin Name ===
Contributors: clubdesign
Donate link: http://plugins.clubdesign.at
Tags: backgrounds, subtle, subtlepatterns, patterns, live preview 
Requires at least: 3.3
Tested up to: 3.5
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use the famous Subtle Patterns in all your Wordpress Installations! Live Preview included!

== Description ==

The plugin is now officially featured by SubtlePatterns: http://subtlepatterns.com/snacks/ !

Add the world famous SubtlePatterns ( http://www.subtlepatterns.com ) as backgrounds into your Wordpress Blog. 

Patterns are updated as Subtlepatterns adds new patterns. 
Enjoy the Live Preview of Patterns: Cycle through all patterns on your own website and save one if you like it!

Subtlepatterns.com has currently a majority of 300+ loveable background patterns!

Show some love and visit our Pages:

*	Owner & Creator of the patterns: [SubtlePatterns](http://www.facebook.com/subtlepatterns "SubtlePatterns")
*	Developer of the plugin: [ClubDesign - Web Development](http://www.facebook.com/clubdesign.mp "ClubDesign.at - Webdesign & Webdevelopment")

The plugin works fine for now, please tell us about your experiences in the forums. We did not test the plugin on IE8- and other older browsers.
If we get enough response, then we can continue the development and make the plugin even better.

**Theme Developers:**
If you want to include our plugin in your theme, please check the FAQ Page for details on how to change the default settings!
Please drop us an email at plugins@clubdesign.at, with an link to your theme. Thanks in advance!

**Donations**
Any donations are welcome to keep up the development of good Wordpress Plugins. Visit our website to find out how you can
donate. Takes 1 minute and makes us happy. ;) http://plugins.clubdesign.at


== Installation ==

1. Download & Install the Plugin to your Wordpress System
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the appearance menu in wordpress and enjoy the new Pattern Button
4. Go to your frontend and cycle through all patterns live on your website

== Frequently Asked Questions ==

= What does this plugin do? =

Adds all subtlepatterns.com directly into your wordpress installation, you have access to all patterns as they come from subtle patterns.

= Where do the patterns come from? =

Visit subtlepatterns.com and show some love! He is creating all the wonderfull patterns by himself!

= Are all the patterns saved on my server? =

No, absolutly not. The patterns are pulled from a Github Repository as you see them. Once you add a pattern as background, it is added to your media library.

= Do i rely on other services? =

As written above, once installed no other service is used. 
There might be a downtime from github some day, then the patterns will no be pulled for selection. But i don`t think that happened before.

= Theme Developer Information = 
If you want to include this plugin as a required plugin, please drop us an email at plugins@clubdesign.at. Just for information!

There are 2 global variables that allow you to override the default plugin settings.
SUBPAT_NOTICE : If set to "0", it will override the default welcome notice.
SUBPAT_LIVE_MODE : If set to "0", it will override the default live mode setting ( default is on )

Define these 2 variables on TOP of your functions.php, f.e. like this:
define('SUBPAT_NOTICE', '0');
define('SUBPAT_LIVE_MODE', '0');

Have fun, and don`t forget to drop us an email or hit us up via Facebook http://www.facebook.com/clubdesign.mp

== Screenshots ==

1. Perfect Backend Integration directly into Appearance Panel

2. All Patterns easily accessible on one page.

3. Frontend Live Mode. Test all backgrounds directly on your page. Click save to add one as background.

== Changelog ==

= 1.2 =
* Bugfixes ( thanks to kraterdesign )

= 1.1 =
* Added more Theme Developer override possibilities
* Added description to live mode settings
* Added donate button ;)
* Added Subtle Pattern Logo
* Some minor code improvements

= 1.0 =
* Re-commit due to broken SVN Repo

= 0.3 =
* Minor Updates

= 0.2 =
* Added global variable SUBPAT_NOTICE. If set to "0" in your theme`s functions.php file, you can override the internal notices by the plugin. Good, if you want to make the plugin required in your theme development process.
* Some Updates to the readme file.
* Json Response - The string which comes from GitHub is now saved on your server. 

= 0.1 =
* Initial release of Subtle Background Patterns

== Upgrade Notice ==

= 1.0 =
Just upgrade, no further action needed.

= 0.1 =
Install and be amused!