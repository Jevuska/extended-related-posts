<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

function extrp_sanitize()
{
	return new EXTRP_Sanitize;
}

class EXTRP_Sanitize
{
	public $attachment_id = 0;
	
	public function __construct()
	{
		$this->post_id   = null;
	}

	public function post_ids( $list )
	{
		$result = array();
		
		if ( is_array( $list ) )
			$arr = $list;
		else
			$arr = ( '' != $list ) ? explode( ',', $list ) : array();
		
		if ( array_filter( $arr ) )
		{
			foreach ( array_unique( $arr ) as $post_id ) :
				$post_id = absint( $post_id );
				
				if ( '' == $post_id )
					continue;
				
				if ( is_string( get_post_status( $post_id ) ) )
					$result[] = $post_id;
			endforeach;
			
			if ( array_filter( $result ) )
				$result = array_values( array_unique( $result ) );
		}
		return $result;
	}
	
	public function data_textarea( $lists )
	{
		$list = '';
		
		if ( '' == $lists )
			return $list;
		
		$result = array();
		
		if ( is_array( $lists ) )
		{
			foreach ( $lists as $id ) :
				$id = absint( $id );
				
				if ( '' == $id )
					continue;
				
				$result[] = $id;
			endforeach;
			
			if ( array_filter( $result ) )
				$list = implode( ',', array_values( array_unique( $result ) ) );
		}
		else
		{
			$words = explode( ',', _x( $lists, 'Comma-separated list of search stopwords in your language' ) );
			
			foreach ( $words as $word ) :
				$word = $this->remove_char( $word );
				if ( 0 == mb_strlen( $word ) )
					continue;
				
				$result[] = $word;
			endforeach;
			
			if ( array_filter( $result ) )
				$list = implode( ',', array_values( array_unique( $result ) ) );
		}
		return $list;
	}
	
	public function remove_char( $q )
	{
		if ( '' == $q )
			return $q;
		
		$q = sanitize_title_with_dashes( urldecode( $q ), '', 'save' );
		$q = wp_strip_all_tags( $q );
		$q = preg_replace( '/&#?[a-z0-9]+;/i','', $q );
		$q = preg_replace( '/[^%A-Za-z0-9 _-]/', ' ', $q );
		$q = preg_replace( '/&.+?;/', '', $q );
		$q = preg_replace( '/_+/', ' ', $q );
		$q = preg_replace( '/\s+/', ' ', $q );
		$q = preg_replace( '|-+|', ' ', $q );
		$q = htmlspecialchars( urldecode( trim( $q ) ) );
		return $q;
	}
	
	public function display( $display )
	{
		$list    = 'list';
		$display = sanitize_key( $display );
		if ( in_array( $display, $this->array_parameter( 'display' ) ) )
			return $display;
		return $list;
	}
	
	public function heading( $heading )
	{
		$h3 = 'h3';
		$heading = sanitize_key( $heading );
		if ( in_array( $heading, $this->array_parameter( 'heading' ) ) )
			return $heading;
		return sanitize_key( $h3 );
	}
	
	public function size( $size )
	{
		if ( in_array( $size, array_keys( $this->array_sizes() ) ) )
			return $size;
		return false;
	}
	
	public function shape( $shape )
	{
		$noshape = 'noshape';
		$shape   = sanitize_html_class( $shape );
		if ( in_array( $shape, $this->array_parameter( 'shape' ) ) )
			return $shape;
		return sanitize_html_class( $noshape );
	}
	
	public function relatedby( $relatedby )
	{
		$default   = 'title';
		$relatedby = sanitize_key( $relatedby );
		if ( in_array( $relatedby, $this->array_parameter( 'relatedby' ) ) )
			return $relatedby;
		return sanitize_key( $default );
	}
	
	public function customsize_key( $key )
	{
		$pattern = '/^[a-z_-]+$/';
		if ( preg_match( $pattern, $key ) )
			return $key;
		return false;
	}
	
