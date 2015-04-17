=== Facebook Feeder ===
Contributors: robin.cornelius
Donate link: http://example.com/
Tags: posts,facebook
Requires at least: 4.1.1
Tested up to: 4.1.1
Stable tag: 4.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Parses Facebook page posts via graph API and turns these in to Wordpress blog 
posts

== Description ==

This plugin will fetch facebook page posts and turn these into wordpress blog 
entries. It is designed to be run as a cron job to automaticly update your blog
as you make posts to facebook.

It will also use the first image from each post as the featured image and also
insert any other images found after the post text.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the `facebookfeeder` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Register a new App on facebook and get the App ID and App Secret
4. Determine the page ID of facebook that you wish to parse
5. Enter page ID, App ID and App secret into the settings page (go to settings facebook page feeder)
6. From the settings page choose who you which to have the posts appearing as
7. Set a maximum retry delay, this will prevent you making to many requests by accident
8. Set a cron job to poll eg:-

*/10 * * * * /usr/bin/curl --silent http://www.example.org?facebookfeed.php

This is set to every 10 minutes but will be limited by the retry delay setting

== Frequently Asked Questions ==

Non yet

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 1.0 =
* Inital release

== Upgrade Notice ==

