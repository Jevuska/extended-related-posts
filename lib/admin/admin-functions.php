<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

add_action( 'load-extrp-admin-page', array(
	'EXTRP_Admin',
	'init' 
) );

function extrp_plugin_updates()
{
	$current_version = get_option( 'extrp_version' );

	if ( version_compare( $current_version, EXTRP_PLUGIN_VERSION, '<' ) )
	{
		include( EXTRP_ADMIN_PATH . 'updates/extrp-1.0.php' );
		update_option( 'extrp_version', EXTRP_PLUGIN_VERSION );
	}
}

function extrp_plugin_action_links( $actions, $plugin_file ) 
{
	static $plugin;
	
	if ( ! isset( $plugin ) )
		$plugin = plugin_basename( EXTRP_PLUGIN_FILE );

	if ( $plugin == $plugin_file )
	{
			$settings  = array( 
				'settings' => '<a href="options-general.php?page=extrp">' . __( 'Settings', 'extrp' ) . '</a>' );
			$actions = array_merge( $settings, $actions );
	}
	return $actions;
}

function extrp_tabs_array()
{
	$tabs = array(
		'overview' => array(
			'title'   => __( 'Overview', 'extrp' ),
			'content' => __( 'Extended Related Posts plugin could be supported by Relevanssi plugin if you activate the feature in plugin administration area. Searching related posts by post title is default search algorithm of this plugin, as WordPress search default. And if no results by post title, it will continued get the related posts by their categories automatically, then by tags, and by split each post title words. If no more relevant posts, it will show your random posts. Abuse with any extension that you made is welcome to support this plugin. Have fun!.', 'extrp' )
		),
		'troubleshooting' => array(
			'title'   => __( 'Troubleshooting', 'extrp' ), 
			'content' => __( 'Try to uninstall then install this plugin again if you get an error php code. Using caching to make your pages become static and make your website load faster, it also lessens the load on your server’s CPU, Memory and HD.', 'extrp' )
		)
	);
	return $tabs;
}

function extrp_tool_tabs_array()
{
	$html = '<h3>%s</h3><kbd class="main-sc">%s</kbd><p class="description">%s</p><h4>%s</h4><p><kbd>%s</kbd></p><h3>%s</h3><kbd class="main-pc">%s</kbd><h4>%s</h4><kbd>%s</kbd><h4>%s</h4><p><pre><kbd>%s</kbd></pre></p> %s <p><pre><kbd>%s</kbd></pre></p><p class="description">%s</p>';
	
	$usage = sprintf( $html, 
		__( 'Main Shortcode', 'extrp' ), 
		'[jv-related-posts]', 
		__( 'It will display related posts as list style without thumbnail. Set the size parameter to show the thumbnails as configuration below.', 'extrp' ), 
		__( 'Shortcode with parameters', 'extrp' ), 
		'[jv-related-posts size="thumbnail" display="right" shape="rounded" subtitle="' . __( 'Related News', 'extrp' ) . '" randomtitle="' . __( 'Another News', 'extrp' ) . '" crop=0]', 
		__( 'Main PHP Code for theme', 'extrp' ), 
		'&lt;?php do_action(&#39;jv-related-posts&#39;); ?&gt;', 
		__( 'Use post id&#39;s only, specify post NOT to retrieve.', 'extrp' ), 
		'&lt;?php do_action(&#39;jv-related-posts&#39;,7); ?&gt;', 
		__( 'PHP Code with parameters', 'extrp' ), 
"&lt;?php do_action( &#39;jv-related-posts&#39;,
  array(
   'posts' => 7,
   'post__not_in' => array(8,25,51),
   'size' => 'medium',
   'crop' => 1
   ) 
); ?&gt;",
		__( 'or', 'extrp' ),
"&lt;?php
 &#36args = array( 
  'posts' => 7,
  'post__not_in' => array(8,25,51),
  'size' => 'medium',
  'crop' => 1
  );
  do_action( &#39;jv-related-posts&#39;, &#36args );
?&gt;", 
		__( 'Note: For PHP Code, the values of the un-set parameters will be set by your settings above, not default shortcode values. Parameters settings are the same with shortcode configuration. ', 'extrp' )
	);
	
	$tabs = array( 
		'overview' => array(
			'title'   => __( 'Overview', 'extrp' ),
			'content' => wp_kses( __( 'Set parameters for your shortcode, and add into post area or text widget. PHP code for theme, add inside sitewide page content loop function on theme files (ie: <kbd>content.php</kbd> in <em>twentyfifteen</em> theme), usually right after the content code <kbd>the_content</kbd>. Use Code Generator below for more convenience.', 'extrp' ), array(
				'kbd' => array(),
				'em' => array()
			) )
		),
		'usage' => array(
			'title'   => __( 'Usage', 'extrp' ), 
			'content' => $usage
		), 
		'instruction' => array(
			'title'   => __( 'Instruction', 'extrp' ),
			'content' => wp_kses( __( '<strong>Check</strong> the parameters; <strong>Click</strong> their optional value to selected; then <strong>Push</strong> <code>Generate Code</code> button.<br>Make sure that selected values are <code class="wp-ui-highlight">highlighted</code> by single click.<br>The values of <code>0</code> = <em>false/disable</em> and <code>1</code> = <em>true/enable</em>.<br>Require browser javascript enable.', 'extrp' ), array(
				'br'     => array(),
				'strong' => array(),
				'code'   => array(
					'class' => array()
				),
				'em' => array()
			) )
		)
	);
	return $tabs;
}

