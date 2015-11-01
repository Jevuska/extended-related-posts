<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

class EXTRP_Admin
{
	private $iecol        = '#ff0000';
	private $ieclass      = 'extrp-query-term';
	private $iecss        = 'text-decoration: underline; color: #ff0000';
	protected $title      = 'Extended Related Posts';
	protected $menu_title = 'EXTRP Related Posts';
	protected $slug       = 'extrp';
	protected $slug_tool  = 'extrp-tool';
	protected $tabs;
	public $with_relevanssi;
	static public function init()
	{
		$class = __CLASS__;
		new $class;
	}
	
	public function __construct()
	{
		
		add_action( 'admin_init', array(
			$this,
			'page_init' 
		) );
		
		add_action( 'admin_menu', array(
			$this,
			'plugin_page' 
		) );
		
		add_action( 'wp_ajax_chk_relevanssi', array(
			$this,
			'chk_relevanssi' 
		) );
		
		add_action( 'wp_ajax_ajx_noimg_view_cb', array(
			$this,
			'ajx_noimg_view_cb' 
		) );
		
	}
	
	public function page_init()
	{
		register_setting(
			'extrp', 
			'extrp_option', 
			array(
				$this,
				'sanitize' 
			) 
		);
		
		register_setting(
			'extrp_tool', 
			'extrp_option_tool', 
			array(
				$this,
				'sanitize' 
			) 
		);
		
		$this->with_relevanssi = get_option( 'extrp_with_relevanssi' );
		$this->plugin_data     = get_plugin_data( EXTRP_PLUGIN_PATH . '/extended-related-posts.php' );
		$this->updates         = extrp_plugin_updates();
	}
	
	public function plugin_page()
	{
		global $extrp_sanitize, $extrp_screen_id, $extrp_screen_id_tool, $extrp_data;
		
		$extrp_data = $extrp_sanitize->big_data();
		
		$item = array( 
			$this->title,
			$this->menu_title,
			$this->slug
		);
		
		$item_tool = array( 
			__( 'Tool of Extended Related Posts', 'extrp' ),
			__( 'EXTRP Tool', 'extrp' ),
			$this->slug_tool
		);
		
		$attr      = array_map( 'esc_attr' , $item );
		$attr_tool = array_map( 'esc_attr' , $item_tool );
		
		$extrp_screen_id = 
			add_options_page(
				$attr[0],
				$attr[1],
				'manage_options',
				$attr[2],
				array(
					$this,
					'create_page'
				)
			);
		
		$extrp_screen_id_tool = 
			add_management_page(
				$attr_tool[0],
				$attr_tool[1],
				'manage_options',
				$attr_tool[2],
				array(
					$this,
					'create_page_tool'
				)
			);
		
		add_action( 'load-' . $extrp_screen_id, array(
			$this,
			'screen_tab' 
		), 20 );
		
		add_action( 'load-plugins.php', 'extrp_set_noimage' );
		
		add_action( 'load-' . $extrp_screen_id_tool, array(
			$this,
			'screen_tab_tool' 
		), 20 );
		
		add_action( 'extrp_set_noimage_first', 'extrp_set_noimage' );
		
		add_action( 'admin_enqueue_scripts', array(
			$this,
			'extrp_enqueu_scripts' 
		) );
		
		add_action( 'admin_footer-' . $extrp_screen_id, array(
			$this,
			'extrp_admin_inline_js' 
		) );
		
		add_action( 'admin_footer-' . $extrp_screen_id_tool, array(
			$this,
			'extrp_admin_inline_js' 
		) );

		add_action( 'admin_footer-widgets.php', array(
			$this,
			'extrp_admin_inline_js' 
		) );
	}
	
