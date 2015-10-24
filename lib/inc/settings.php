<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

function extrp_settings()
{
	global $extrp_settings;

	if ( ! empty( $extrp_settings ) )
		return $extrp_settings;

	$extrp_settings = get_option( 'extrp_option' );

	return $extrp_settings;
}

function extrp_default_setting( $option = '' )
{
	global $extrp_sanitize;
	$args = $extrp_sanitize->sanitize();
	
	switch ( $option )
	{
		case 'update' :
			$args = array(
				 'required_wp_version' => '4.2.2' 
			);
			return $args;
		break;
		
		case 'shortcode' :
			$keys    = array_keys( $args );
			$exclude = array( 0, 13, 17, 18, 19, 21, 24, 29, 30, 31 );
			for ( $i = 0; $i < count( $keys ); $i++ ) :
				if ( in_array( $i, $exclude ) ) {
					unset( $args[ $keys[ $i ] ] );
				};
				if ( 'image_size' == $keys[ $i ] )
					$args[ $keys[ $i ] ] = false;
			endfor;

			return $args;
		break;
		
		default :
			$args = apply_filters( 'extrp_default_settings', $args );
			return $args;
		break;
	}
}