function extrp_help_sidebar()
{
	$plugin_data   = get_plugin_data( EXTRP_PLUGIN_FILE );
	$github_url    = extrp_github_url();
	$html = '<p><strong>%1$s</strong></p><p><a href="%2$s" target="_blank">%3$s</a></p><p><a href="%4$s" target="_blank">%5$s</a></p>';
	$output = sprintf( $html,
		__( 'For more information:', 'extrp' ),
		$plugin_data['PluginURI'],
		__( 'Plugin Page', 'extrp' ),
		esc_url( $github_url ),
		'GitHub'
	);
	return $output;
}

function extrp_github_url()
{
	$plugin_data   = get_plugin_data( EXTRP_PLUGIN_FILE );
	$authorname    = $plugin_data['AuthorName'];
	$github_repo   = 'extended-related-posts';
	$github_url    = "https://github.com/$authorname/$github_repo/";
	return $github_url;
}

function extrp_mb_group()
{
	global $extrp_sanitize;
	$data = $extrp_sanitize->big_data();
	$group = [];
	for ( $i = 1 ; $i <= count( $data ) ; $i++ ) :
		$group[ $data[ $i ]['id'] ] = $data[ $i ]['group'];
	endfor;
	return array_unique( $group );
}

function extrp_checkbox( $type )
{
	global $extrp_settings;
	
	$checked = '';
	if ( ( isset( $extrp_settings[ $type ] ) && $extrp_settings[ $type ] == true ) )
		$checked = 'checked="checked"';
	
	$output = 
		sprintf( 
			'<label for="%1$s"><input type="checkbox" id="%1$s" name="extrp_option[%1$s]" value="%2$s" %3$s/></label>', 
			esc_attr( $type ), 
			true, 
			$checked
		);
	return $output;
}

function extrp_multiple_checkbox( $type, $array )
{
	global $extrp_settings;
	
	$input_field = '';
	
	foreach ( $array as $k => $v) :
		$checked = in_array( $k, $extrp_settings[ $type ] ) ? 'checked="checked"' : '';
		$desc = ( ! empty( $v ) ) ? $v : $k;
		$input_field .= 
			sprintf( 
				'<label for="%1$s_%2$s">
					<input 
					id="%1$s_%2$s" 
					name="extrp_option[%1$s_%2$s]" 
					type="checkbox" 
					value="%2$s" 
					%3$s>%4$s  
				</label>',
				esc_attr( $type ), 
				esc_attr( $k ), 
				$checked, 
				ucwords( esc_attr( $desc ) ) 
			);
	endforeach;
	
	return $input_field;
}

function extrp_multiple_radio( $type, $array )
{
	global $extrp_settings;
	
	$input_field = '';
	
	foreach ( $array as $k => $v) :
		$selected = ( $extrp_settings[ $type ] == $v ) ? 'checked="checked"' : '';
		$desc = ( ! empty( $v ) ) ? $v : $k;
		$input_field .= 
			sprintf( 
				'<label for="%1$s_%2$s">
					<input 
					id="%1$s_%2$s" 
					name="extrp_option[%1$s]" 
					type="radio" 
					value="%2$s" 
					%3$s>%4$s  
				</label>',
				esc_attr( $type ), 
				esc_attr( $v ), 
				$selected, 
				ucwords( esc_attr( $desc ) ) 
			);
	endforeach;
	
	return $input_field;
}

