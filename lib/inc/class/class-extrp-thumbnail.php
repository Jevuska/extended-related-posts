<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

class EXTRP_Thumbnail
{
	private static $instance;
	public $post_id;
	public $size;
	public $crop;
	public $img_class;
	public $img_src;
	public $attr = '';
	public $attr_link = '';
	public $icon = true;
	public $attachment_id = 0;
	public $data_thumb;
	public $direct_src_external = '';
	public $text = false;
	public $permalink = true;
	public $attach_external_img = false;
	public $local_img_filename = '';
	
	public function __construct( $args )
	{
		if ( ! is_array( $args ) )
			return;

		$this->post_id             = ( null === $args['post_id'] ) ? null : (int) $args['post_id'];
		$this->size                = ( isset( $args['size'] ) ) ? sanitize_key( $args['size'] ) : false;
		$this->crop                = ( isset( $args['crop'] ) ) ? wp_validate_boolean( $args['crop'] ) : (bool) 0;
		$this->img_class           = ( isset( $args['img_class'] ) ) ? sanitize_html_class( $args['img_class'] ) : '';
		$this->img_default         = ( isset( $args['default'] ) ) ? esc_url_raw( $args['default'] ) : '';
		$this->img_src             = ( isset( $args['src'] ) && '' != $args['src'] ) ? esc_url_raw( $args['src'] ) : '';
		$this->img_src             = ( isset( $args['full_src'] ) ) ? esc_url_raw( $args['full_src'] ) : $this->img_src;
		$this->direct_src_external = ( '' != $this->img_src ) ? $this->img_src : '';
		
		$this->post = get_post( $this->post_id );
		
		$this->attachment_id = intval( get_post_thumbnail_id( $this->post_id ) );
			
		if ( ! is_admin() )
		{
			$image = $this->thumbnail_meta_data();
			
			if ( 0 == $this->attachment_id )
			{
				$this->attachment_id   = $image['attachment_id'];
				if ( ! is_admin() )
					$this->img_src     = $image['src'];
				
				if ( -1 == $this->attachment_id )
					$this->img_src_ext = $image['src'];
			}
		}
		
		$this->attachment          = get_post( $this->attachment_id );

		add_filter( 'icon_dir', array( $this, 'filter_icon_dir' ) );
	}
	
	public static function getInstance()
	{
		if ( ! self::$instance )
			self::$instance = new self();
		return self::$instance;
	}

	public function filter_icon_dir()
	{
		$icon_dir = ABSPATH . WPINC . '/images/crystal';
		return $icon_dir;
	}
	
	public function get_attachment_link()
	{
		
		if ( isset( $this->image_exist()['noexists'] ) || empty( $this->attachment ) || ( 'attachment' != $this->attachment->post_type ) || ! wp_get_attachment_url( $this->attachment->ID ) )
		{
			if ( ! empty( $this->img_src_ext ) )
				return $this->filter_attachment_link();
			return wp_get_attachment_image( $this->attachment_id, $this->size, $this->icon, $this->attr );
		}
		
		add_filter( 'wp_get_attachment_link', array(
			$this,
			'filter_attachment_link'
		), 20, 6 );
		
		$this->attach_link = wp_get_attachment_link( $this->attachment_id, $this->size, $this->permalink, $this->icon, $this->text, $this->attr );
		
		return $this->attach_link;
	}
	
