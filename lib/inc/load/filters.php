<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

function extrp_capability_filter( $option )
{
	//avalible option refer to https://codex.wordpress.org/Roles_and_Capabilities
	$cap = apply_filters( 'extrp_capability_filter', $option );
	return sanitize_key( $cap );
}

function extrp_max_chars( $maxchars )
{
	$maxchars = apply_filters( 'extrp_max_chars', $maxchars );
	return absint( $maxchars );
}

function extrp_limit_count_id( $limit )
{
	$limit = apply_filters( 'extrp_limit_count_id', $limit );
	return absint( $limit );
}

function do_related_by_tag()
{
	$option = 'tag';
	return $option;
}

function do_related_by_cat()
{
	$option = 'cat';
	return $option;
}

function do_related_by_split_title()
{
	$option = 'splittitle';
	return $option;
}

function do_related_by_random()
{
	$option = 'random';
	return $option;
}