function extrp_selected_input( $type, $array )
{
	global $extrp_settings;
	
	$html = '';
	$class = '';
	foreach ( $array as $k => $v )
	{
		$classhl = '';
		
		if ( 'image_size' == $type )
			$val = $k;
		else
			$val = $v;
		
		$selected = ( $extrp_settings[ $type ] == $val ) ? 'selected="selected"' : '';
		if ( 'image_size' == $type ) :
			$class .= 'class="set-noimage change-size"';
			$text   = $k . ' - ' . $v['width'] . ' x ' . $v['height'];
		elseif ( 'highlight' == $type ) :
			$val      = $k;
			$text     = $v[0];
			$classhl .= 'class="select-' . $k . '"';
			$selected = ( $extrp_settings[ $type ]['hl'] == $val ) ? ' selected="selected"' : '';
		elseif ( 'post_excerpt' == $type ) :
			$val  = $v;
			$text = ucwords( $k );
		else :
			$text = ucwords( $v );
		endif;
		
		$html .= "<option $classhl value='$val' $selected>";
		$html .= $text;
		$html .= "</option>";
	}
		
	$input_field = 
		sprintf( 
			'<label for="%1$s">
				<select %2$s id="%1$s" 
				name="extrp_option[%1$s]" class="highlight-select"
				>
				%3$s
				</select>
			</label>', 
			$type,
			$class,			
			$html 
		);
	
	return $input_field;
}

function extrp_hl_input( $extrp_settings, $type, $option, $name )
{
	$text_arr = array(
			'col'   => __( 'Use HTML color codes (#rrggbb)', 'extrp' ),
			'bgcol' => __( 'Use HTML color codes (#rrggbb)', 'extrp' ),
			'css'   => esc_html__( 'You can use any CSS styling here, style will be inserted with a <span>', 'extrp' ),
			'class' => esc_html__( 'Name a class here, the terms will be wrapped in a <span> with the class', 'extrp' ) 
		);
	
	$input  = '';
	$i      = 0;

	foreach ( $option as $k => $v )
	{
	$i++;
		if ( (int) 5 > $i )
			continue;
		$input .= sprintf( 
			'<div class="cp cp-%1$s"><input type="text" name="%2$s[hl_val_%1$s]" value="%3$s" class="%1$s-field" id="cp-%1$s"> <p id="%1$s-description" class="description">%4$s</p></div>',
			sanitize_html_class( $k ),
			esc_attr( $name ),
			( $extrp_settings['hl'] == $k ) ? sanitize_text_field( $extrp_settings['hlt'] ) : sanitize_text_field( $v[1] ),
			$text_arr[ $k ]
		);
	}
	
	return $input;
}

function extrp_input_type( $type, $inputype, $attr = '' )
{
	global $extrp_settings;
	
	$html = '<label for="%1$s"><input id="%1$s" type="%2$s" name="extrp_option[%1$s]" value="%3$s" %4$s></label>';

	if ( 'number' == $inputype )
		$value = absint( $extrp_settings[ $type ] );
	else
		$value = sanitize_text_field( $extrp_settings[ $type ] );
	
	$input_field = sprintf( $html,
		esc_attr( $type ),
		esc_attr( $inputype ),
		$value,
		esc_attr( $attr )
	);
	return $input_field;
}

function extrp_textarea( $type )
{
	global $extrp_settings, $extrp_sanitize;
	$html = '<label for="%1$s"><textarea id="%1$s" class="large-text code" rows=3 name="extrp_option[%1$s]">%2$s</textarea></label>';
	
	$value  = esc_textarea( $extrp_sanitize->data_textarea( $extrp_settings[ $type ] ) );
	$output = sprintf( $html,
		$type,
		( '' != $value ) ? $value : ''
	);
	return $output;
}

function extrp_sample_preview( $type, $array )
{
	global $extrp_settings;
	
	if ( 'shape' == $type )
		$html = '<div class="thumb-default" data-thumb="image-shapes">';
	else
		$html = '<div class="extrp-display-image">';
	for ( $i = 0; $i < count( $array ); $i++ ) :
	$active = ( $extrp_settings[ $type ] == $array[ $i ] ) ? ' active current' : '';
	$html .= sprintf( '<div class="extrp-out-box"><div data-img="%1$s"  class="extrp-%1$s sample-%1$s-%2$s%3$s"><div></div></div><span>%2$s</span></div>',esc_attr( $type ), esc_attr( $array[ $i ] ), $active );
	endfor;
	$html .= '</div>';

	return $html;
}
	
