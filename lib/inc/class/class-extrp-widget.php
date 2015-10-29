<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

class EXTRP_Widget extends WP_Widget
{
	function __construct()
	{
		$widget_ops = array( false, 'description' => __( 'A related posts on your sidebar.', 'extrp' ) );
		parent::__construct( false, 'EXTRP Related Posts', $widget_ops );
	}

	function widget( $args, $instance )
	{
		global $post, $extrp_settings, $extrp_sanitize;
		
		if ( is_404() || is_home() || is_front_page() )
			return;
		
		$post_id = ( null === $post->ID ) ? get_the_ID() : (int) $post->ID;
		
		extract( $args );

		$default_setting = extrp_default_setting( 'shortcode' );

	    $set = wp_parse_args( $instance, $default_setting );
	
		$a = $extrp_sanitize->sanitize( $set );

		$result = extrp_create_html( 
			$a['relatedby'], 
			$post_id, 
			$a['single'], 
			$a['posts'], 
			$a['post_date'], 
			$a['subtitle'], 
			$a['randomposts'], 
			$a['titlerandom'], 
			$a['title'], 
			$a['desc'], 
			$a['image_size'], 
			$a['display'], 
			$a['shape'], 
			$a['crop'], 
			$a['heading'], 
			$a['postheading'], 
			$a['post_excerpt'], 
			$a['maxchars'], 
			$a['highlight']['hl'], 
			$a['highlight']['hlt'], 
			$a['relevanssi'], 
			$a['post__in'], 
			$a['post__not_in'], 
			'shortcode', 
			'widget' 
		); 
		
		if ( '' == $result )
			return;
		
		if ( $extrp_settings['active'] )
			add_filter( 'the_content', 'extrp_filter_the_content', 10 );
		
		$title         = apply_filters( 'widget_title', $result['subtitle'] );
		$title_section = ( '' != $title ) ? $before_title . $title . $after_title : '';

		printf( '%1$s<div class="widget_form_extrp">%2$s%3$s</div>%4$s',
			$before_widget,
			$title_section,
			$result['result'],
			$after_widget
		);
	}

	function update( $input, $old_input )
	{
		global $extrp_sanitize, $extrp_settings;

		$extrp_data = $extrp_sanitize->big_data(); 
		$id = $extrp_sanitize->extrp_multidimensional_search( $extrp_data, array('parameter'=>'post_type') );
		
		$post_type = [];
		foreach ( $extrp_data[ $id ]['optional'] as $type) :
			if ( isset( $input['post_type_' . $type] ) )
			{
				unset( $input['post_type_' . $type] );
				$post_type[] = $type;
			}
		endforeach;
		
		if ( ! array_filter( $post_type ) )
			$post_type[] = 'post';
		
		$input['post_type'] = $post_type;
		
		$input['post_date'] = array(
			isset( $input['post_date_show_date'] ) ? $input['post_date_show_date'] : '',
			isset( $input['post_date_time_diff'] ) ? $input['post_date_time_diff'] : ''
		);
		
		if ( isset( $input['highlight'] ) )
		{
			if ( $extrp_sanitize->highlight_name( $input['highlight'] ) )
			{
				if ( is_array( $input['highlight'] ) ) :
					$input['hl'] = $input['highlight']['hl'];
				else :
					$input['hl'] = $input['highlight'];	
				endif;
					
				$input['hlt'] = isset( $input['add']['hl_val_' . $input['hl']] ) ? $input['add']['hl_val_' . $input['hl']] :   $input['hl'];

				if ( 'no' != $input['hlt'] ) {
					if ( 'col' == $input['hlt'] || 'bgcol' == $input['hlt'] )
						$input['hlt'] = $extrp_sanitize->sanitize_hex_color( $input['hlt'] );
					if ( 'css' == $input['hlt'] || 'class' == $input['hlt'] )
						$input['hlt'] = sanitize_text_field( $input['hlt'] );
				}
				
				$input['highlight'] = array(
					 'hl' => sanitize_text_field( $input['hl'] ),
					'hlt' => sanitize_text_field( $input['hlt'] ) 
				);
			}
		}

		$new_input = $old_input;
		$new_input = [];
		$default = extrp_default_setting( 'shortcode' );
		
		$keys = array_keys( $default );
		
		foreach ( $keys as $k ) :
			if ( isset( $input[ $k ] ) )
				$new_input[ $k ] = $input[ $k ];
			else
				$new_input[ $k ] = false;
		endforeach;

		return $extrp_sanitize->sanitize( $new_input );
	}

