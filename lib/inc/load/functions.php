<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

function extrp_theme_setup()
{
	global $extrp_settings,$extrp_sanitize;
	
	if ( ! function_exists( 'add_theme_support' ) )
		add_theme_support( 'post-thumbnails' );
	
	$size = $extrp_sanitize->customsize( $extrp_settings['customsize'] );
	if ( false == $size )
		return;
	
		for ( $i = 0 ; $i < count( $size ) ; $i++ ) :
			$tag[ $i ]    = $size[ $i ]['size'];
			$width[ $i ]  = $size[ $i ]['width'];
			$height[ $i ] = $size[ $i ]['height'];
			$crop[ $i ]   = $size[ $i ]['crop'];
			if ( $tag[ $i ] )
				add_image_size( $tag[ $i ], $width[ $i ], $height[ $i ], $crop[ $i ] );
		endfor;
}

function update_with_relevanssi_option()
{
	global $extrp_settings, $extrp_sanitize;

	if ( ! $extrp_settings )
		$extrp_settings = extrp_default_setting();
	
	if ( ! get_option( 'extrp_with_relevanssi' ) )
		update_option( 'extrp_with_relevanssi', false );
	
	if ( ! function_exists( 'relevanssi_do_query' ) )
	{
		$result = array();
		foreach ( $extrp_settings as $key => $value ) :
			if ( isset( $key ) ) :
				if ( 'relevanssi' == $key )
					$result['relevanssi'] = false;
				else
					$result[ $key ] = $value;
			endif;
		endforeach;
		
		if ( true == get_option( 'extrp_with_relevanssi' ) )
		{
			update_option( 'extrp_with_relevanssi', false );
			update_option( 'extrp_option', $result );
		}
	}
	else
	{
		if ( false == get_option( 'extrp_with_relevanssi' ) )
			update_option( 'extrp_with_relevanssi', true );
	}
}

function extrp_set_noimage()
{
	global $extrp_settings;

	if ( ! is_admin() )
		return;

	if ( ! isset( $extrp_settings['noimage']['default'] ) )
		return;

	if ( '' == $extrp_settings['noimage']['default'] || false == wp_get_attachment_image_src( $extrp_settings['noimage']['attachment_id'], 'full', false ) ) 
	{
		$bail_noimage = extrp_bail_noimage();
		update_option( 'extrp_option', $bail_noimage );
	}
}

function extrp_bail_noimage()
{
	global $extrp_settings, $extrp_sanitize;
	$extrp_noimage = [];
	$args = array(
		'post_id' => null,
		'src'     => esc_url_raw( EXTRP_URL_PLUGIN_IMAGES . 'default.png' ),
		'size'    => 'thumbnail'
	);
		
	$thumbnail     = extrp_thumbnail( $args );
	$extrp_noimage = $thumbnail->attachment_external();
		
	$output['noimage'] = array(
		'attachment_id' => absint( $extrp_noimage['attachment_id'] ),
		'default'       => esc_url_raw( $extrp_noimage['default'] ),
		'full_src'      => esc_url_raw( $extrp_noimage['default'] ),
		'size'          => $extrp_sanitize->size( $extrp_noimage['size'] ),
		'src'           => esc_url_raw( $extrp_noimage['src'] ),
		'width'         => intval( $extrp_noimage['width'] ),
		'height'        => intval( $extrp_noimage['height'] ),
		'crop'          => wp_validate_boolean( $extrp_noimage['crop'] )		
	);
	$args = wp_parse_args( $output, $extrp_settings );

	return $args;
}

function extrp_filter_the_content( $content )
{
	global $post;
	$content = $content . extrp_set_related_posts( $post );
	return $content;
}

function extrp_related_posts( $args = '' )
{
	global $post;
	
	$content = extrp_set_related_posts( $post, $args );
	if ( empty( $content ) )
		return;
	echo $content;
}