	public function customsize_crop( $crop )
	{
		if ( ! is_array( $crop) )
			return wp_validate_boolean( $crop );

		$x_array =  array( 'left','center','right');
		$y_array = array( 'top', 'center', 'bottom');
		$x = ( in_array( $crop[0], $x_array ) ) ? $crop[0] : 'center';
		$y = ( in_array( $crop[1], $y_array ) ) ? $crop[1] : 'center';
		return array( $x, $y );
	}
	
	public function highlight( $hlg )
	{
		$hlg = ( is_array( $hlg ) ) ? array_values( $hlg ) : explode( '|', $hlg );
		
		$hl  = $this->highlight_name( $hlg[0] );
		$hlt = $hlg[1];
		
		$array   = array_keys( $this->array_parameter( 'highlight' ) );
		$noinput = array_slice( $array, 1, 3, true );
		
		if ( $hl != current( $array ) ) :
		
			if ( in_array( $hl, $noinput ) )
				$hlt = $hl;
			
			if ( strpos( $hl, 'col' ) )
				$hlt = $this->sanitize_hex_color( $hlt );
			
			if ( strpos( $hl, 'ss' ) ) :
				$hlt = sanitize_text_field( $hlt );
			endif;
			
		else :
		
			$hlt = 'no';
			
		endif;
		
		$highlight = array(
			'hl'  => sanitize_text_field( $hl ),
			'hlt' => sanitize_text_field( $hlt ) 
		);
		
		return $highlight;
	}
	
	
	public function highlight_name( $name )
	{
		$default = 'strong';
		if ( in_array( $name, array_keys( $this->array_parameter( 'highlight' ) ) ) )
			return $name;
		return $default;
	}
	