	function form( $instance )
	{
		global $extrp_settings, $extrp_sanitize;

		$this->set = extrp_default_setting( 'shortcode' );

		$this->exclude = array( 1, 7, 14, 18, 19, 20, 22, 30, 31, 32, 33, 34, 35, 36 );
		
		$this->data = $extrp_sanitize->big_data();

		if ( $instance ) 
		{
			$instance = $extrp_sanitize->sanitize( $instance );
			foreach ( $this->data as $key => $value ) :
			
				$normal = isset( $instance[ $value['parameter'] ] ) ? $instance[ $value['parameter'] ] : $value['normal'];
				
				$this->data[ $key ]['id']          = $value['id'];
				$this->data[ $key ]['normal']      = $normal;
				$this->data[ $key ]['parameter']   = $value['parameter'];
				$this->data[ $key ]['optional']    = $value['optional'];
				$this->data[ $key ]['subtitle']    = $value['subtitle'];
				$this->data[ $key ]['description'] = $value['description'];
				$this->data[ $key ]['group']       = $value['group'];
				$this->data[ $key ]['subgroup']    = $value['subgroup'];
				$this->data[ $key ]['lang']        = $value['lang'];
			endforeach;
			
			$data = $this->data;
			
		} else {
			$data = $this->data;
		}

		$key = array_keys( $data );
		$plugin_data    = get_plugin_data( EXTRP_PLUGIN_FILE );
		$plugin_version = 'v' . $plugin_data['Version'];
		printf( '<div class="extrp-widget-form"><label for="%1$s" class="hidden">%2$s</label><input id="%1$s" name="%3$s" type="hidden" value="%2$s" />',
			$this->get_field_id( 'title_extrp' ),
			esc_html( $plugin_version ),
			$this->get_field_name( 'title_extrp' )
		);
		
		foreach ( $key as $id ) :

			$subtitle    = $data[ $id ]['subtitle'];
			$parameter   = $data[ $id ]['parameter'];
			$normal      = $data[ $id ]['normal'];
			$optional    = $data[ $id ]['optional'];
			$description = $data[ $id ]['description'];
			
			if ( 'post_type' == $parameter || 'post_date' == $parameter )
			{
				$input_field = '';
				if ( ! is_array( $normal ) )
					$normal = array( $normal );
				foreach ( $optional as $k => $v ) :
					
					$checked = in_array( $k, $normal ) ? 'checked="checked"' : '';
					$desc = ( ! empty( $v ) ) ? $v : $k;
					$input_field .= sprintf( '<input id="%1$s" name="%2$s" type="checkbox" value="%3$s" %4$s><label for="%1$s">%5$s</label><br>',
						$this->get_field_id( $parameter . '_' . $k ),
						$this->get_field_name( $parameter . '_' . $k ),
						esc_attr( $k ),
						$checked,
						ucwords( esc_attr( $desc ) ) 
					);
				endforeach;
				
				printf( '<p><label for="%1$s">%2$s</label><br>%3$s',
				$this->get_field_id( $parameter ),
				$subtitle,
				$input_field
				);
			}
			
			if ( 'subtitle' == $parameter || 'titlerandom' == $parameter )
				printf( '<p><input id="%1$s" name="%2$s" type="text" value="%3$s" /> <label for="%1$s">%4$s</label></p>', $this->get_field_id( $parameter ), $this->get_field_name( $parameter ), $normal, $subtitle );
			
			if ( 'posts' == $parameter || 'maxchars' == $parameter )
				printf( '<p><input class="small-text" id="%1$s" name="%3$s" type="number" value="%4$s" /> <label for="%1$s">%2$s</label></p>', $this->get_field_id( $parameter ), $subtitle, $this->get_field_name( $parameter ), $normal );
			
			if ( 'post__in' == $parameter || 'post__not_in' == $parameter )
				printf( '<p><label for="%1$s">%2$s</label><textarea class="large-text" id="%1$s" name="%3$s">%4$s</textarea></p>', $this->get_field_id( $parameter ), $subtitle, $this->get_field_name( $parameter ), $extrp_sanitize->create_list_post_ids( $normal ) );
			
			if ( 'relatedby' == $parameter || 'postheading' == $parameter || 'display' == $parameter || 'shape' == $parameter || 'image_size' == $parameter || 'highlight' == $parameter ) :

				printf( '<p><select id="%1$s" name="%2$s" class="%3$s-select">', 
					$this->get_field_id( $parameter ),
					$this->get_field_name( $parameter ),
					$parameter
				);
				
				if ( 'image_size' == $parameter ) :
					$selected = ( false == $normal ) ? 'selected="selected"' : '';
					printf( '<option value="%1$s" %2$s>%3$s</option>',
						false,
						$selected,
						__( 'No Thumbnail', 'extrp' )
					);
				endif;
				
				foreach ( $optional as $k => $v ) :
					$classhl = '';
					if ( 'image_size' == $parameter || 'highlight' == $parameter )
							$val = $k;
						else
							$val = $v;
						
					$selected = $normal == $val ? 'selected="selected"' : '';
					if ( 'highlight' == $parameter ) {
						$classhl .= 'class="select-' . $k . '"';
						$selected = $extrp_sanitize->highlight( $normal )['hl'] == $val ? ' selected="selected"' : '';
					}
					
					printf( '<option %1$s value="%2$s" %3$s>%4$s</option>',
						$classhl,
						$val,
						$selected,
						ucwords( esc_attr( $val ) )
					);
				endforeach;
				
				printf( '</select> <label for="%1$s">%2$s</label></p>',
					$this->get_field_id( $parameter ),
					$subtitle
				);
				
				if ( 'highlight' == $parameter ) {
					echo extrp_hl_input( $extrp_sanitize->highlight( $normal ), $parameter, $optional, $this->get_field_name( 'add' ) );
				}
				
			endif;
			
			if ( 'single' == $parameter || 'title' == $parameter || 'desc' == $parameter || 'randomposts' == $parameter || 'crop' == $parameter || 'relevanssi' == $parameter || 'post_excerpt' == $parameter ) :
				$selected = ( true == $normal ) ? 'checked="checked"' : '';
				printf( '<p><input type="checkbox" id="%1$s" name="%2$s" value="%3$s" class="widget-%4$s" %5$s/> <label for="%1$s"><span>%6$s</span></label> ',
					$this->get_field_id( $parameter ),
					$this->get_field_name( $parameter ),
					true,
					$parameter,
					$selected,
					$subtitle
				);
				printf( '</p>');
			endif;
			
		endforeach;
		printf( '</div>');
	}
}

function extrp_register_widgets()
{
	register_widget( 'EXTRP_Widget' );
}

add_action( 'widgets_init', 'extrp_register_widgets' );