	public function filter_attachment_link()
	{
		global $_wp_additional_image_sizes, $extrp_settings;
		if ( is_admin() )
			return;

		if ( $this->permalink )
		{
			$url = esc_url( get_permalink( $this->post_id ) );

			$default_attr = array(
				'href'  => $url,
				'title' => the_title_attribute( 'echo=0&post=' . $this->post_id ),
				'class' => '',
				'style' => ''
			);
		}
		
		if ( $this->text ) :
			$this->link_text = $this->text;
			
		elseif ( $this->size && 'none' != $this->size ) :
			$this->link_text = $this->get_attachment_image()['link_text'];
			$default_attr['class'] = 'link-' . $this->attachment_id;
			if ( -1 == $this->attachment_id )
			{
				$style = '';

				if ( @GetImageSize( $this->img_src ) )
				{
					list( $width, $height ) = @GetImageSize( $this->img_src );
				
					list( $_w, $_h ) = image_constrain_size_for_editor( $width, $height, $this->size );

					$style = 'width:' . $_w . 'px;height:' . $_h . 'px;display:block;';
				
					if ( $this->crop )
					{
						if ( isset( $_wp_additional_image_sizes[ $this->size ] ) )
						{
							$this->img_width  = $_wp_additional_image_sizes[ $this->size ]['width'];
							$this->img_height = $_wp_additional_image_sizes[ $this->size ]['height'];
						}
						else
						{
							$this->img_width  = get_option( $this->size . '_size_w' );
							$this->img_height = get_option( $this->size . '_size_h' );
						}
						
						$style = 'display:block;width:' . intval( $this->img_width ) . 'px;height:' . intval( $this->img_height ) . 'px';
					}
				}
				$default_attr['class'] = 'link-' . $this->attachment_id;
				$default_attr['style'] = $style;
			}
			else
			{
				unset( $default_attr['style'] );
			}
		else :
			$this->link_text = '';
		endif;
		
		if ( trim( $this->link_text ) == '' )
			$this->link_text = $this->attachment->post_title;

		$this->attr_link = wp_parse_args( $default_attr, $this->attr_link  );
		
		$this->attr_link = array_map( 'esc_attr', $this->attr_link );
		$link = rtrim( "<a " );
		foreach ( $this->attr_link as $name => $value ) {
			$link .= " $name=" . '"' . $value . '"';
		}
		$link .= '>';
		$link .= $this->link_text . '</a>';
		
		return $link;
	}
	
	protected function get_attachment_image()
	{
		if ( is_admin() )
			return;
		
		$thumbnail           = $this->thumbnail_meta_data();
		$this->post_id       = $thumbnail['post_id'];
		$this->img_src       = $thumbnail['src'];
		$this->attachment_id = $thumbnail['attachment_id'];
		$this->img_width     = $thumbnail['width'];
		$this->img_height    = $thumbnail['height'];
		$this->hwstring      = $thumbnail['hwstring'];
		$this->size          = $thumbnail['size'];
		
		$attachment_html     = $this->img_html();
		
		$arg = array(
			'link_text'     => $attachment_html['html'],
			'src'           => $this->img_src,
			'attachment_id' => $this->attachment_id,
			'post_id'       => $this->post_id
		);
		return $arg;
	}
	
	protected function img_html()
	{
		global $_wp_additional_image_sizes, $extrp_settings;
		
		$this->attachment = get_post( $this->attachment_id );
		
		$this->img_class  = $this->img_class . ' size-' . $this->size  . ' thumbnail ' . 'wp-image-' . $this->attachment_id;
		
		if ( ! empty ( $this->img_src_ext ) )
			$this->img_src = $this->img_src_ext;

		if ( -1 == $this->attachment_id ) :
			$this->img_width = $extrp_settings['noimage']['width'];
			$this->img_height = $extrp_settings['noimage']['height'];
		endif;

		if ( ! empty( $this->img_src ) && -1 == $this->attachment_id ) :
			$style = '';
			if ( @GetImageSize( $this->img_src ) )
			{
				$style = 'width:100%;';
				
				if ( $this->crop )
				{
					list( $_w, $_h ) = @GetImageSize( $this->img_src );
					if ( $_w > $_h )
						$style = 'height:100%;';
				}
			} 
			else
			{
				$style = '';
				$this->img_src = $extrp_settings['noimage']['src'];
			}

			$default_attr = array(
				'src'    => $this->img_src,
				'style'  => $style,
				'class'  => esc_attr( $this->img_class ),
				'alt'    => ''
			);
		elseif ( ! empty( $this->img_src ) && 0 < $this->attachment_id ) :
			$default_attr = array(
				'src'    => $this->img_src,
				'width'  => intval( $this->img_width ),
				'height' => intval( $this->img_height ),
				'class'  => esc_attr( $this->img_class ),
				'alt'    => ''
			);
		else :
			$default_attr = array(
				'src'    => $this->img_src,
				'class'  => esc_attr( $this->img_class ),
				'alt'    => ''
			);
		endif;
		
		if ( 0 < $this->attachment_id )
		{
			if ( empty( $default_attr['alt'] ) )
				$default_attr['alt'] = trim( strip_tags( get_post_meta( $this->attachment_id, '_wp_attachment_image_alt', true ) ) );
			
			if ( empty( $default_attr['alt'] ) )
				$default_attr['alt'] = trim( strip_tags( esc_attr__( $this->attachment->post_excerpt, 'extrp' ) ) );
			
			if ( empty( $default_attr['alt'] ) )
				$default_attr['alt'] = trim( strip_tags( esc_attr__( $this->attachment->post_title, 'extrp' ) ) );
		} else {
			if ( '' != $this->img_src ) {
				$default_attr['alt'] = trim( strip_tags( esc_attr__( get_the_title( $this->post_id ) ) ) );
			} else {
				$text_no_alt         = 'No Image Available';
				$default_attr['alt'] = esc_attr__( $text_no_alt, 'extrp' );
			}
		}
		
		$this->attr = wp_parse_args( $default_attr, $this->attr  );

		$html = rtrim( "<img " );
		foreach ( $this->attr as $name => $value )
		{
			$html .= " $name=" . '"' . $value . '"';
		}
		$html .= ' />';

		$this->img_src_html = array(
			'html'    => $html,
			'attr'    => $this->attr,
			'post_id' => $this->post_id
		);
		
		return $this->img_src_html;
	}
	
