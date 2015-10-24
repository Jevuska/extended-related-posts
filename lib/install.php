<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

register_activation_hook( EXTRP_PLUGIN_FILE, array(
	'EXTRP_Setup',
	'on_activation' 
) );

register_deactivation_hook( EXTRP_PLUGIN_FILE, array(
	'EXTRP_Setup',
	'on_deactivation' 
) );

register_uninstall_hook( EXTRP_PLUGIN_FILE, array(
	'EXTRP_Setup',
	'on_uninstall' 
) );

add_action( 'plugins_loaded', array(
	'EXTRP_Load',
	'init' 
) );
