=== Extended Related Posts ===
Contributors: Jevuska
Donate link: https://www.jevuska.com/donate/
Tags: posts, relevanssi, relevant, search, shortcode, widget, multilanguage, thumbnail, related
Requires at least: 4.3
Tested up to: 4.4
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a better related posts more relevant under your post. Settings, shortcode and widget available.

== Description ==
Extended Related Posts plugin could be supported by [Relevanssi](https://wordpress.org/plugins/relevanssi/ "Relevanssi plugin") plugin if you activate the feature in plugin administration area. Searching related posts by post title is default search algorithm of this plugin, as WordPress search default. And if no results by post title, it will continued get the related posts by their categories automatically, then by tags, and by split each post title words. If no more relevant posts, it will show your random posts. Abuse with any extension that you made is welcome to support this plugin. A bunch of features is available.

= Features =
 * **Automatic**, show related post under post content.
 * **Excerpt** on related post, you have two option here *default* and *snippet*.
 * **Thumbnail**, show thumbnail in each post, serve default thumbnail if no image attached in post, on-the-fly customize.
 * **Style**, you can change the appeareance of related post; *inline*, *right*, *left* etc...
 * **Keywords Highlight**, highlighting the match words on excerpt related post.
 * **Widgets**, set it up to show related post on your sidebar.
 * **Shortcode Generator**, it will make you more comfortable to create shortcode of **EXTRP related post**.
 * **PHP Code**, if you need to implement code direct into your theme, **Shortcode Generator** will create it for you.
 * **Stopwords**, filtering the keywords to get better relevant post.
 * **Caching**, this feature to make better performance load for your site. This plugin use *Transient* caching, and you can schedulling to delete caches or delete them directly.
 * **Multi-Language**, so far this plugin have 2 languages *English* and *Bahasa Indonesia*.
 * Some filter hooks is available. ( see: [Plugin page](https://www.jevuska.com/2015/10/22/extended-related-posts-plugin-wordpress/ "Plugin page") ).
 * A bunch of another features is available.

= In Package =
 * The excerpt post supported by `Search Excerpt` plugin ( author: *Scoot Yang* ).
 * Thumbnail resizer supported by `Aqua Resizer` plugin ( author: *Syamil MJ* ).
 
== Installation ==
1. Upload plugin zip contents to `wp-contents/plugin` directory and activate the plugin.
2. Go to `Settings` > `EXTRP Related Posts` and make some configuration if you need and save your work.
3. If you need to use relevanssi algorithm for relevant posts, you can enable Relevanssi Algorithm feature under plugin panel settings.
4. You can insert shortcodes and php code into administration posts area or direct to your theme. Widget for related post on sidebar is available.

== Frequently Asked Questions ==
= How do I setup my WordPress theme to work with Extended Related Posts =
You can use php code `<?php do_action('jv-related-posts'); ?>` and add this single line code after the_content code. Single or sitewide pages is welcome. More advance code is available.

== Screenshots ==
1. General Settings
2. Thumbnail Settings
3. Additional Settings
4. Shortcode Generator
5. Widget Settings

== Changelog ==
* 1.0.7 = January 17, 2016
 * Fixes bug when no title to split.
 
* 1.0.6 = December 28, 2015
 * Fixes readme.
 * Normalize path.
 * Remove unused variable in functions.
 * Fix highlight excerpt bug.
 * Add update core version in plugin footer.
 
* 1.0.5 = December 22, 2015
 * Fixes screen option variables.
 
* 1.0.4 = December 21, 2015
 * Fixes setup plugin and variables.
 
* 1.0.3 = November 02, 2015
 * Fix sanitize input field.
 * Fix stopwords functionality.
 * Rename function `create_list_post_ids` into `data_textarea` and fix functionality.
 * Fix image default for thumbnail.

* 1.0.2 = October 29, 2015
 * patch `get_search_query` for shortcode and widget as base search if on search page.

* 1.0.1 = October 25, 2015
 * Fix PHP warnings.
 * Fix `extrp_del_cache_transient`.
 * Fix item `on_deactivation` and `on_uninstall`.
 * Sanitize input field.
 * Fix code readability.
 * Fix stable tag readme file.
 * Remove `post_type` parameter from shortcode and widget.

* 1.0.0 = October 22, 2015
 * First official release!

== Upgrade Notice ==
* 1.0.7
 * Fixes a security related bug. Upgrade immediately.
 
* 1.0.6
 * Fixes a security related bug. Upgrade immediately.
 
* 1.0.5
 * Fixes a security related bug. Upgrade immediately.
 
* 1.0.4
 * This version fixes a security related bug. Upgrade immediately.
 
* 1.0.3
 * This version fixes a security related bug. Upgrade immediately.

* 1.0.1
 * This version fixes a security related bug. Upgrade immediately.

* 1.0.0
 * This version is first release.