function extrp_set_related_posts( $post, $arg = '' )
{
	$a = extrp_do_your_settings( $post, $arg );
	
	$post_id = ( null === $post->ID ) ? get_the_ID() : (int) $post->ID;
	
	remove_filter( 'the_content', 'extrp_filter_the_content' );

	$result = extrp_create_html( $a['relatedby'], $post_id, $a['single'], $a['posts'], $a['post_date'], $a['subtitle'], $a['randomposts'], $a['titlerandom'], $a['post_title'], $a['desc'], $a['image_size'], $a['display'], $a['shape'], $a['crop'], $a['heading'], $a['postheading'], $a['post_excerpt'], $a['maxchars'], $a['highlight'], $a['relevanssi'], $a['post__in'], $a['post__not_in'] );

	add_filter( 'the_content', 'extrp_filter_the_content', 10 );

	if ( ! $result )
		return;
	return $result;
}

function extrp_do_your_settings( $post, $arg = '' )
{
	global $extrp_settings, $extrp_sanitize;
	$b = $extrp_sanitize->sanitize( $extrp_settings );
	
	if ( ! $b['thumb'] )
		$b['image_size'] = false;
	
	if ( !empty( $arg ) ) :
		$d     = ( is_array( $arg ) ) ? $arg : array(
			 'post__not_in' => $extrp_sanitize->post_ids( $arg ) 
		);
		$i     = extrp_default_setting( 'shortcode' );
		$new_d = $extrp_sanitize->sanitize( $d );
		foreach ( $b as $k => $v ) :
			if ( ! array_key_exists( $k, $i ) )
				unset( $b[ $k ] );
			foreach ( $new_d as $kk => $vv )
			{
				if ( ! array_key_exists( $kk, $i ) )
					unset( $new_d[ $kk ] );
			}
		endforeach;
		
		$b = wp_parse_args( $new_d, $b );
		
	endif;

	return $b;
}

function extrp_create_html( $relatedby = '', $post_id = '', $single = '', $postcount = '', $date = '', $subtitle = '', $randomposts = '', $titlerandom = '', $title = '', $desc = '', $size = '', $display = '', $shape = '', $crop = '', $heading = '', $postheading = '', $snippet = '', $maxchars = '', $highlight = '', $relevanssi = false, $post_in = '', $post_not_in = '', $option = '', $widget = '' )
{
	global $extrp_settings;

	if ( $single && ! is_single() )
		return false;
	
	$expiration = $extrp_settings['expire'];
	$respon = extrp_json( $relatedby, $post_id, $postcount, $date, $randomposts, $size, $title, $shape, $postheading, $crop, $expiration, $snippet, $maxchars, $highlight, $relevanssi, $post_in, $post_not_in, $option );
	
	if ( ! $respon )
		return false;
	
	$json = json_decode( $respon );
	$post = $json->post_id->$post_id;
	$rb   = $json->post_id->$post_id->relatedby;
				
	$ul_f = '';
	$ul_l = '';
	
	if ( '' != $size && 'list' == $display )
		$display = 'left_wrap';
	
	if ( ! $size )
	{
		$ul_f = '<ul>';
		$ul_l = '</ul>';
	}
	
	if ( $subtitle && $titlerandom )
	{
		$subtitle = ( 'random' != $rb ) ? $subtitle : $titlerandom;
	}
	else if ( $subtitle )
	{
		$subtitle = ( 'random' != $rb ) ? $subtitle : '';
	}
	else if ( $titlerandom )
	{
		$subtitle = ( 'random' != $rb ) ? '' : $titlerandom;
	}
	else
	{
		$subtitle = '';
	}
	
	$subtitles = '';
	if ( ! empty ( $subtitle ) && 'widget' != $widget )
		$subtitles = sprintf( '<%1$s>%2$s</%1$s>', esc_html( $heading ), esc_html__( $subtitle, 'extrp' ) );
	
	$post_header = sprintf( '<div id="extrp" class="extrp" data="related by %s">%s<div class="extrp-%s" >%s', esc_attr( $rb ), $subtitles, esc_attr( $display ), $ul_f );
	$post_footer = sprintf( '%s</div><div class="clearfix"></div></div>', $ul_l );
	
	$post_content = array();
		foreach ( $post->post_content as $_post ) :
			$item        = '';
			$thumbnail   = '';
			$description = '';
			$post_title  = '';
			$item_title  = '';
			$post_date = '';

			if ( ! empty( $_post->post_title ) )
				$item_title = sprintf( '<%1$s><a href="%2$s" title="%3$s">%4$s</a></%1$s>', 
				$_post->post_title->postheading, 
				$_post->post_title->permalink, 
				$_post->post_title->attr, 
				$_post->post_title->title );
			
			if ( '' != $_post->post_thumbnail ) 
			{
				$class = 'extrp-shape-' . $shape;
				$thumbnail .= $_post->post_thumbnail; 
			}
			
			$post_title .= $item_title;

			if ( $desc )
				$description .= $_post->post_excerpt;

			if ( ! empty( $_post->post_date ) )
			{
				$time = '';
				if ( in_array( 'show_date', $date ) )
				{
					$time .= '<span class="posted-on"><time class="entry-date published" datetime="%1$s">%2$s</time></span>';
				}
				
				if ( in_array( 'time_diff', $date ) )
				{
					$time .= '<span class="posted-on"><i>%3$s</i></span>';
				}
				
				$time .= '<br class="clearfix">';

				$post_date = sprintf( $time,
					$_post->post_date->updated,
					$_post->post_date->published,
					$_post->post_date->time_diff . ' ' . __( 'ago', 'extrp' )
				);
			}
	
			switch ( $display )
			{
				case 'inline';
					$item .= extrp_inline_style( $post_date, $thumbnail, $post_title, $description );
					break;
				
				case 'left';
				case 'right';
					$item .= extrp_float_style( $post_date, $thumbnail, $post_title, $description, $display );
					break;
				
				case 'left_wrap';
				case 'right_wrap';
					$item .= extrp_wrap_style( $post_date, $thumbnail, $post_title, $description, $display );
					break;
					
				case 'list_group';
					$item .= extrp_list_group_style( $post_date, $thumbnail, $post_title, $description );
					break;
				
				default:
					$item .= extrp_list_style( $post_date, $post_title, $description );
					break;
			}
			$post_content[] = $item;
		endforeach;

	if ( ! array_filter( $post_content ) )
		return false;
	
	$post_content = implode( '', array_unique( $post_content ) );
	
	if ( empty( $post_content ) )
		return false;
	
	$result = "$post_header $post_content $post_footer";
	
	if ( 'widget' == $widget )
		return array(
			'subtitle'  => $subtitle,
			'result' => $result
		);
		
	return $result;
}