	protected function thumbnail_meta_data()
	{
		global $extrp_settings;
		
		if ( is_admin() )
			return;
		
		if ( 0 == $this->attachment_id )
		{
			
			if ( false != $this->get_first_image_data() )
			{
				$attachments          = $this->get_first_image_data();
				$this->attachment_id  = $attachments['attachment_id'];
			}
		}

		if ( 0 == $this->attachment_id )
		{
			
			$first_img_data     = $this->catch_first_image();
			
			if ( isset( $first_img_data['local'] ) && '' != $first_img_data['local']['src'])
			{
				$this->img_src            = $first_img_data['local']['src'];
				$this->local_img_filename = $first_img_data['local']['filename'];

				if ( false !=  $this->get_attachment_id() )
					$this->attachment_id  = $this->get_attachment_id();
				else
					$this->attachment_id = 0;
			}

			if ( isset( $first_img_data['external'] ) )
			{
				$this->img_src = $first_img_data['external'];
				
				if ( $this->attach_external_img ) :
					if ( false != $this->attachment_external() ) :
						$data_img_external    = $this->attachment_external();
						$this->img_src        = $data_img_external['src'];
						$this->attachment_id  = $data_img_external['attachment_id'];
					endif;
				else :
					$external_img = array(
						'attachment_id'  => -1,
						'src'            => $this->img_src,
						'post_id'        => $this->post_id
					);
					return $external_img;
				endif;
			}
		}

		$data = wp_get_attachment_image_src( $this->attachment_id, 'full', $this->icon );

		$this->img_src = $data[0];
		
		$thumb = [];
		
		if ( 0 != $this->attachment_id && '' != $this->img_src )
		{
			$thumb = $this->process_thumb();
		}

		if ( 0 == $this->attachment_id )
		{
			$this->img_src = $extrp_settings['noimage']['full_src'];
			
			if ( false !=  $this->get_attachment_id( $this->img_src ) )
					$this->attachment_id  = $this->get_attachment_id( $this->img_src );
				else
					$this->attachment_id = 0;
		}
		
		$thumb_arr = array(
			'attachment_id'  => $this->attachment_id,
			'src'            => $this->img_src,
			'post_id'        => $this->post_id,
		);

		$args = wp_parse_args( $thumb, $thumb_arr );

		return $args;
	}
	
	public function get_first_image_data()
	{
		$first_image = '';
		$args        = array(
			'numberposts'    => 1,
			'order'          => 'ASC',
			'post_mime_type' => 'image',
			'post_parent'    => $this->post_id,
			'post_status'    => null,
			'post_type'      => 'attachment' 
		);
		$attachments    = get_children( $args );

		if ( ! array_filter( $attachments ) )
			return false;
		
		$id = '';
		$data = array();
		
		foreach ( $attachments as $attachment )
		{
			$id  .= $attachment->ID;
			$data = wp_get_attachment_image_src( $id, $this->size, $this->icon );
		}

		if ( ! array_filter( $data ) )
			return false;

		$data_attachment = array(
			'attachment_id' => $id,
			'src'           => $data[0],
			'hwstring'      => image_hwstring( $data[1], $data[2] )
		);
		
		return $data_attachment;
	}
	
	public function get_attachment_id( $src = '' )
	{
		global $wpdb;
		$this->attachment_id = false;

		if ( '' != $src ) {
			$this->img_src = $src;

			$exist = $this->image_exist();
			if ( isset( $exist['exists'] ) && '' != $exist['exists'] )
			$this->local_img_filename = $exist['exists']['filename'];
			if ( empty( $this->local_img_filename ) )
				return;
		}
		
		if ( '' == $this->local_img_filename )
			return;
		
		$this->attachment_id  = wp_cache_get( 'attachment_id_' . $this->post_id, 'getattachid' );
		
		if ( false === $this->attachment_id ) :
			$sql = "
				   SELECT wposts.ID 
				   FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
				   WHERE wposts.ID = wpostmeta.post_id
						AND wpostmeta.meta_key = '_wp_attached_file'
						AND wpostmeta.meta_value = '%s'
						AND wposts.post_type = 'attachment'
				   ";
			$sql = $wpdb->prepare( $sql, $this->local_img_filename );
			$this->attachment_id = $wpdb->get_var( $sql );
			wp_cache_set( 'attachment_id_' . $this->post_id, $this->attachment_id, 'getattachid', 300 );
		endif;
		
		if ( empty ( $this->attachment_id ) )
			return false;
		return absint( $this->attachment_id );
	}
	