	public function sanitize_hex_color( $color )
	{
		$id = absint( $this->extrp_multidimensional_search( $this->big_data(), array( 'parameter' => 'highlight' ) ) );
		
		$default_color = $this->big_data()[ $id ]['optional']['col'][1];
		
		if ( '' === $color )
			return $default_color;
		
		if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) )
			return $color;
		
		return $default_color;
	}
	
	public function array_tf()
	{
		$args = array(
			'0',
			'1',
		);
		return $args;
	}
	
	public function array_sizes()
	{
		global $_wp_additional_image_sizes;
		$sizes              = array();
		$intermediate_sizes = get_intermediate_image_sizes();
		
		foreach ( $intermediate_sizes as $_size ) :
		
			if ( in_array( $_size,
				array(
					'thumbnail',
					'medium',
					'large' 
				)
			) ) :
				$sizes[ $_size ]['width']  = get_option( $_size . '_size_w' );
				$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
				$sizes[ $_size ]['crop']   = (bool) get_option( $_size . '_crop' );
				
			endif;
			
			if ( isset( $_wp_additional_image_sizes[ $_size ] ) )
				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'] 
				);
			
		endforeach;
		
		return $sizes;
	}
	
	public function array_parameter( $parameter )
	{
		$id = absint( $this->extrp_multidimensional_search( 
			$this->big_data(), 
			array( 
				'parameter' => $parameter 
			) 
		) );
		
		$args = $this->big_data()[ $id ]['optional'];
		
		return $args;
	}
	
	public function relevanssi( $relevanssi )
	{
		if ( true == $relevanssi && 1 == get_option( 'extrp_with_relevanssi' ) && function_exists( 'relevanssi_do_query' ) )
			return true;
		return false;
	}
	
	public function customsize( $args )
	{
		$array = array();

		if ( empty( $args ) )
			return false;
		
		for ( $i = 0 ; $i < count( $args ) ; $i++ ) :
			$array[ $i ] = array(
				'size'   => $this->customsize_key( $args[ $i ]['size'] ),
				'width'  => intval( $args[ $i ]['width'] ),
				'height' => intval( $args[ $i ]['height'] ),
				'crop'   => $this->customsize_crop( $args[ $i ]['crop'] ) 
			);
		endfor;
		
		return $array;
	}
	
	public function post_date( $args )
	{
		$array = array_keys( $this->array_post_date() );
		$result = array();
		
		if ( ! is_array( $args ) )
			$args = explode( ',', $args );
		
		foreach ( $args as $k ) :
			$k = sanitize_key( $k );
			if ( in_array( $k, $array ) )
				$result[] = $k;
		endforeach;
		
		return $result;
	}
	
	public function array_post_date()
	{
		$array = array(
			'show_date' => __( 'Show post date','extrp' ),
			'time_diff' => __( 'Show time difference','extrp' ),
		);
		return $array;
	}

	public function data_thumb( $args )
	{
		global $extrp_settings;

		if ( ! is_array( $args ) ) :
			$id = absint( $this->extrp_multidimensional_search( $this->big_data(), array( 'parameter' => 'noimage' ) ) );
			return $this->big_data()[ $id ]['normal'];
		endif;

		$noimage = $extrp_settings['noimage'];
			
		if ( ! isset( $noimage['default'] ) ||  $args === $noimage ||  false == wp_get_attachment_image_src( $noimage['attachment_id'], 'full', false ) ) :
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
				return true;
			return $args;
		endif;
		
		$default = array(
			'post_id'   => null,
			'img_class' => ''
		);
		
		$args       = wp_parse_args( $args, $default );

		$thumbnail  = extrp_thumbnail( $args );

		$data_thumb = $thumbnail->process_thumb();
		
		if ( ! $data_thumb )
			return false;
		
		return $data_thumb;
	}
	
	public function noimage_default()
	{
		global $extrp_settings;
		
		$url = esc_url_raw( $extrp_settings['noimage']['default'] );
		
		$noimage = ( ! empty( $url ) && isset( $extrp_settings['noimage']['attachment_id'] ) && false != wp_get_attachment_image_src( $extrp_settings['noimage']['attachment_id'], 'full', false ) ) ? $url : ''; 
		return $noimage;
	}

	public function post_types( $array )
	{
		$result = array();

		if ( ! is_array( $array ) )
			$array = array( sanitize_key( $array ) );
		
		foreach ( $this->array_post_types() as $post_type ) :
			$post_type    = sanitize_key( $post_type );
			if ( in_array( $post_type, $array ) )
				$result[] = $post_type;
		endforeach;
		return $result;
	}

	public function extrp_multidimensional_search( $parents, $searched )
	{
	  if ( empty( $searched ) || empty( $parents ) )
		return false; 

	  foreach ( $parents as $key => $value ) :
		$exists = true; 
		
		foreach ( $searched as $skey => $svalue ) :
		  $exists = ( $exists && isset( $parents[ $key ][ $skey ] ) && $parents[ $key ][ $skey ] == $svalue ); 
		endforeach; 
		
		if ( $exists )
			return $key;
		
	  endforeach;
	  
	  return false; 
	}

	public function array_post_types()
	{
		$args       = array(
			'public' => true,
			'_builtin' => false 
		);
		$output     = 'names';
		$operator   = 'or';
		$post_types = get_post_types( $args, $output, $operator );
		if ( is_array( $post_types ) )
			return $post_types;
		return false;
	}

	public function big_data()
	{
		$data = array();
		
		$data[1] = array(
			'id'          => 1,
			'parameter'   => 'active',
			'normal'      => (bool) 1,
			'optional'    => $this->array_tf(),
			'subtitle'    => __( 'Show Related Posts', 'extrp' ),
			'description' => __( 'Show under post content automatically', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp'),
			'lang'        => 'boolean'
		);
		
		$data[2] = array(
			'id'          => 2,
			'parameter'   => 'post_type',
			'normal'      => array( 'post' ),
			'optional'    => $this->array_post_types(),
			'subtitle'    => __( 'Posts Types', 'extrp' ),
			'description' => __( 'This list is your existing post types.', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'array'
		);
		
		$data[3] = array(
			'id'          => 3,
			'parameter'   => 'relatedby',
			'normal'      => 'title',
			'optional'    => array(
				'title',
				'cat',
				'tag',
				'splittitle',
				'random' 
			),
			'subtitle'    => __( 'Related Posts By', 'extrp' ),
			'description' => __( 'Define your related posts by.', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[4] = array(
			'id'          => 4,
			'parameter'   => 'single',
			'normal'      => (bool) 1,
			'optional'    => $this->array_tf(),
			'subtitle'    => __( 'Show on Single Post Only', 'extrp' ),
			'description' => __( 'Unset for sitewide page', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'boolean'
		);
		
		$data[5] = array(
			'id'          => 5,
			'parameter'   => 'posts',
			'normal'      => 5,
			'optional'    => 3,
			'subtitle'    => __( 'Posts Count', 'extrp' ),
			'description' => __( 'Post Count per post', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'integer'
		);
		
		$data[6] = array(
			'id'          => 6,
			'parameter'   => 'post_date',
			'normal'      => '',
			'optional'    => array( 
				'show_date' => __( 'Post Date','extrp' ),
				'time_diff' => __( 'Time Difference','extrp' ) 
			),
			'subtitle'    => __( 'Show Post Date', 'extrp' ),
			'description' => __( 'Show both of post date and/or time difference', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'array'
		);
		
		$data[7] = array(
			'id'          => 7,
			'parameter'   => 'heading',
			'normal'      => 'h3',
			'optional'    => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'div', 'p' ),
			'subtitle'    => __( 'HTML Heading Subtitle', 'extrp' ),
			'description' => __( 'HTML Headings tags of subtitle. &lt;h1&gt;,&lt;h2&gt;,&lt;h3&gt;,...', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[8] = array(
			'id'          => 8,
			'parameter'   => 'postheading',
			'normal'      => 'h4',
			'optional'    => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'div', 'p' ),
			'subtitle'    => __( 'HTML Heading Post Titles', 'extrp' ),
			'description' => __( 'HTML Headings tags of post titles. &lt;h1&gt;,&lt;h2&gt;,&lt;h3&gt;,...', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[9] = array(
			'id'          => 9,
			'parameter'   => 'subtitle',
			'normal'      => __( 'Related Posts', 'extrp' ),
			'optional'    => __( 'Related News', 'extrp' ),
			'subtitle'    => __( 'Subtitle Related Posts', 'extrp' ),
			'description' => wp_kses( __( 'Changed the text, or leave as an empty <kbd>subtitle=""</kbd> to hide.', 'extrp' ), array( 'kbd' => array() ) ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[10] = array(
			'id'          => 10,
			'parameter'   => 'randomposts',
			'normal'      => (bool) 1,
			'optional'    => $this->array_tf(),
			'subtitle'    => __( 'Enable Random Posts', 'extrp' ),
			'description' => __( 'Unset to disable random posts', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'boolean'
		);
		
		$data[11] = array(
			'id'          => 11,
			'parameter'   => 'titlerandom',
			'normal'      => __( 'Random Posts', 'extrp' ),
			'optional'    => __( 'Random News', 'extrp' ),
			'subtitle'    => __( 'Subtitle Random Posts', 'extrp' ),
			'description' => wp_kses( __( 'Changed the text, or leave as an empty <kbd>titlerandom=""</kbd> to hide.', 'extrp' ), array( 'kbd' => array() ) ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[12] = array(
			'id'          => 12,
			'parameter'   => 'post_title',
			'normal'      => (bool) 1,
			'optional'    => $this->array_tf(),
			'subtitle'    => __( 'Show Post Title', 'extrp' ),
			'description' => __( 'You can hide the posts title if thumbnail is set.', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'boolean'
		);
		
		$data[13] = array(
			'id'          => 13,
			'parameter'   => 'desc',
			'normal'      => (bool) 1,
			'optional'    => $this->array_tf(),
			'subtitle'    => __( 'Show Post Description', 'extrp' ),
			'description' => __( 'Unset to hide the posts description.', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'boolean'
		);
		
		$data[14] = array(
			'id'          => 14,
			'parameter'   => 'stopwords',
			'normal'      => 'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
			'optional'    => '',
			'subtitle'    => __( 'Stopwords', 'extrp' ),
			'description' => __( 'Separate them by comma.', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[15] = array(
			'id'          => 15,
			'parameter'   => 'display',
			'normal'      => 'list',
			'optional'    => array( 'list', 'list_group', 'inline', 'left', 'right', 'left_wrap', 'right_wrap' ),
			'subtitle'    => __( 'Style of list', 'extrp' ),
			'description' => __( 'Style of list.', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[16] = array(
			'id'          => 16,
			'parameter'   => 'post__in',
			'normal'      => '',
			'optional'    => '1,2,3',
			'subtitle'    => __( 'Include Post IDs', 'extrp' ),
			'description' => __( 'Type by comma. Only the post IDs with post exists will be listed here.', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'array'
		);
		
		$data[17] = array(
			'id'          => 17,
			'parameter'   => 'post__not_in',
			'normal'      => '',
			'optional'    => '4,5,6',
			'subtitle'    => __( 'Exclude Post IDs', 'extrp' ),
			'description' => __( 'Type by comma. This post IDs will be excluded of your related posts', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'array'
		);
		
		$data[18] = array(
			'id'          => 18,
			'parameter'   => 'css',
			'normal'      => (bool) 1,
			'optional'    => $this->array_tf(),
			'subtitle'    => __( 'Use CSS on Plugin', 'extrp' ),
			'description' => __( 'Unset to disable. Your shortcode style will be affected too.', 'extrp' ),
			'group'       => 'general',
			'subgroup'    => __( 'general settings', 'extrp' ),
			'lang'        => 'boolean'
		);
		
		$data[19] = array(
			'id'          => 19,
			'parameter'   => 'thumb',
			'normal'      => (bool) 1,
			'optional'    => $this->array_tf(),
			'subtitle'    => __( 'Enable Thumbnail', 'extrp' ),
			'description' => __( 'Unset to disabled.', 'extrp' ),
			'group'       => 'thumbnail',
			'subgroup'    => __( 'thumbnail settings', 'extrp' ),
			'lang'        => 'boolean'
		);
		
		$data[20] = array(
			'id'          => 20,
			'parameter'   => 'ext_thumb',
			'normal'      => (bool) 0,
			'optional'    => $this->array_tf(),
			'subtitle'    => __( 'Allow External URL', 'extrp' ),
			'description' => __( 'Use it if no local image attached on your post but have external image. With activate this feature, it will increase page load time. Enable cache to get better performance load.', 'extrp' ),
			'group'       => 'thumbnail',
			'subgroup'    => __( 'thumbnail settings', 'extrp' ),
			'lang'        => 'boolean'
		);
		
		$data[21] = array(
			'id'          => 21,
			'parameter'   => 'image_size',
			'normal'      => 'thumbnail',
			'optional'    => $this->array_sizes(),
			'subtitle'    => __( 'Size', 'extrp' ),
			'description' => __( 'The sizes are your current settings in "Media Settings" and additional custom size.', 'extrp' ),
			'group'       => 'thumbnail',
			'subgroup'    => __( 'thumbnail settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[22] = array(
			'id'          => 22,
			'parameter'   => 'customsize',
			'normal'      => array(
				0 => array(
					'size'   => '',
					'width'  => '',
					'height' => '',
					'crop'   => ''
				)
			),
			'optional'    => array(
				0 => array(
					'size'   => '',
					'width'  => '',
					'height' => '',
					'crop'   => ''
				)
			),
			'subtitle'    => __( 'Add Custom Size', 'extrp' ),
			'description' => array(
				'main_desc' => __( 'Add Thumbnail Custom Size', 'extrp' ),
				'size'      => __( 'Key (allowed a to z, hypen [ - ] or underscore [ _ ])', 'extrp' ),
				'width'     => __( 'Width (px)', 'extrp' ),
				'height'    => __( 'Height (px)', 'extrp' ),
				'crop'      => __( 'Set the post thumbnail by cropping the image','extrp' )
			),
			'group'       => 'thumbnail',
			'subgroup'    => __( 'thumbnail settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[23] = array(
			'id'          => 23,
			'parameter'   => 'shape',
			'normal'      => 'noshape',
			'optional'    => array(
				'noshape',
				'rounded',
				'circle',
				'thumbnail' 
			),
			'subtitle'    => __( 'Shape', 'extrp' ),
			'description' => __( 'Thumbnail shape style.', 'extrp' ),
			'group'       => 'thumbnail',
			'subgroup'    => __( 'thumbnail settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[24] = array(
			'id'          => 24,
			'parameter'   => 'crop',
			'normal'      => (bool) 1,
			'optional'    => $this->array_tf(),
			'subtitle'    => __( 'Crop thumbnail', 'extrp' ),
			'description' => __( 'Set the post thumbnail by cropping the image (either from the sides, or from the top and bottom)', 'extrp' ),
			'group'       => 'thumbnail',
			'subgroup'    => __( 'thumbnail settings', 'extrp' ),
			'lang'        => 'boolean'
		);
		
		$data[25] = array(
			'id'          => 25,
			'parameter'   => 'noimage',
			'normal'      => array(
				'attachment_id' => '',
				'default'  => esc_url_raw( $this->noimage_default() ),
				'full_src' => esc_url_raw( $this->noimage_default() ),
				'size'     => 'thumbnail',
				'src'      => esc_url_raw( $this->noimage_default() ),
				'width'    => '',
				'height'   => '',
				'crop'     => (bool) 1
			),
			'optional'    => array(
				'attachment_id' => '',
				'default'       => esc_url_raw( $this->noimage_default() ),
				'full_src'      => esc_url_raw( $this->noimage_default() ),
				'size'          => 'thumbnail',
				'src'           => '',
				'width'         => '',
				'height'        => '',
				'crop'          => (bool) 1
			),
			'subtitle'    => __( 'Default Thumbnail', 'extrp' ),
			'description' => array(
				'main_desc'      => __( 'If there is no post thumbnail specified, then it will display this default image.', 'extrp' ),
				'btn_upload_txt' => __( 'Set New Image', 'extrp' ),
				'btn_reset_txt'  =>__( 'Reset', 'extrp' )
			),
			'group'       => 'thumbnail',
			'subgroup'    => __( 'thumbnail settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[26] = array(
			'id'          => 26,
			'parameter'   => 'post_excerpt',
			'normal'      => (bool) 1,
			'optional'    => array(
				'default' => 0, 
				'snippet' => (bool) 1 
			),
			'subtitle'    => __( 'Excerpt Type', 'extrp' ),
			'description' => __( 'Snippet - like search engine, the description based on the query.', 'extrp' ),
			'group'       => 'additional',
			'subgroup'    => __( 'additional settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[27] = array(
			'id'          => 27,
			'parameter'   => 'highlight',
			'normal'      => 'no|no',
			'optional'    => array(
				'no'     => array(
					'No Highlighting',
					'no' 
				),
				'mark'   => array(
					'&lt;mark&gt;',
					'mark' 
				),
				'em'     => array(
					'&lt;em&gt;',
					'em' 
				),
				'strong' => array(
					'&lt;strong&gt;',
					'strong' 
				),
				'col'    => array(
					'Text Color',
					'#333' 
				),
				'bgcol'  => array(
					'Background Color',
					'#FFFF00' 
				),
				'css'    => array(
					'CSS Style',
					'text-decoration: underline; color: #ff0000' 
				),
				'class'  => array(
					'CSS Class',
					'extrp-query-term' 
				) 
			),
			'subtitle'    => __( 'Highlight Query', 'extrp' ),
			'description' => __( 'Highlighting snippets.', 'extrp' ),
			'group'       => 'additional',
			'subgroup'    => __( 'additional settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[28] = array(
			'id'          => 28,
			'parameter'   => 'maxchars',
			'normal'      => 150,
			'optional'    => 256,
			'subtitle'    => __( 'Excerpt Length', 'extrp' ),
			'description' => __( 'Maximum allowed characters length of post excerpt.', 'extrp' ),
			'group'       => 'additional',
			'subgroup'    => __( 'additional settings', 'extrp' ),
			'lang'        => 'integer'
		);
		
		$data[29] = array(
			'id'          => 29,
			'parameter'   => 'relevanssi',
			'normal'      => 0,
			'optional'    => $this->array_tf(),
			'subtitle'    => __( 'Enable Relevanssi Algorithm', 'extrp' ),
			'description' => array(
				sprintf( wp_kses( 
					__( 'Require Relevanssi plugin installed. <a href="%1$s" class="thickbox" aria-label="%2$s" title="%2$s"><i class="dashicons dashicons-external"></i></a>', 'extrp' ), array(
						'a' => array(
							'href'       => array(),
							'class'      => array(),
							'aria-label' => array(),
							'title'      => array()
						),
						'i' => array(
							'class' => array()
						)
					)
				),
				self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=relevanssi&amp;TB_iframe=true&amp;width=600&amp;height=550' ),
				__( 'More information about Relevanssi plugin', 'extrp' ) ), 
				__( 'With activate this feature, highlight and length of excerpt settings will affected to your Relevanssi plugin settings. If both of shortcode and related posts under post showing in one single page, I recommend to enable cache setting. As a note, the excerpt of Relevanssi plugin not running stable on outside search pages, don&#39t be worried, just lets this plugin handle it.', 'extrp' )
			),
			'group'       => 'additional',
			'subgroup'    => __( 'additional settings', 'extrp' ),
			'lang'        => 'boolean'
		);
		
		$data[30] = array(
			'id'          => 30,
			'parameter'   => 'cache',
			'normal'      => 0,
			'optional'    => $this->array_tf(),
			'subtitle'    => __( 'Enable Cache', 'extrp' ),
			'description' => array(
				__( 'Cache to database as transient.', 'extrp' ), 
				__( 'Your shortcode will be cached too. Transients only get deleted when a request is made after expire time and create the new one. So, until someone visits your page and calls up the Transient, it will stay in the DB. In short: It&#39s not a real persistent cache and not equal to stuff running on cron jobs.', 'extrp' )
			),
			'group'       => 'additional',
			'subgroup'    => __( 'additional settings', 'extrp' ),
			'lang'        => 'boolean'
		);
		
		$data[31] = array(
			'id'          => 31,
			'parameter'   => 'expire',
			'normal'      => 3600,
			'optional'    => 86400,
			'subtitle'    => __( 'Cache Expire Time', 'extrp' ),
			'description' => __( 'Minimum 3600 seconds', 'extrp' ),
			'group'       => 'additional',
			'subgroup'    => __( 'additional settings', 'extrp' ),
			'lang'        => 'integer'
		);
		
		$data[32] = array(
			'id'          => 32,
			'parameter'   => 'schedule',
			'normal'      => 3600,
			'optional'    => 86400,
			'subtitle'    => __( 'Schedule Delete All Caches', 'extrp' ),
			'description' => array(
				__( 'Minimum 3600 seconds', 'extrp' ),
				__( 'This is wp schedule event and run by wp cron. With cache enabled and the scheduled time has passed, the action will trigger when someone visits your WordPress site.','extrp' )
			),
			'group'       => 'additional',
			'subgroup'    => __( 'additional settings', 'extrp' ),
			'lang'        => 'integer'
		);
		
		$data[33] = array(
			'id'          => 33,
			'parameter'   => 'sidebar',
			'normal'      => '',
			'optional'    => '',
			'subtitle'    => '',
			'description' => wp_kses( __( '<p><i class="%1$s">Remember, if you ever need a helping hand, you will find one at the end of each of your arms. As you grow older, you will discover that you have two hands, one for helping yourself and the other for helping others.</i></p><p class="%2$s"> &#126; Sam Levenson</p><p class="%2$s">Here my support contact &amp; PayPal email <i class="%3$s"></i> <a target="_blank" href="%4$s" title="%5$s">%6$s</a></p>', 'extrp' ), array(
				'p' => array(
					'class' => array()
				),
				'i' => array(
					'class' => array()
				),
				'a' => array(
					'target' => array(),
					'href'   => array(),
					'title'  => array()
				) 
			) ),
			'group'       => 'support',
			'subgroup'    => __( 'Help Support, Credit &amp; Donate', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[34] = array(
			'id'          => 34,
			'parameter'   => 'sidebar',
			'normal'      => '',
			'optional'    => '',
			'subtitle'    => '',
			'description' => __( 'EXTRP features.', 'extrp' ),
			'group'       => 'features',
			'subgroup'    => __( 'Features', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[35] = array(
			'id'          => 35,
			'parameter'   => 'sidebar',
			'normal'      => '',
			'optional'    => '',
			'subtitle'    => '',
			'description' => '',
			'group'       => 'save',
			'subgroup'    => __( 'save your settings', 'extrp' ),
			'lang'        => 'string'
		);
		
		$data[36] = array(
			'id'          => 36,
			'parameter'   => 'tool',
			'normal'      => '',
			'optional'    => '',
			'subtitle'    => '',
			'description' => '',
			'group'       => 'tool',
			'subgroup'    => 'shortcode generator',
			'lang'        => 'string'
		);
		
		return $data;
	}
	
	public function sanitize( $c = '' )
	{
		$default = $this->array_default_setting();
		$a       = array_keys( $default );
		
		if ( empty( $c ) )
			$c = $default;
		
		$key = array_keys( $c );
		$b   = wp_parse_args( $c, $default );
		
		$args = array(
			$a[0]  => wp_validate_boolean( $b['active'] ),
			$a[1]  => $this->post_types( $b['post_type'] ),
			$a[2]  => $this->relatedby( $b['relatedby'] ),
			$a[3]  => wp_validate_boolean( $b['single'] ),
			$a[4]  => absint( $b['posts'] ),
			$a[5]  => $this->post_date( $b['post_date'] ),
			$a[6]  => $this->heading( $b['heading'] ),
			$a[7]  => $this->heading( $b['postheading'] ),
			$a[8]  => sanitize_text_field( $b['subtitle'] ),
			$a[9]  => wp_validate_boolean( $b['randomposts'] ),
			$a[10] => sanitize_text_field( $b['titlerandom'] ),
			$a[11] => wp_validate_boolean( $b['post_title'] ),
			$a[12] => wp_validate_boolean( $b['desc'] ),
			$a[13] => $this->data_textarea( $b['stopwords'] ),
			$a[14] => $this->display( $b['display'] ),
			$a[15] => $this->post_ids( $b['post__in'] ),
			$a[16] => $this->post_ids( $b['post__not_in'] ),
			$a[17] => wp_validate_boolean( $b['css'] ),
			$a[18] => wp_validate_boolean( $b['thumb'] ),
			$a[19] => wp_validate_boolean( $b['ext_thumb'] ),
			$a[20] => $this->size( $b['image_size'] ),
			$a[21] => $this->customsize( $b['customsize'] ),
			$a[22] => $this->shape( $b['shape'] ),
			$a[23] => wp_validate_boolean( $b['crop'] ),
			$a[24] => $this->data_thumb( $b['noimage'] ),
			$a[25] => wp_validate_boolean( $b['post_excerpt'] ),
			$a[26] => $this->highlight( $b['highlight'] ),
			$a[27] => absint( $b['maxchars'] ),
			$a[28] => $this->relevanssi( $b['relevanssi'] ),
			$a[29] => wp_validate_boolean( $b['cache'] ),
			$a[30] => absint( $b['expire'] ),
			$a[31] => absint( $b['schedule'] )
		);
		
		foreach ( $args as $k => $v ) :
			if ( ! in_array( $k, $key ) )
				unset( $args[ $k ] );
		endforeach;
		return $args;
	}
	
	public function array_default_setting()
	{
		$_data = $this->big_data();
		$data  = array_splice( $_data, 0, 32 );
		array_unshift( $data, '' );
		unset( $data[0] );
		
		$default = array();
		for( $i = 1 ; $i <= count( $data ) ; $i++ ) :
			$default[ $data[ $i ]['parameter'] ] = $data[ $i ]['normal'];
		endfor;
		return $default;
	}
}