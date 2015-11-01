<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */

if ( !  defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

class EXTRP_Setup
{
	public static function on_activation()
	{
		global $extrp_sanitize, $extrp_settings;
		
		if ( empty( $extrp_settings ) ) :
			$extrp_settings = extrp_default_setting();
		else :
			$new_update = extrp_default_setting( 'update' );
			foreach ( $new_update as $key => $value )
			{
				if ( ! isset( $extrp_settings[ $key ] ) )
					$extrp_settings[ $key ] = $value;
			}
		endif;
		
		update_option( 'extrp_option', $extrp_settings );
		
		$current_version = get_option( 'extrp_version' );
		
		if ( '' != $current_version ) :
			update_option( 'extrp_version_upgraded_from', $current_version );
		else :
			$extrp_data = get_plugin_data( EXTRP_PLUGIN_PATH . '/extended-related-posts.php' );
			update_option( 'extrp_version', $extrp_data['Version'] );
		endif;
	}
	
	public static function on_deactivation()
	{
		global $extrp_settings;
		
		$default = extrp_default_setting();
		delete_option( 'extrp_version' );
		update_option( 'extrp_with_relevanssi', (bool) 0 );
		$upgraded_from = get_option( 'extrp_version_upgraded_from' );
		if ( $upgraded_from )
			delete_option( 'extrp_version_upgraded_from' );
		
		wp_delete_attachment( extrp_get_attach_id( $extrp_settings['noimage']['default'], null ), true  );
	}
	
	public static function on_uninstall()
	{
		global $extrp_settings;
		
		remove_action('extrp_set_noimage_first');
		self::delete_cache();
		delete_option( 'extrp_option' );
		delete_option( 'extrp_with_relevanssi' );
		delete_option( 'widget_extrp_widget' );
		
		wp_clear_scheduled_hook( 'extrp_delete_cache' );

		if ( 
			isset( $extrp_settings['noimage']['attachment_id'] ) 
			&& '' != $extrp_settings['noimage']['attachment_id'] 
			&& false === wp_delete_attachment( $extrp_settings['noimage']['attachment_id'], true  ) 
			) 
		{
			$msg = __( 'Fail to delete No Image default. Try to delete it manually.', 'extrp' );
			add_settings_error( 'extrp-notices', esc_attr( 'delete-attachment-notice' ), $msg, 'notice-warning' );
		}
	}
	
	protected static function delete_cache()
	{
		$extrp_load = new EXTRP_Load;
		$extrp_load->extrp_del_cache_transient();
	}
}