	public function catch_first_image()
	{
		$first_img_url = '';
		
		if ( null == $this->post_id )
			return false;

		preg_match_all('!http(s)?://[a-z0-9\-\.\/]+\.(?:jpe?g|png|gif)!Ui' , $this->post->post_content , $matches);
		
		$result = array();
		foreach ( $matches[0] as $img_url ) :
		
			$this->img_src = $img_url;
			$img_src = $this->image_exist();
			
			if ( false == $img_src )
				continue;
			
			if ( isset( $img_src['external'] ) )
			{
				$result['external'][] = $this->img_src ;
				continue;
			}
			
			if ( isset( $img_src['exists'] ) ) 
			{
				unset( $result['external'] );
				$result['local'] = $img_src['exists'] ;
				break;
			}

		endforeach;

		if ( ! array_filter( $result ) )
				return false;

		$output  = wp_cache_get( 'catch_first_image_' . $this->post_id, 'firstimg' );
		
		if ( false === $output ) :
			if ( isset( $result['external'] ) ) :
				unset( $result['local'] );

				foreach ( $result['external'] as $k ) :
					$result['external'] = $k;
					break;
				endforeach;
				
				if ( ! array_filter( $result ) )
					return false;
				
			endif;
			$output = $result;
			wp_cache_set( 'catch_first_image_' . $this->post_id, $output, 'firstimg', 300 );
		endif;	

		return $output;
	}
	
	public function image_exist()
	{

		if ( false == $this->image() || empty( $this->img_src ) ) 
			return false;

		$result = array();

		if ( false == $this->is_local_attachment() )
		{
			if ( false == wp_http_validate_url( $this->img_src ) )
				return false;
			
			$result['external'] = $this->img_src;
			return $result;
		}

		$this->img_src = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $this->img_src );
		$img_filename  = str_replace( $this->upurl . '/', '', $this->img_src );
		$img_path      = $this->updir . '/' . $img_filename;

