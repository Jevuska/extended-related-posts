<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

function extrp_register_shortcodes()
{
	add_shortcode( 'jv-related-posts', 'extrp_related_posts_shortcode' );
}

function extrp_related_posts_shortcode( $atts = null, $result = '' )
{
	global $post, $extrp_settings, $extrp_sanitize;

	if ( is_home() || is_front_page() )
		return;
	
	$post_id = ( null === $post->ID ) ? get_the_ID() : (int) $post->ID;
	
	$option = 'shortcode';
	
	$default_setting = shortcode_atts( extrp_default_setting( 'shortcode' ), $atts, 'jv-related-posts' );

	$a = $extrp_sanitize->sanitize( $default_setting );

	$result = extrp_create_html( $a['relatedby'], $post_id, $a['single'], $a['posts'], $a['post_date'], $a['subtitle'], $a['randomposts'], $a['titlerandom'], $a['title'], $a['desc'], $a['image_size'], $a['display'], $a['shape'], $a['crop'], $a['heading'], $a['postheading'], $a['post_excerpt'], $a['maxchars'], $a['highlight']['hl'], $a['highlight']['hlt'], $a['relevanssi'], $a['post__in'], $a['post__not_in'], $option ); 
	
	if ( $extrp_settings['active'] )
		add_filter( 'the_content', 'extrp_filter_the_content', 10 );

	$author_id         = $post->post_author;
	$can_publish_posts = user_can( $author_id, extrp_capability_filter() );
	if ( ! $can_publish_posts )
		return; //well, users that can't publish posts, no cake for you. (default)	
	
	return $result;
}