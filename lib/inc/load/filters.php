<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

function extrp_capability_filter()
{
	$option = 'publish_posts'; //avalible option refer to https://codex.wordpress.org/Roles_and_Capabilities
	return apply_filters( 'extrp_capability_filter', $option );
}

function extrp_max_chars( $maxchars = '' )
{
	global $extrp_settings;
	if ( empty( $maxchars ) )
		$maxchars = apply_filters( 'extrp_max_chars', $extrp_settings['max_char'] );
	return absint( $maxchars );
}

function extrp_limit_count_id()
{
	$limit = 1;
	return apply_filters( 'extrp_limit_count_id', (int) $limit );
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