	public function sanitize( $input )
	{
		global $extrp_sanitize, $extrp_settings;
		
		$extrp_data = $extrp_sanitize->big_data();
		$default = extrp_default_setting();
		
		$new_input = array();
		
		$id = absint( $extrp_sanitize->extrp_multidimensional_search( $extrp_data, array( 'parameter' => 'post_type' ) ) );
		
		$post_type = array();
		foreach ( $extrp_data[ $id ]['optional'] as $type) :
			$type = sanitize_key( $type );
			if ( isset( $input['post_type_' . $type] ) )
				$post_type[] = $type;
		endforeach;
		
		if ( ! array_filter( $post_type ) )
			$post_type[] = 'post';
		
		$input['post_type'] = $post_type;
		
		$input['post_date'] = array(
			( isset( $input['post_date_show_date'] ) ) ? sanitize_key( $input['post_date_show_date'] ) : '',
			( isset( $input['post_date_time_diff'] ) ) ? sanitize_key( $input['post_date_time_diff'] ) : ''
		);
		
		if ( isset( $input['delcache'] ) )
		{
			global $wpdb;
			
			$caches = wp_cache_get( 'extrp_transient_cache_all', 'extrpcache' );
			
			if ( false == $caches ) :
				$s	    = "%extrp_cache_post_%";
				$sql    = "
						  SELECT option_name
						  FROM $wpdb->options
						  WHERE option_name
						  LIKE %s
						  ";
				$sql    = $wpdb->prepare( $sql, $s );
				$caches = $wpdb->get_col( $sql );
				wp_cache_set( 'extrp_transient_cache_all', $caches, 'extrpcache', 300  );
			endif;
			
			if ( $caches ) :
				$del_transient = array();
				foreach ( $caches as $transient ) :
					if ( ! delete_option( $transient ) )
						$del_transient[] = sanitize_key( $transient );
				endforeach;
				
				if ( false == in_array( 'error', $del_transient ) ) :
					$msg = __( 'Success to delete all cache.', 'extrp' );
					add_settings_error( 'extrp-notices', esc_attr( 'delete-cache' ), $msg, 'updated' );
				else :
					$msg  = __( 'These caches still exist, try to delete again. If error still exist, check your database connection.', 'extrp' );
					$list = implode( '</li><li>', $del_transient );
					$list = sprintf( '<p><ul><li>%s</li></ul></p>', $list );
					add_settings_error( 'extrp-notices', esc_attr( 'delete-cache' ), $msg . $list, 'error' );
				endif;
			else :
				$msg = __( 'Cache was empty, there is no cache need to be deleted.', 'extrp' );
				add_settings_error( 'extrp-notices', esc_attr( 'delete-cache' ), $msg, 'notice-warning' );
			endif;
		}

		if ( ! isset( $input['thumb'] ) && ! isset( $input['post_title'] ) )
		{
			$msg = __( 'Unable to hide the post title if thumbnail not set.', 'extrp' );
				add_settings_error( 'extrp-notices', 'hide-title', $msg, 'notice-warning' );
			$input['post_title'] = (bool) 1;
		}
		
		if ( isset( $input['relevanssi'] ) && 1 == $input['relevanssi'] )
		{
			if ( 0 == $this->with_relevanssi ) :
				add_settings_error( 'extrp-notices', esc_attr( 'error-notice-relevanssi' ), __( 'Unable to use Relevanssi algorithm, please activate/install Relevanssi plugin', 'extrp' ), 'notice-warning' );
				$input['relevanssi'] = (bool) 0;
			endif;
		}
		
		if ( isset( $input['customsize_size'] ) && '' !== $input['customsize_size'] )
		{
			if ( false == $extrp_sanitize->customsize_key( $input['customsize_size'] ) )
			{
				$msg     = __( 'Unable to add this image size, please check your input again.', 'extrp' );
				$keyname = sprintf( '<kbd>%s</kbd>', esc_html( $input['customsize_size'] ) );
				add_settings_error( 'extrp-notices', 'error-notice-customsize', $msg . $keyname, 'error' );
				$input['customsize'] = $extrp_settings['customsize'];
			} else {
				if ( '' != intval( $input['customsize_width'] ) && '' != intval( $input['customsize_height'] ) ) :
					$input['customsize_crop'] = ( isset( $input['customsize_crop'] ) ) ? (bool) 1 : (bool) 0;
		
					$customsize = array();
					for ( $i = 0 ;$i < count( $input['customsize_size'] ) ; $i++ ) :
						$customsize[ $i ] = 
							array(
								'size'   => sanitize_key( $input['customsize_size'] ),
								'width'  => intval( $input['customsize_width'] ),
								'height' => intval( $input['customsize_height'] ),
								'crop'   => wp_validate_boolean( $input['customsize_crop'] ) 
							);
					endfor;
					$input['customsize'] = $customsize;
				else :
					$msg     = __( 'Unable to add this image size, width or height is not defined.', 'extrp' );
					add_settings_error( 'extrp-notices', 'error-notice-customsize', $msg, 'error' );
					$input['customsize'] = $extrp_settings['customsize'];
				endif;
			}
		}
		
		if ( isset( $input['highlight'] ) )
		{
			if ( '' != $input['highlight'] ) :
				$input['hl']  = ( is_array( $input['highlight'] ) ) ? $extrp_sanitize->highlight_name( $input['highlight']['hl'] ) : $extrp_sanitize->highlight_name( $input['highlight'] );
				
				$input['hlt'] = ( isset( $input['hl_val_' . $input['hl']] ) ) ? sanitize_text_field( $input['hl_val_' . $input['hl']] ) : sanitize_key( $input['hl'] );
				
				if ( 'no' != $input['hl'] )
				{
					if ( 'col' == $input['hl'] || 'bgcol' == $input['hl'] )
						$input['hlt'] = $extrp_sanitize->sanitize_hex_color( $input['hlt'] );
					
					if ( 'css' == $input['hl'] )
						$input['hlt'] = sanitize_text_field( $input['hlt'] );
					
					if ( 'class' == $input['hl'] )
						$input['hlt'] = sanitize_html_class( $input['hlt'] );
				};
				
				$input['highlight'] = array(
					 'hl' => $input['hl'],
					'hlt' => $input['hlt']
				);
			else :
				$msg = __( 'Unable to set highlight, please check your input again.', 'extrp' );
				add_settings_error( 'extrp-notices', 'error-notice-highlight', $msg, 'error' );
				$input['highlight'] = $extrp_settings['highlight'];
			endif;
		}
		
		$keys = array_keys( $default );

		if ( isset( $input['reset'] ) && 'Reset' == sanitize_text_field( $input['reset'] ) )
		{
			$msg = __( 'Success to reset your data.', 'extrp' );
			add_settings_error( 'extrp-notices', esc_attr( 'reset-notice' ), $msg, 'updated' );
			$attach_id = extrp_get_attach_id( esc_url_raw( $input['src'] ), null );
			
			if ( ! $attach_id )
			{
				$attach_id_default = extrp_get_attach_id( $extrp_sanitize->noimage_default(), null );
				if ( ! $attach_id_default )
					return extrp_bail_noimage();
			}
			return $default;
		}
		
		if ( isset( $input['src'] ) )
		{
			$attach_id = extrp_get_attach_id( esc_url_raw( $input['src'] ), null );

			if ( ! $attach_id )
			{
				$attach_id_default = extrp_get_attach_id( $extrp_sanitize->noimage_default(), null );
				if ( ! $attach_id_default )
					return extrp_bail_noimage();
				return $extrp_settings;
			}
			
			$input['noimage'] = array(
					'attachment_id' => absint( $input['attachment_id'] ),
					'default'       => $extrp_sanitize->noimage_default(),
					'size'          => sanitize_key( $input['image_size'] ),
					'src'           => esc_url_raw( $input['src'] ),
					'crop'          => ( isset( $input['crop'] ) ) ? wp_validate_boolean( $input['crop'] ) : false
			);
		}
		
		foreach ( $keys as $k ) :
			if ( isset( $input[ $k ] ) )
				$new_input[ $k ] = $input[ $k ];
			else
				$new_input[ $k ] = false;
		endforeach;
		return $extrp_sanitize->sanitize( $new_input );
	}
	
