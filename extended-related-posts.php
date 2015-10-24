<?php
/*
Plugin Name: Extended Related Posts
Plugin URI: http://www.jevuska.com/2015/10/22/extended-related-posts-plugin-wordpress/
Description:  Create a better related posts more relevant under your post. Settings, shortcode and widget available.
Version: 1.0.0
Author: Jevuska
Author URI: http://www.jevuska.com
License: GPL2
Domain Path: /lib/languages
Text Domain: extrp

Extended Related Posts is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Extended Related Posts is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Extended Related Posts. If not, see https://www.gnu.org/licenses/gpl-2.0.html

* @package EXTRP
* @category Core
* @author Jevuska
* @version 1.0
*/

if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Extended_Related_Posts' ) ) :
	final class Extended_Related_Posts
	{
		private static $instance;
		public static function instance()
		{
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Extended_Related_Posts ) ) :
				self::$instance = new Extended_Related_Posts;
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				define( 'EXTRP_RUNNING', true );
			endif;
			return self::$instance;
		}
		
		public function setup_constants()
		{
			if ( ! defined( 'EXTRP_PLUGIN_FILE' ) )
				define( 'EXTRP_PLUGIN_FILE', __FILE__ );
			
			if ( ! defined( 'EXTRP_PLUGIN_VERSION' ) )
				define( 'EXTRP_PLUGIN_VERSION', '1.0.0' );

			if ( ! defined( 'EXTRP_PLUGIN_URL' ) )
				define( 'EXTRP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			
			if ( ! defined( 'EXTRP_PLUGIN_PATH' ) )
				define( 'EXTRP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			
			if ( ! defined( 'EXTRP_PATH_LIB' ) )
				define( 'EXTRP_PATH_LIB', EXTRP_PLUGIN_PATH . 'lib/' );
			
			if ( ! defined( 'EXTRP_PATH_PLUGIN_TEMPLATE' ) )
				define( 'EXTRP_PATH_PLUGIN_TEMPLATE', EXTRP_PATH_LIB . 'template/' );
			
			if ( ! defined( 'EXTRP_PATH_PLUGIN_IMAGES' ) )
				define( 'EXTRP_PATH_PLUGIN_IMAGES', EXTRP_PATH_LIB . 'assets/images/' );
			
			if ( ! defined( 'EXTRP_URL_PLUGIN_IMAGES' ) )
				define( 'EXTRP_URL_PLUGIN_IMAGES', EXTRP_PLUGIN_URL . 'lib/assets/images/' );
			
			if ( ! defined( 'EXTRP_URL_PLUGIN_CSS' ) )
				define( 'EXTRP_URL_PLUGIN_CSS', EXTRP_PLUGIN_URL . 'lib/assets/css/' );
			
			if ( ! defined( 'EXTRP_ADMIN_PATH' ) )
				define( 'EXTRP_ADMIN_PATH', EXTRP_PATH_LIB . 'admin/' );
		}
		
		private function includes()
		{
			global $extrp_sanitize, $extrp_settings;
			
			require_once( EXTRP_PATH_LIB . 'inc/class/class-aq-resizer.php' );
			require_once( EXTRP_PATH_LIB . 'inc/class/class-extrp-thumbnail.php' );
			require_once( EXTRP_PATH_LIB . 'inc/class/class-extrp-sanitize.php' );
			require_once( EXTRP_PATH_LIB . 'inc/settings.php' );
			
			$extrp_sanitize = extrp_sanitize();
			$extrp_settings = extrp_settings();
			
			require_once( EXTRP_PATH_LIB . 'inc/class/class-extrp-load.php' );
			require_once( EXTRP_PATH_LIB . 'inc/class/class-extrp-excerpt.php' );
			
			if ( is_admin() ) :
				require_once( ABSPATH . 'wp-includes/pluggable.php' );
				if ( current_user_can( 'manage_options' ) ) :
					require_once( EXTRP_ADMIN_PATH . 'admin-functions.php' );
					require_once( EXTRP_ADMIN_PATH . 'class/class-extrp-shortcode-table.php' );					
					require_once( EXTRP_ADMIN_PATH . 'class/class-extrp-admin.php' );
					require_once( EXTRP_ADMIN_PATH . 'class/class-extrp-setup.php' );
					do_action( 'load-extrp-admin-page' );
				endif;
			else :
			endif;
			require_once( EXTRP_PATH_LIB . 'inc/class/class-extrp-widget.php' );
			require_once( EXTRP_PATH_LIB . 'install.php' );
		}
		
		public function load_textdomain()
		{
			$domain          = 'extrp';
			$extrp_lang_dir  = EXTRP_PATH_LIB . 'languages/';
			$extrp_lang_dir  = apply_filters( 'extrp_languages_directory', $extrp_lang_dir );
			$locale          = apply_filters( 'plugin_locale', get_locale(), $domain );
			$mofile          = sprintf( '%1$s-%2$s.mo', $domain, $locale );
			$mofile_local    = $extrp_lang_dir . $mofile;
			$mofile_global   = trailingslashit( WP_LANG_DIR ) . $domain . '/' . $mofile;
			
			if ( file_exists( $mofile_global ) ) :
				load_textdomain( $domain, $mofile_global );
			elseif ( file_exists( $mofile_local ) ) :
				load_textdomain( $domain, $mofile_local );
			else :
				load_plugin_textdomain( $domain, false, $extrp_lang_dir );
			endif;
		}
	}
endif;

function EXTRP()
{
	return Extended_Related_Posts::instance();
}
EXTRP();