function extrp_json( $relatedby, $post_id, $postcount, $date = '', $randomposts = '', $size = '', $title = '', $shape = '', $postheading = '', $crop = '', $expiration, $snippet, $maxchars = '', $highlight = '', $relevanssi = false, $post_in = '', $post_not_in = '', $option = '' )
{
	global $extrp_settings;
	remove_filter( 'extrp_option_related_by', 'do_related_by_random' );
	remove_filter( 'extrp_option_related_by', 'do_related_by_split_title' );
	remove_filter( 'extrp_option_related_by', 'do_related_by_tag' );
	remove_filter( 'extrp_option_related_by', 'do_related_by_cat' );
	remove_filter( 'the_content', 'extrp_filter_the_content' );
	if ( ! $relevanssi ) :
		remove_filter( 'the_posts', 'relevanssi_query', 10 );
		remove_filter( 'posts_request', 'relevanssi_prevent_default_request', 10 );
		remove_filter( 'query_vars', 'relevanssi_query_vars', 10 );
	endif;
	$respon = false;
	$cache = false;
	
	if ( $extrp_settings['cache'] ) :
		$cache = true;
		if ( 'shortcode' == $option )
			$respon = get_transient( 'extrp_cache_post_shortcode_' . $post_id );
		else
			$respon = get_transient( 'extrp_cache_post_' . $post_id );
	endif;

	if ( false == $respon) :
		$default = false;
		if ( ! $default ) :
			$post_ids  = extrp_search_postids_db( $post_id, $postcount );
			$exist_cat = extrp_get_list_cat_ids( $post_id );
			$exist_tag = array_filter( extrp_get_list_tag_ids_arr( $post_id ) );
			$q         = extrp_get_the_title( $post_id );
			
			if ( 'shortcode' == $option && is_category() )
				$q = single_cat_title( '', false );

			if ( 'shortcode' == $option && is_tag() )
				$q = single_tag_title( '', false );
			
			if ( 'shortcode' == $option && is_search() )
				$q = get_search_query();
			
			$array_relatedby = array_keys( extrp_related_by( $post_id, $q, $postcount ) );
			
			$array_rb = array_unique(
				wp_parse_args(
					$array_relatedby,
					array( $relatedby )
				)
			);

			if ( ! $randomposts )
				unset( $array_rb[5] );

			foreach ( $array_rb as $rb ) :
			
				if ( 'tag' == $rb && ! $exist_tag )
					continue;
				
				if ( 'splittitle' == $rb && ! $post_ids )
					continue;
				
				$query = jv_query( $rb, $post_id, $q, $postcount, $post_in, $post_not_in, $relevanssi );
				if ( 0 < $query->found_posts ) :
					$relatedby = $rb;
					$default   = true;
					break;
				endif;
				
				if ( 0 == $query->found_posts && '' != $query->post ) :
					$query     = jv_query( $rb, $post_id, $q, $postcount, $post_in, $post_not_in, false );
					$relatedby = $rb;
					$default   = true;
					break;
				endif;
				
			endforeach;
		endif;

		wp_reset_query();
		wp_reset_postdata();
		if ( $default ) :
		    $post_date = array();
			$post_thumbnail = '';
			$post_title = array();
			$post_content = array();
			if ( $query->have_posts() ) :
				while ( $query->have_posts()) :
					$query->the_post();
					$id = get_the_ID();

					if ( array_filter( $date ) )
					{
						$post_date = array(
							'published' => get_the_date( '', $id ),
							'updated' => esc_attr( get_post_modified_time( 'c', true, $id ) ),
							'time_diff' => esc_html( human_time_diff( get_post_time( 'U', true, $id ), current_time ('timestamp') ) )
						);
					};
			
					if ( $title ) {
						$post_title = array(
							'postheading' => esc_html( $postheading ),
							'permalink' => esc_url( get_permalink( $id ) ),
							'attr' => the_title_attribute( 'echo=0&post=' . $id ),
							'title' => get_the_title( $id )
						);
					};
					
					if ( '' != $size )
					{
						$class = 'extrp-shape-' . $shape;
						$post_thumbnail = extrp_get_the_post_thumbnail( $id, $size, $crop, $class );
					}
			
					if ( $relevanssi )
					{
						$post_excerpt = $query->post_excerpt ? 
							get_the_excerpt() :
							extrp_get_excerpt( $id, $maxchars, $q, $hl, $hlt, $snippet );
							
					}
					else
					{
						$post_excerpt = extrp_get_excerpt( $id, $maxchars, $q, $highlight, $snippet );
					}
					$post_content[] = array (
						'id' => $id,
						'post_date' => $post_date,
						'post_title' => $post_title,
						'post_excerpt' => $post_excerpt, 
						'post_thumbnail' => $post_thumbnail 
					);
				endwhile;
				
				$c['post_id'] = array(
					$post_id => array(
							'relatedby' => $relatedby,
							'post_content' => $post_content,
					) 
				);
				$respon = wp_json_encode( $c );
				
				if ( $cache ) :
					if ( 'shortcode' == $option )
						set_transient( 'extrp_cache_post_shortcode_' . absint( $post_id ), $respon, absint( $expiration ) );
					else
						set_transient( 'extrp_cache_post_' . absint( $post_id ), $respon, absint( $expiration ) );
				endif;
			else :
				$respon = false;
			endif;
		endif;
		wp_reset_query();
		wp_reset_postdata();
	endif;
	if ( ! $respon )
		return false;
	
	return $respon;
}