	public function screen_tab()
	{
		global $extrp_screen_id, $extrp_screen_id_tool, $extrp_data;
		
		$screen = get_current_screen();
		if ( $screen->id != $extrp_screen_id )
			return;

		$this->tabs = extrp_tabs_array();
		
		foreach ( $this->tabs as $id => $data )
		{			
			$screen->add_help_tab( array(
				'id' => $id,
				'title' => __( $data['title'], 'extrp' ),
				'callback' => array(
					$this,
					'prepare' 
				) 
			) );
		}

		$screen->set_help_sidebar( extrp_help_sidebar() );
		
		$group = extrp_mb_group();
		foreach ( $group as $k => $v ) :
		
			if ( 'sidebar' == $extrp_data[ $k ]['parameter'] )
			{
				continue;
			}
			
			if ( 'tool' == $v || 'sidebar' == $extrp_data[ $k ]['parameter'] )
			{
				add_meta_box(
					$v,
					ucwords( $extrp_data[ $k ]['subgroup'] ),
					array(),
					$extrp_screen_id_tool,
					'side',
					'core'
				);
				continue;
			}
			
			add_meta_box(
				$v,
				ucwords( $extrp_data[ $k ]['subgroup'] ),
				array(),
				$extrp_screen_id,
				'side',
				'core'
			);
		endforeach;
	}

