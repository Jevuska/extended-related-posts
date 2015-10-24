<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

	$extrp_settings = extrp_settings();
	$new_fields_defaults = array(); //no items for updates
	foreach ( $new_fields_defaults as $key => $value ) {
		if ( ! isset( $extrp_settings[ $key ] ) ) {
			$extrp_settings[ $key ] = $value;
		}
	}
	update_option( 'extrp_option', $extrp_settings );