function jv_query( $relatedby, $post_id, $q, $postcount, $post_in = '', $post_not_in = '', $relevanssi = false )
{
	global $extrp_settings;
	
	$s = extrp_related_by( $post_id, $q, $postcount, $relatedby );
	$post_ids  = extrp_search_postids_db( $post_id, $postcount );
	$post__in = array_filter( $post_in ) ? array_diff( $post_in, array(
		 $post_id 
	) ) : array();
	
	$post__not_in = ( '' == $post_not_in ) ? array( $post_id ) : array_unique( 
		wp_parse_args( array( $post_id ), $post_not_in )
	);
	
	$post__in_key = ( 'post__in' != $s[0] ) ? 'post__in' : '';
	$post__in_val = ( 'post__in' != $s[0] ) ? $post__in : '';

	$default = array(
		'related_by'          => $relatedby,
		'post_type'           => extrp_post_type(),
		'posts_per_page'      => $postcount,
		'post_status'         => 'publish',
		'post__not_in'        => $post__not_in,
		'ignore_sticky_posts' => 1,
		$s[0]                 => $s[1],
		$post__in_key         => $post__in_val
	);

	$additional = apply_filters( 'extrp_wp_query', array() );
	$args = wp_parse_args( $additional, $default );
	$query = new WP_Query( $args );
	if ( $relevanssi )
		relevanssi_do_query( $query );
	return $query;
}