function extrp_multiple_input_text( $type, $input_array, $desc_array )
{
	global $extrp_settings;

	if ( isset( $extrp_settings['customsize'] ) && '' != $extrp_settings['customsize'][0]['size'] )
		$input_array = $extrp_settings['customsize'];
	
	$html = '';
	for( $i = 0 ; $i < count( $input_array ) ; $i++ ) :
		$field = [];
		$checked = '';
		foreach ( $input_array[ $i ] as $input => $v ) :
		
			if ( 'size' == $input ) :
				$input_type = 'text';
			elseif ( 'crop' == $input ) :
				$input_type = 'checkbox';
				$checked = ( true == $v ) ? 'checked="checked"' : '';
			else :
				$input_type = 'number';
			endif;
			
			$field[] = 
				sprintf(
					'<label for="%1$s_%2$s"><input id="%1$s_%2$s" type="%3$s" name="extrp_option[%1$s_%2$s]" value="%4$s" %5$s> <span class="description">%6$s</span></label>',
					$type,
					$input,
					$input_type,
					$v,
					$checked,
					$desc_array[ $input ]
				);
		endforeach;
		$html .= '<ul><li>' . implode( '</li><li>', $field ) . '</li></ul>';
	endfor;
	return $html;
}

function extrp_upload_input( $type, $txt_arr )
{
	global $extrp_settings, $extrp_sanitize;
	
	$full_src  = esc_url_raw( $extrp_settings[ $type ]['full_src'] );
	
	$html = '<input type="text" value="%1$s" class="regular-text noimage" id="set-noimage" name="extrp_option[src]" readonly="readonly"><input id="attachment_id" name="extrp_option[attachment_id]" type="hidden" value="%2$s">%3$s %4$s<div class="custom-img-container">%5$s</div>';
	
	$img_src   = esc_url_raw( $extrp_settings[ $type ]['src'] );
	
	if ( false == extrp_get_attach_id( $img_src ) )
	{
		$attention = __( 'Image not exists. Please check your image from media library. You can add new one ', 'extrp' );

		if ( '' == $img_src )
		{
			$attention = __( 'The url default image not set up. You can add new one ', 'extrp' );
		}
		
		$attention .= __( 'or push &#39;Save Changes&#39; button to get default image directly.', 'extrp' );
		
		$attention = sprintf( '<div id="message" class="error fade notice is-dismissible"><p>%1$s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%2$s</span></button></div>', $attention, __( 'Dismiss this notice.', 'extrp' ) );
		
		return sprintf( $html,
			$full_src,
			absint( $extrp_settings[ $type ]['attachment_id'] ),
			get_submit_button( $txt_arr['btn_upload_txt'], 'set-noimage button thickbox', 'new_image', false ),
			get_submit_button( $txt_arr['btn_reset_txt'], 'set-noimage button reset-noimage', 'reset', false ),
			$attention
		);
	}
	
	
	$attach_id = extrp_get_attach_id( $full_src, null );
	
	if ( false != $attach_id )
	{
		$html_img_preview = sprintf( '<p><a href="%1$s" class="thickbox" title="%2$s"><img width="%3$s" height="%4$s" src="%5$s" class="extrp-shape-%6$s" data-size="%7$s" data-id="%8$s" alt="%2$s" id="upload-custom-img" data-title="%2$s"></a></p>',
			$full_src,
			get_the_title( $attach_id ),
			intval( $extrp_settings[ $type ]['width'] ), 
			intval( $extrp_settings[ $type ]['height'] ), 
			$img_src, 
			sanitize_key( $extrp_settings['shape'] ), 
			sanitize_key( $extrp_settings[ $type ]['size'] ),
			absint( $attach_id )
		);
		
		return sprintf( $html,
			$full_src,
			absint( extrp_get_attach_id( $extrp_settings['noimage']['default'], null ) ),
			get_submit_button( $txt_arr['btn_upload_txt'], 'set-noimage button thickbox', 'new_image', false ),
			get_submit_button( $txt_arr['btn_reset_txt'], 'set-noimage button reset-noimage', 'reset', false ),
			$html_img_preview
		);
	}
}
?>