	public function screen_tab_tool()
	{
		global $extrp_screen_id_tool, $extrp_data;
		
		$screen = get_current_screen();
		if (  $screen->id != $extrp_screen_id_tool )
			return;

		$this->tabs = extrp_tool_tabs_array();
		
		foreach ( $this->tabs as $id => $data )
		{			
			$screen->add_help_tab( array(
				'id' => $id,
				'title' => __( $data['title'], 'extrp' ),
				'callback' => array(
					$this,
					'prepare' 
				) 
			) );
		}

		$screen->set_help_sidebar( extrp_help_sidebar() );
		
		$group = extrp_mb_group();
		foreach ( $group as $k => $v ) :

			if ( 'tool' == $v )
			{
				add_meta_box(
					$v,
					ucwords( $extrp_data[ $k ]['subgroup'] ),
					array(),
					$extrp_screen_id_tool,
					'side',
					'core'
				);
				break;
			}
		endforeach;
	}
	
	public function create_page()
	{
		global $extrp_sanitize, $extrp_screen_id, $extrp_data;

		$screen = get_current_screen();
		if ( $screen->id != $extrp_screen_id )
			return;

		$group = extrp_mb_group();

		foreach ( $group as $k => $v ) :
			if ( 'tool' == $v )
				continue;
			$id = ( 'save' == $v ) ? 'submitdiv' : $v;
			$context = ( 'sidebar' == $extrp_data[ $k ]['parameter'] ) ? 'side' : 'advanced';

			add_settings_section( 
				$id . '_section', 
				'', 
				array(
					$this,
					'print_section_info_' . $id
				), 
				$this->slug . '_' . $id
			);
			
			add_meta_box( 
				$id, 
				strtoupper( $extrp_data[ $k ]['subgroup'] ),
				array(
					$this,
					'callback_meta_box'
				), 
				$this->slug, 
				$context, 
				'high',
				array( 'mb' => $v )
			);
			
			foreach ( $extrp_data as $item ) :
				if ( $id == $item['group'] ) :
					if ( 'sidebar' == $item['parameter'] )
							continue;
					add_settings_field( 
						$item['parameter'] . '_setting', 
						$item['subtitle'], 
						array(
							$this,
							'field_cb' 
						), 
						$this->slug . '_' . $id, 
						$id . '_section',
						array( 'id' => $item['id'] )
					);
				endif;
			endforeach;
		endforeach;
	
		add_filter(
			'admin_footer_text', 
			array( 
				$this, 
				'admin_footer_text' 
			) 
		);
		
		add_filter(
			'update_footer', 
			array( 
				$this, 
				'update_footer' 
			), 
			20 
		);
		
		$this->create_mb();
	}
	
	public function create_page_tool()
	{
		
		global $extrp_screen_id_tool, $extrp_data, $extrp_sanitize;
		
		$screen = get_current_screen();
		if ( $screen->id != $extrp_screen_id_tool )
			return;
		
		$id = absint( $extrp_sanitize->extrp_multidimensional_search( $extrp_data, array( 'parameter' => 'tool') ) );

		add_settings_section( 
				$extrp_data[ $id ]['parameter'] . '_section', 
				'', 
				array(
					$this,
					'print_section_info_' . $extrp_data[ $id ]['parameter']
				), 
				$this->slug . '_' . $extrp_data[ $id ]['parameter']
			);
		
		add_meta_box(
			$extrp_data[ $id ]['group'], 
			strtoupper( $extrp_data[ $id ]['subgroup'] ), 
			array(
				$this,
				'callback_meta_box'
			), 
			$this->slug_tool, 
			'advanced',
			'high',
			array( 'mb' => $extrp_data[ $id ]['group'] )
		);
		
		add_filter( 
			'admin_footer_text', 
			array( 
				$this, 
				'admin_footer_text' 
			) 
		);
		
		add_filter( 
			'update_footer', 
			array( 
				$this, 
				'update_footer' 
			), 
			20 
		);
		
		$this->create_mb_tool();
	}
	