function extrp_related_by( $post_id, $q, $postcount, $relatedby = '' )
{
	$relatedby = apply_filters( 'extrp_option_related_by', $relatedby );
	$option    = array(
		'title' => array(
			's',
			$q 
		),
		'cat' => array(
			'cat',
			extrp_get_list_cat_ids( $post_id ) 
		),
		'tag' => array(
			'tag__in',
			extrp_get_list_tag_ids_arr( $post_id ) 
		),
		'splittitle' => array(
			'post__in',
			extrp_search_postids_db( $post_id, $postcount ) 
		),
		'random' => array(
			'orderby',
			'rand' 
		) 
	);
	if ( ! array_key_exists( $relatedby, $option ) )
		return $option;
	return $option[ $relatedby ];
}

function extrp_enqueue_scripts()
{
	global $extrp_settings;
	$css = $extrp_settings['css'];
	if ( $css )
		wp_enqueue_style( 'extrp', extrp_style_uri(), array(), EXTRP_PLUGIN_VERSION );
}

function extrp_style_uri()
{
	$path_css = EXTRP_URL_PLUGIN_CSS;
	return $path_css . 'style.css';
}

function extrp_get_excerpt( $post_id, $maxchars = '', $q = '', $highlight = '', $snippet = '' )
{
	$post_id = ( null === $post_id ) ? get_the_ID() : (int) $post_id;
	
	if ( $snippet ) :
		$args = array(
			'post_id'   => $post_id,
			'q'         => $q,
			'highlight' => $highlight,
			'maxchars'  => $maxchars
		);
		$extrp_excerpt = extrp_excerpt( $args );
		$excerpt = $extrp_excerpt->get();
	else :
		$excerpt = extrp_max_charlength(
			$maxchars, 
			strip_shortcodes( get_the_excerpt() ) 
		);
	endif;
	
	return $excerpt;
}

function extrp_max_charlength( $maxchars, $excerpt )
{
	$maxchars++;
	$r = '';
	if ( mb_strlen( $excerpt ) > $maxchars ) :
		$subex = mb_substr( $excerpt, 0, $maxchars - 5 );
		$exwords = explode( ' ', $subex );
		$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
		if ( $excut < 0 ) :
			$r .= mb_substr( $subex, 0, $excut );
		else :
			$r .= $subex;
		endif;
		$r .= '...';
	else :
		$r .= $excerpt;
	endif;
	return $r;
}

function extrp_get_list_cat_ids( $post_id )
{
	$categories = get_the_category( $post_id );
	$arr_cat    = array();
	foreach ( $categories as $category ) :
		$arr_cat[] = $category->term_id;
	endforeach;
	$list_cat_id = implode( ',', $arr_cat );
	return $list_cat_id;
}

function extrp_get_list_tag_ids_arr( $post_id )
{
	$tags = wp_get_post_tags( $post_id, array(
		 'fields' => 'ids' 
	) );
	if ( array_filter( $tags ) )
		return $tags;
	return array();
}

function extrp_search_postids_db( $post_id, $postcount )
{
	$limit = extrp_limit_count_id( 1 );
	$arr   = extrp_split_title( $post_id );
	if ( array_filter( $arr ) ) :
		$post_ids = extrp_sql_post_ids( $arr, $limit, $post_id );

		if ( (int) 0 == count( $post_ids ) )
			return false;
		
		if ( (int) 1 == count( $post_ids ) )
			$post_ids = extrp_sql_post_ids( $arr, $postcount, $post_id );
		
		if ( (int) 2 == count( $post_ids ) || (int) 3 == count( $post_ids ) )
			$post_ids = extrp_sql_post_ids( $arr, (int) 2, $post_id );
		
		return $post_ids;
	endif;
	return false;
}

