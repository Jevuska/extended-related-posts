<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

class EXTRP_Load
{
	protected static $instance;
	
	public static function init()
	{
		is_null( self::$instance ) AND self::$instance = new self;
		return self::$instance;
	}
	
	public function __construct()
	{
		global $extrp_settings;

		if ( $extrp_settings['cache'] )
		{
			add_filter( 'cron_schedules', array(
				$this,
				'extrp_del_cache_schedule' 
			) );
			add_action( 'extrp_delete_cache', array(
				$this,
				'extrp_del_cache_transient' 
			) );
			
			if ( ! wp_next_scheduled( 'extrp_delete_cache' ) ) {
				wp_schedule_event( current_time( 'timestamp' ), 'inseconds', 'extrp_delete_cache' );
			}
		}

		add_action( 'init', 'extrp_register_shortcodes' );
		add_filter( 'the_content', 'do_shortcode' );
		add_filter( 'widget_text', 'do_shortcode' );
		if ( $extrp_settings['active'] )
			add_filter( 'the_content', 'extrp_filter_the_content', 10 );
		add_action( 'admin_init', 'update_with_relevanssi_option' );
		add_action( 'wp_enqueue_scripts', 'extrp_enqueue_scripts' );
		add_action( 'jv-related-posts', 'extrp_related_posts' );
		add_action( 'after_setup_theme', 'extrp_theme_setup' );
		add_action( current_filter(), array(
			$this,
			'load_file' 
		), 30 );
		
		add_filter( 'plugin_action_links', 'extrp_plugin_action_links', 10, 5 );
		add_action( 'widgets_init', 'extrp_register_widgets' );
	}
	
	public function load_file()
	{
		foreach ( glob( EXTRP_PATH_LIB . 'inc/load/*.php' ) as $file )
			include_once $file;
	}
	
	public function extrp_del_cache_schedule( $schedules )
	{
		global $extrp_settings;
		$seconds                = $extrp_settings['schedule'];
		$schedules['inseconds'] = array(
			'interval' => $seconds,
			'display'  => __( 'Every ' . $seconds . ' seconds' ) 
		);
		return $schedules;
	}
	
	public function extrp_del_cache_transient()
	{
		global $wpdb;
		
		$caches = wp_cache_get( 'extrp_transient_cache_all', 'extrpcache' );
		
		if ( false == $caches ) :
			$s	    = "%extrp_cache_post_%";
			$sql    = "
					  SELECT option_name
					  FROM $wpdb->options
					  WHERE option_name
					  LIKE %s
					  ";
			$sql    = $wpdb->prepare( $sql, $s );
			$caches = $wpdb->get_col( $sql );
			wp_cache_set( 'extrp_transient_cache_all', $caches, 'extrpcache', 300  );
		endif;
		
		if ( $caches ) :
			foreach ( $caches as $transient )
				delete_option( $transient );
		endif;
	}
}