	protected function create_mb()
	{		
		$col = ( 1 == get_current_screen()->get_columns() ) ? '1' : '2';
		
		printf(
			'<div id="extrp" class="wrap">
				<h1>%s</h1>
				<div>%s</div>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-%s">', 
			esc_html( $this->title ),
			__( 'Create a better related posts under your post.', 'extrp' ),
			$col
		);
		?>
					<form method="post" action="options.php">
						<div id="postbox-container-2" class="postbox-container">
							<?php settings_fields( 'extrp' ); ?>
							<?php do_meta_boxes( $this->slug, 'advanced', 'extrp_option' ); ?>
						</div>
						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes( $this->slug, 'side', 'extrp_option' ); ?>
						</div>
						<?php 
							wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
							wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
						?>
						<div class="clear"></div>
					</form>
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<?php
	}
	
	protected function create_mb_tool()
	{
		$col = ( 1 == get_current_screen()->get_columns() ) ? '1' : '2';
	
		printf(
			'<div id="extrp" class="wrap">
				<h1>%s</h1>
				<div>%s</div>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-%s">', 
			__( 'Tool of Extended Related Posts', 'extrp' ),
			__( 'Create a better related posts under your post.', 'extrp' ),
			$col
		);		
		?>
	
					</div>
					<div id="postbox-container" class="postbox-container">
						<?php do_meta_boxes( $this->slug_tool, 'advanced', 'tool' ); ?>
					</div>
			</div>
		</div>
		<?php
	}
	
	public function prepare( $screen, $tab )
	{
		printf(
			'<p>%s</p>', 
			__( $tab['callback'][0]->tabs[ $tab['id'] ]['content'],'extrp' ) 
		);
	}
	
	
	
	public function callback_meta_box( $post = null, $args )
	{
		$option = $args['args']['mb'];
		switch( $option )
		{
			case 'general':
				do_settings_sections( 'extrp_general' );
			break;
			case 'thumbnail':
				do_settings_sections( 'extrp_thumbnail' );
			break;
			case 'additional':
				do_settings_sections( 'extrp_additional' );?>
				<div id="major-publishing-actions">
					<div id="publishing-action">
						<?php submit_button( __( 'Save Changes' ), 'button-primary settings-save', 'save_settings', false, array( 'id' => 'save_settings_footer' ) ); ?>	
					</div>
					<div class="publishing-action">
						<?php submit_button( __( 'Reset', 'extrp' ), 'button-secondary reset', 'extrp_option[reset]', false, array( 'id' => 'reset_settings_footer' ) ); ?> <?php submit_button( __( 'Delete Cache', 'extrp' ), 'button-secondary delete-cache', 'extrp_option[delcache]', false, array( 'id' => 'delete_cache' ) ); ?>
					</div>
					
				<div class="clear"></div>
				</div>
			<?php	
			break;
			case 'save': ?>
				<div id="major-publishing-actions">
					<div id="publishing-action">
						<?php submit_button( __( 'Save Changes', 'extrp' ), 'button-primary settings-save', 'save_settings', false, array( 'id' => 'save_settings_footer' ) ); ?>
					</div>
					<div class="publishing-action">
						<?php submit_button( __( 'Reset', 'extrp' ), 'button-secondary reset', 'extrp_option[reset]', false, array( 'id' => 'reset_settings_footer' ) ); ?>
					</div>
					<div class="clear"></div>
				</div>
			<?php
				do_settings_sections( 'extrp_save' );
			break;
			case 'support':
				do_settings_sections( 'extrp_support' );
			break;
			case 'features':
				do_settings_sections( 'extrp_features' );
			break;
			default:
				do_settings_sections( 'extrp_tool' );
				?>
				<div id="major-publishing-actions">
					<div id="publishing-action">
						<a href="<?php echo admin_url( 'options-general.php?page=' . $this->slug ); ?>" class="button button-secondary button-small" title="<?php _e( 'Back to Settings', 'extrp' ) ?>"><i class="dashicons dashicons-admin-settings"></i></a>
					</div>
					<div class="publishing-action">
						<?php submit_button( __( 'Generate Code', 'extrp' ), 'button-primary', 'generate_code', false, array( 'id' => 'generate_code' ) ); ?>
						<?php submit_button( __( 'Reset', 'extrp' ), 'button-secondary', 'reset_code', false, array( 'id' => 'reset_code' ) ); ?>
					</div>
					<div class="clear"></div>
				</div>
			<?php
			break;
		}
		?>

	<?php
	}
	