		if ( file_exists( $img_path ) && false != getimagesize( $img_path ) )
		{
			if ( 5 > getimagesize( $img_path )[0] || 5 > getimagesize( $img_path )[1] )
				$result['toosmall'] = array(
					'filename' => $img_filename,
					'path'     => $img_path 
				);
			else
				$result['exists'] = array(
					'filename' => $img_filename,
					'path'     => $img_path,
					'src'      => $this->img_src					
				);
		} else {
			$result['noexists'] = array(
				 'src' => $this->img_src
			);
		}
		return $result;
	}
	
	public function image()
	{
		global $extrp_settings, $extrp_sanitize;

		if ( ! is_admin() && ! $this->is_local_attachment( $this->img_src ) ) :
			if ( ! $extrp_settings['ext_thumb'] )
				return false;
		endif;
		
		if ( ! $this->remote_head() )
			return false;
		
		$filetype = wp_check_filetype( $this->img_src );

		$mime_type = $filetype['type'];
		
		if ( false !== strpos( $mime_type, 'image' ) )
			return $this->img_src; 
		
		return false;
	}
	
	public function is_local_attachment()
	{
		$this->upload_info = wp_upload_dir();
		$this->updir       = $this->upload_info['basedir'];
		$this->upurl       = $this->upload_info['baseurl'];
		$http_prefix       = "http://";
		$https_prefix      = "https://";
		$relative_prefix   = "//";
		
		if ( empty( $this->img_src ) )
			return false;
		
		if ( ! strncmp( $this->img_src, $https_prefix, strlen( $https_prefix ) ) ) :
			$this->upurl = str_replace( $http_prefix, $https_prefix, $this->upurl );
		elseif ( ! strncmp( $this->img_src, $http_prefix, strlen( $http_prefix ) ) ) :
			$this->upurl = str_replace( $https_prefix, $http_prefix, $this->upurl );
		elseif ( ! strncmp( $this->img_src, $relative_prefix, strlen( $relative_prefix ) ) ) :
			$this->upurl = str_replace( array(
				0 => $http_prefix,
				1 => $https_prefix 
			), $relative_prefix, $this->upurl );
		endif;

		if ( false === strpos( $this->img_src, $this->upurl ) )
			return false;
			
		return true;
	}
	
	public function attachment_external()
	{
		global $extrp_settings;
		
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');

		$this->upload_info = wp_upload_dir();
		$this->updir       = $this->upload_info['basedir'];
		$this->upurl       = $this->upload_info['baseurl'];
		
		if ( ! is_admin() )
		{
			if ( array_filter( get_attached_media( 'image', $this->post_id ) ) )
				return false;

			if ( '' != $this->direct_src_external ) 
			{
				$this->img_src = $this->direct_src_external;
				
				if ( $this->img_src != $this->image_exist() )
					return false;

				if ( false == $this->remote_head() )
					return false;
			}
			
			if ( has_post_thumbnail( $this->post_id ) )
				return false;

			if ( empty( $this->img_src ) || 
				empty( $this->post_id ) || 
				$this->img_src != $this->image_exist()['external'] )
			return false;
		}

		$path            = parse_url( $this->img_src, PHP_URL_PATH );
		$filename        = basename( $path );
		$filename_title  = strtok( $filename, '.' );
		
		$result          = media_sideload_image( $this->img_src, $this->post_id, $filename_title, 'src' );

		if ( is_wp_error( $result ) )
			return false;

		$this->img_src            = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $result );
			
		$img_filename             = str_replace( $this->upurl . '/', '', $this->img_src );
		$this->local_img_filename = $img_filename;
		$this->attachment_id      = $this->get_attachment_id();
		
		$image = wp_get_attachment_image_src( $this->attachment_id, 'full' ); 
		
		$image_size = wp_get_attachment_image_src( $this->attachment_id, $this->size );
		
		$data = array(
			'attachment_id' => $this->attachment_id,
			'filename'      => $img_filename,
			'default'       => $image[0],
			'full_src'      => $image[0],
			'size'          => $this->size,
			'src'           => $image_size[0],
			'width'         => $image_size[1],
			'height'        => $image_size[2],
			'crop'          => $image_size[3]
		);
		return $data;	
	}
	
	protected function remote_head()
	{
		if ( empty( $this->img_src ) )
			return false;
		
		$r  = wp_cache_get( 'safe_uri_' . $this->img_src, 'urlexternal' );
		
		if ( false === $r ) :
			$r = wp_remote_head( $this->img_src, array( 'timeout' => 5 ) );

			if ( false === strpos( wp_remote_retrieve_header( $r, 'content-type' ), 'image' ) && 200 != wp_remote_retrieve_response_code( $r ) )
				return false; 

			wp_cache_set( 'safe_uri_' . $this->img_src, $r, 'urlexternal', 300 );
		 endif; 
		
		if ( false != $r ) 
			return $this->img_src;
		return false;
	}
	
	public function process_thumb()
	{
		global $_wp_additional_image_sizes;
		
		$this->img_thumb_url = '';
		
		if ( isset( $_wp_additional_image_sizes[ $this->size ] ) )
		{
			$this->img_width  = $_wp_additional_image_sizes[ $this->size ]['width'];
			$this->img_height = $_wp_additional_image_sizes[ $this->size ]['height'];
		}
		else
		{
			$this->img_width  = get_option( $this->size . '_size_w' );
			$this->img_height = get_option( $this->size . '_size_h' );
		}
		
		$this->thumb_url     = extrp_aq_resize( $this->img_src, $this->img_width, $this->img_height, $this->crop, false, true );

		$this->img_thumb_url = $this->thumb_url[0];
		$this->img_width     = $this->thumb_url[1];
		$this->img_height    = $this->thumb_url[2];
		
		if ( 0 == $this->attachment_id )
			$this->attachment_id = extrp_get_attach_id( $this->img_src );
		$this->data_thumb = array(
		    'attachment_id' => $this->attachment_id,
			'default'       => $this->img_default,
			'full_src'      => $this->img_src,
			'size'          => $this->size,
			'src'           => $this->img_thumb_url,
			'width'         => $this->img_width,
			'height'        => $this->img_height,			
			'crop'          => $this->crop,
			'hwstring'      => image_hwstring( $this->img_width, $this->img_height )
		);
		
		if ( ! array_filter( $this->data_thumb ) )
			return false;
		return $this->data_thumb;
	}
}

function extrp_thumbnail( $args = array() )
{
	$extrp_thumbnail = new EXTRP_Thumbnail( $args );
	return $extrp_thumbnail;
}