function extrp_sql_post_ids( $arr, $limit, $post_id )
{
	global $wpdb;

	if ( ! array_filter( $arr ) )
		return false;
	
	$limit = intval( $limit );
	$post_type = extrp_post_type();
	$term      = array();
	for ( $i = 0; $i < count( $post_type ); $i++ )
	{
		foreach ( $arr as $k )
		{
			$k      = $wpdb->esc_like( $k );
			$k      = "%$k%";
			$sql    = "(SELECT ID FROM $wpdb->posts WHERE ID != %d AND post_status = 'publish' AND post_type = %s AND post_title LIKE %s LIMIT %d)";
			$term[] = $wpdb->prepare( $sql, $post_id, $post_type[ $i ], $k, $limit );
		}
	}
							
	if ( array_filter( $term ) )
	{
		$sql    = implode( ' union all ', $term );
		$result = $wpdb->get_col( $sql );
	}

	if ( $result )
		return array_values( array_unique( $result ) );
	return false;
}

function extrp_post_type()
{
	global $extrp_settings, $extrp_sanitize;
	$post_type = apply_filters( 'extrp_post_type', $extrp_settings['post_type'] );
	return $extrp_sanitize->post_types( $post_type );
}

function extrp_get_the_title( $post_id )
{
	$post_title = join( ' ', extrp_split_title( $post_id ) );
	return $post_title;
}

function extrp_split_title( $post_id )
{
	global $extrp_sanitize;
	
	$post_title = get_the_title( absint( $post_id ) );
	
	if ( ! empty( $post_title ) ) :
		$post_title = $extrp_sanitize->remove_char( $post_title );
		$array      = explode( ' ', $post_title );
		return extrp_filter_stopwords( $array );
	endif;
	return false;
}

function extrp_filter_stopwords( $r )
{
	$stopwords = extrp_stopwords();
	if ( false != $stopwords ) :
		$split_title = array();
		
		for ( $i = 0; $i < count( $r ); $i++ ) :
			if (  3 < mb_strlen( $r[ $i ] ) && in_array( $r[ $i ], $stopwords ) === false )
				$split_title[] = $r[ $i ];
		endfor;
		
		if ( array_filter( $split_title ) )
			return $split_title;
	endif;
	return false;
}

function extrp_stopwords()
{
	global $extrp_settings, $extrp_sanitize;
	
	$stopword = $extrp_settings['stopwords'];
	
	if ( ! empty( $stopword ) )
	{
		$words     = explode( ',', _x( $stopword, 'Comma-separated list of search stopwords in your language' ) );
		$stopwords = array();
		foreach ( $words as $word )
		{
			$word = trim( $word, "\r\n\t " );
			$word = $extrp_sanitize->remove_char( $word );
			if ( 0 == mb_strlen( $word ) )
				continue;
			$stopwords[] = $word;
		}
		return $stopwords;
	}
	return false;
}

function extrp_get_the_post_thumbnail( $post_id, $size = '', $crop = '', $img_class = '' )
{

	global $extrp_settings, $extrp_sanitize;
	$args = array(
		'post_id'   => absint( $post_id ),
		'size'      => $extrp_sanitize->size( $size ),
		'crop'      => $extrp_sanitize->customsize_crop( $crop ),
		'img_class' => esc_attr( $img_class )
	);

	$thumb = extrp_thumbnail( $args );
	
	$get_attachment_link = apply_filters( 'extrp_get_the_post_thumbnail', $thumb->get_attachment_link(), $post_id, $size , $crop , $img_class );
	
	return $get_attachment_link;
}

function extrp_get_attach_id( $src, $post_id = 0 )
{
	global $extrp_sanitize;

	$args = array(
		'src'     => esc_url_raw( $src ),
		'post_id' => absint( $post_id )
	);
	
	$thumb = extrp_thumbnail( $args );
	return $thumb->get_attachment_id( $src );
}