	public function print_section_info_general()
	{
		$info = __( 'This are global settings, and some of configuration may affect your default shortcode, unless shortcode that set-up with parameters. Enter your settings below:', 'extrp' );
		printf( '<p>%s</p>', $info );
	}
	
	public function print_section_info_thumbnail()
	{
		$info = __( 'Some of these settings may affect your shortcode settings, especially Thumbnail Custom Size settings. Enter your settings below:', 'extrp' );
		printf( '<p>%s</p>', $info );
	}
	
	public function print_section_info_additional()
	{
		$info = __( 'This are global settings, and some of configuration may affect your default shortcode, unless shortcode with set-up with parameters. Enter your settings below:', 'extrp' );
		printf( '<p>%s</p>', $info );
	}

	public function print_section_info_save()
	{
		$info = __( 'save', 'extrp' );
		printf( '<p>%s</p>', $info );
	}
	
	public function print_section_info_support()
	{
		global $extrp_data, $extrp_sanitize;
		
		$class           = 'extrp-quote';
		$textright       = 'textright';
		$dashicons_email = 'dashicons dashicons-email-alt';
		$url             = $this->plugin_data['AuthorURI'] . '/donate/';
		$title           = __( 'PayPal Donate' );
		$email           = 'contact@jevuska.com';
		
		$id = absint( $extrp_sanitize->extrp_multidimensional_search( $extrp_data, array( 'group' => 'support' ) ) );

		printf( $extrp_data[ $id ]['description'], 
			sanitize_html_class( $class ), 
			sanitize_html_class( $textright ), 
			esc_attr( $dashicons_email ), 
			esc_url( $url ), 
			esc_attr( $title ), 
			sanitize_email( $email )  
		);
	}
	
	public function print_section_info_features()
	{
		printf( '<div id="major-publishing-actions"><div id="publishing-action"><a href="%1$s" class="button button-secondary button-small" title="%2$s">%2$s</a> <a href="%3$s" class="button button-secondary button-small" title="%4$s">%4$s</a></div><div class="publishing-action"><span class="dashicons dashicons-admin-generic"></span></div><div class="clear"></div></div>',
			admin_url( 'widgets.php' ),
			__( 'Setup Widget', 'extrp' ),
			admin_url( 'tools.php?page=' . $this->slug_tool ),
			__( 'Create Shortcode', 'extrp' )
		);
	}
	
