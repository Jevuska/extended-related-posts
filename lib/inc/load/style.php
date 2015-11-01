<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

function extrp_inline_style( $post_date, $thumbnail, $post_title, $description )
{
	$html = sprintf( '<div class="extrp-col"><div>%s</div><div>%s%s<p>%s</p></div></div>', $thumbnail, $post_title, $post_date, $description );
	return $html;
}

function extrp_list_group_style( $post_date, $thumbnail, $post_title, $description )
{
	$html = sprintf( '<div class="item col-xs-3 col-lg-3"><div class="thumbnail">%s<div class="caption">%s%s<p>%s</p></div></div></div>', $thumbnail, $post_title, $post_date, $description );
	return $html;
	
}

function extrp_float_style( $post_date, $thumbnail, $post_title, $description, $display )
{
	$thumb = '';
	if ( empty( $thumbnail ) ) :
		$class = 'extrp-text-body';
		$html  = '<li class="extrp-text">';
	else :
		$class = 'extrp-media-body';
		$html  = '<div class="extrp-media">';
		$thumb = sprintf( '<div class="extrp-media-%s">%s</div>', esc_attr( $display ), $thumbnail );
	endif;
	
	if ( 'left' == $display )
		$html .= $thumb;
	
	$html .= sprintf( '<div class="%s">%s%s<p>%s</p></div>', esc_attr( $class ), $post_title, $post_date, $description );
	
	if ( 'right' == $display )
		$html .= $thumb;
	
	if ( empty( $thumbnail ) )
		$html .= '</li>';
	else
		$html .= '</div>';
	return $html;
}

function extrp_wrap_style( $post_date, $thumbnail, $post_title, $description, $display )
{
	$thumb = '';
	if ( empty( $thumbnail ) ) :
		$class = 'extrp-text-body';
		$html  = '<li class="extrp-text">';
	else :
		$class = 'extrp-media-body';
		$html  = '<div class="extrp-media">';
		$thumb = sprintf( '<div class="extrp-media-%s">%s</div>', esc_attr( $display ), $thumbnail );
	endif;
	
	$html .= sprintf( '<div class="%s">%s%s%s<span>%s</span></div>', esc_attr( $class ), $post_title, $post_date, $thumb, $description );

	if ( empty( $thumbnail ) )
		$html .= '</li>';
	else
		$html .= '</div>';
	
	return $html;
}

function extrp_list_style( $post_date, $post_title, $description )
{
	$html = sprintf( '<li>%s%s<p>%s</p></li>', $post_title, $post_date, $description );
	return $html;
}

?>