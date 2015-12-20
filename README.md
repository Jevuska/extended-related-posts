---
Contributors: Jevuska
Donate link: http://www.jevuska.com/donate/
Tags: posts, relevanssi, relevant, search, shortcode, widget, multilanguage, thumbnail
Requires at least: 4.3
Tested up to: 4.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
---

Create a better related posts more relevant under your post. Settings, shortcode and widget available.

## Description
Extended Related Posts plugin could be supported by [Relevanssi](https://wordpress.org/plugins/relevanssi/) plugin if you activate the feature in plugin administration area. Searching related posts by post title is default search algorithm of this plugin, as WordPress search default. And if no results by post title, it will continued get the related posts by their categories automatically, then by tags, and by split each post title words. If no more relevant posts, it will show your random posts. Abuse with any extension that you made is welcome to support this plugin. A bunch of features is available.

## Installation
1. Upload plugin zip contents to `wp-contents/plugin` directory and activate the plugin.
2. Go to `EXTRP Related Posts` > `Settings` and make some configuration if you need and save your work.
3. If you need to use relevanssi algorithm for relevant posts, you can enable Relevanssi Algorithm feature under plugin panel settings.
4. You can insert shortcodes and php code into administration posts area or direct to your theme. Widget for related post on sidebar is available.

## Frequently Asked Questions
#### How do I setup my WordPress theme to work with Extended Related Posts

You can use php code `<?php do_action('jv-related-posts'); ?>` and add this single line code after the_content code. Single or sitewide pages is welcome. More advance code is available.

## Screenshots
1. General Settings `extrp-general-settings.jpg`.
![screenshot 1](lib/assets/images/screenshots/extrp-general-settings.jpg)

2. Thumbnail Settings `extrp-thumbnail-settings.jpg`.
![screenshot 2](lib/assets/images/screenshots/extrp-thumbnail-settings.jpg)

3. Additional Settings `extrp-additional-settings.jpg`.
![screenshot 3](lib/assets/images/screenshots/extrp-additional-settings.jpg)

4. Shortcode Generator `extrp-shortcode-generator.jpg`.
![screenshot 4](lib/assets/images/screenshots/extrp-shortcode-generator.jpg)

5. Widget Settings `extrp-widget.jpg`.
![screenshot 4](lib/assets/images/screenshots/extrp-widget.jpg)

## Changelog
* 1.0.4 = December 21, 2015
 * Fixes setup plugin and variables
 
* 1.0.3 = November 02, 2015
 * Fix sanitize input field
 * Fix stopwords functionality
 * Rename function `create_list_post_ids` into `data_textarea` and fix functionality
 * Fix image default for thumbnail
 * Remove `post_type` parameter from shortcode and widget

* 1.0.2 = October 29, 2015
 * patch `get_search_query` for shortcode and widget as base search if on search page

* 1.0.1 = October 25, 2015
 * Fix PHP warnings
 * Fix `extrp_del_cache_transient`
 * Fix item `on_deactivation` and `on_uninstall`
 * Sanitize input field
 * Fix code readability
 * Fix stable tag readme file

## Upgrade Notice
* 1.0.4
 * This version fixes a security related bug. Upgrade immediately.
 
* 1.0.3
 * This version fixes a security related bug. Upgrade immediately.
 
* 1.0.1
 * This version fixes a security related bug. Upgrade immediately.

* 1.0.0
 * This version is first release