	public function print_section_info_tool()
	{
		printf( '<p class="description">%s</p>', 
			wp_kses( __( 'Instruction available under <code>Help</code> tab on your screen ( top right ).', 'extrp' ), array( 'code' => array() ))
		);
		
		$shortcodetable = new EXTRP_Shortcode_Table();
        $shortcodetable->prepare_items();
		$shortcodetable->display();

		printf( '<table class="widefat code-result"><tr><td colspan=6><strong>%s</strong><label for="shortcode-example"><textarea data-autoresize id="shortcode-generator-result" class="large-text code"></textarea></label></td></tr><tr><td colspan=6><strong>%s</strong><label for="phpcode-example"><textarea data-autoresize id="phpcode-generator-result" class="large-text code"></textarea></label></td></tr></tfoot></table>', 
			__( 'Shortcode result', 'extrp' ), 
			__( 'Theme PHP code result', 'extrp' )
		);
	}
	
	
	public function field_cb( $args )
	{
		global $extrp_settings, $extrp_data;
		
		$id                = $args['id'];
		
		$parameter         = $extrp_data[ $id ]['parameter'];
		$input_field       = extrp_checkbox( $parameter );
		
		$optional          = $extrp_data[ $id ]['optional'];
		$description_field = $extrp_data[ $id ]['description'];
		
		$additional_field  = '';
		
		switch ( $parameter )
		{
			case 'active':
				$input_field = extrp_checkbox( $parameter );
			break;
			case 'post_type':
			case 'post_date':
				$input_field = extrp_multiple_checkbox( $parameter, $optional );
			break;
			case 'relatedby':
			case 'display':
			case 'image_size':
			case 'shape':
			case 'post_excerpt':
			case 'highlight':
				$input_field = extrp_selected_input( $parameter, $optional );
				if ( 'display' == $parameter || 'shape' == $parameter)
					$additional_field = extrp_sample_preview( $parameter, $optional );
				if ( 'highlight' == $parameter)
					$additional_field = extrp_hl_input( $extrp_settings[ $parameter ], $parameter, $optional, 'extrp_option' );
			break;
			case 'noimage':
				$input_field = extrp_upload_input( $parameter, $description_field );
				$description_field = $description_field['main_desc'];
			break;
			case 'customsize':
				$input_field = extrp_multiple_input_text( $parameter, $optional, $description_field );
				$description_field = $description_field['main_desc'];
			break;
			case 'posts':
			case 'maxchars':
			case 'expire':
			case 'schedule':
				$input_field = extrp_input_type( $parameter, 'number' );
				if ( 'schedule' == $parameter )
					$description_field = implode( '</br>', $description_field );
			break;
			case 'heading':
			case 'postheading':
				$input_field = extrp_multiple_radio( $parameter, $optional );
			break;
			
			case 'subtitle':
			case 'titlerandom':
				$input_field = extrp_input_type( $parameter, 'text' );
			break;
			
			case 'stopwords':
			case 'post__in':
			case 'post__not_in':
				$input_field = extrp_textarea( $parameter );
			break;
			
			case 'relevanssi':
			case 'cache':
				$description_field = implode( '</br>', $description_field );
			break;
			
			default:
				$input_field = extrp_checkbox( $parameter );
				$additional_field = '';
			break;
		}

		printf( '
				%1$s
				<p class="description">%2$s</p>
			%3$s',
			$input_field,
			$description_field,
			$additional_field
		);
	}
	public function ajx_noimg_view_cb()
	{
		global $extrp_sanitize, $extrp_screen_id;
		
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'heartbeat-nonce' ) || ! check_ajax_referer( 'heartbeat-nonce', 'nonce', false ) )
		{
			$noperm = array(
				'result' => array(
					'msg'     => __( 'You do not have permission to do that.', 'extrp' ),
					'tokenid' => (int) 3 
				)
			);
			$msg = wp_json_encode( $noperm );
			wp_die( $msg );
		}

		if ( isset( $_POST['chk'] ) && 'settings_page_extrp' == sanitize_key( $_POST['chk'] ) )
		{
			
			$thumb = $extrp_sanitize->data_thumb( array(
				'size' => $extrp_sanitize->size( $_POST['size'] ),
				'src'  => esc_url( $_POST['src'] ),
				'crop' => wp_validate_boolean( $_POST['crop'] )
			) );
			
			if ( ! $thumb )
			{
				$fail = array(
					'result' => array(
						'msg'     => __( 'Fail to generate image. Refresh your page and try again.', 'extrp' ),
						'tokenid' => (int) 2 
					) 
				);
				$msg  = wp_json_encode( $fail );
				wp_die( $msg );
			}
			
			if ( ! $thumb['src'] )
			{
				$noperm = array(
					'result' => array(
						'msg'     => __( 'Image not exists. Please check your image from media library. You can add new one or push &#39;Save Changes&#39; button to get default image directly.', 'extrp' ),
						'tokenid' => (int) 4
					)
				);
				$msg    = wp_json_encode( $noperm );
				wp_die( $msg );
			};
		
			$success = array(
				'result' => array(
					'title'     => get_the_title( intval( $_POST['attach_id'] ) ),
					'src'       => esc_url( $_POST['src'] ),
					'thumbnail' => esc_url( $thumb['src'] ),
					'size'      => sanitize_key( $thumb['size'] ),
					'width'     => intval( $thumb['width'] ),
					'height'    => intval( $thumb['height'] ),
					'crop'      => wp_validate_boolean( $thumb['crop'] ),
					'shape'     => $extrp_sanitize->shape( $_POST['shape'] ),
					'msg'       => __( 'Success', 'extrp' ),
					'tokenid'   => (int) 1
				) 
			);
			
			$result = wp_json_encode( $success );
			wp_die( $result );
		}
		else
		{
			$noperm = array(
				'result' => array(
					'msg'     => __( 'You do not have permission to do that.', 'extrp' ),
					'tokenid' => (int) 3 
				) 
			);
			$msg    = wp_json_encode( $noperm );
			wp_die( $msg );
		}
	}
	
	public function chk_relevanssi()
	{
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'heartbeat-nonce' ) || ! check_ajax_referer( 'heartbeat-nonce', 'nonce', false ) )
		{
			$error = array(
				'result' => (int) 3 
			);
			$msg   = wp_json_encode( $error );
			wp_die( $msg );
		}

		if ( isset( $_POST['chk'] ) && ( 'tools_page_extrp-tool' == sanitize_key( $_POST['chk'] ) || 'widgets' == sanitize_key( $_POST['chk'] ) ) )
		{
			if ( 1 == get_option( 'extrp_with_relevanssi' ) && function_exists( 'relevanssi_do_query' ) ) :
				$success = array(
					'result' => (bool) 1 
				);
				$msg     = wp_json_encode( $success );
			else :
				$fail = array(
					'result' => (bool) 0 
				);
				$msg  = wp_json_encode( $fail );
			endif;
		}
		else
		{
			$broken = array(
				 'result' => (int) 2 
			);
			$msg    = wp_json_encode( $broken );
		}
		wp_die( $msg );
	}
	public function admin_footer_text()
	{
		$html = '<span id="footer-thankyou">&copy; 2015 - %s %s %s</p>';
		
		printf( $html,
			esc_html( $this->plugin_data['Name'] ), 
			__( 'plugin by', 'extrp' ), 
			$this->plugin_data['Author']
		);
	}
	
	public function update_footer()
	{
		$txt = '%s %s';
		
		printf( $txt, 
			__( 'Version', 'extrp' ), 
			esc_html( $this->plugin_data['Version'] )
		);
	}
	
	public function extrp_enqueu_scripts()
	{
		global $extrp_settings, $extrp_screen_id, $extrp_screen_id_tool;

		$screen = get_current_screen();

		if ( $extrp_screen_id != $screen->id && $extrp_screen_id_tool != $screen->id && 'widgets' != $screen->id ) 
			return;

		wp_enqueue_style( 'extrp', extrp_style_uri(), array(), EXTRP_PLUGIN_VERSION, false );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'extrp-script-handle', EXTRP_PLUGIN_URL . 'lib/assets/js/jquery.extrp.js', array(
			 'wp-color-picker' 
		), EXTRP_PLUGIN_VERSION, true );
		wp_enqueue_media();
		
		wp_localize_script( 'extrp-script-handle', 'extrpL10n', array(
			'subtitletxt'     => __( 'Related News', 'extrp' ),
			'titlerandomtxt'  => __( 'Random News', 'extrp' ),
			'notice1'         => __( 'You don&#39;t need this parameter by this highlight type.', 'extrp' ),
			'notice2'         => __( 'Please choose type of highlight first.', 'extrp' ),
			'notice3'         => __( 'Its default value, enter another one.', 'extrp' ),
			'notice4'         => __( 'No Value', 'extrp' ),
			'notice5'         => __( 'Activate or install Relevanssi plugin', 'extrp' ),
			'ok'              => __( 'OK', 'extrp' ),
			'null'            => __( 'empty', 'extrp' ),
			'mediatitle'      => __( 'Select or Upload Media Of Your Chosen Persuasion', 'extrp' ),
			'buttonmediatext' => __( 'Set as No Image Thumbnail', 'extrp' ) 
		) );
		
		wp_localize_script( 'extrp-script-handle', 'extrpSet', array(
			'iecol'    => $this->iecol,
			'ieclass'  => $this->ieclass,
			'iecss'    => $this->iecss,
			'hl'       => '',
			'noimage'  => $extrp_settings['noimage']['default']
		) );
		add_thickbox();
		$extrp_css = '
			.column-parameter, .column-lang{width:10%}
			.column-normal{width:11%}
			.column-description{width:20%}
		';
							
		if ( $screen->id ==  $extrp_screen_id_tool )
			wp_add_inline_style( 'extrp', $extrp_css );
	}
	
	public function extrp_admin_inline_js()
	{
		print "<script type='text/javascript'>\n";
		print "jQuery( document ).ready( function( $ ) {\n";
		print "$( this ).extrp();\n";
		print "} );\n";
		print "</script>\n";
	}
}