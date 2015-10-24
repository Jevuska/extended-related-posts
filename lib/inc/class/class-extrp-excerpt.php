<?php
//Search Excerpt Plugin by Scott Yang http://scott.yang.id.au/pages/search-excerpt.html 
//:: Modify by JEVUSKA

/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;
if ( ! defined( 'ABSPATH' ) )
	exit;


if ( ! defined( '_EXTRP_LEN_SEARCH' ) )
	define( '_EXTRP_LEN_SEARCH', 15 );

class EXTRP_Excerpt
{
	
	private static $instance;
	
	public $post_id = null;
	public $q;
	public $highlight;
	public $maxchars;
	public $excerpt;
	private $keys;
	private $content;
	
	public function __construct( $args = array() )
	{
		if ( ! is_array( $args ) )
			return;
		$args = wp_parse_args( $args, array(
			 'col_css' => 'style=color:' 
		) );
		
		$this->post_id   = ( null === $args['post_id'] ) ? get_the_ID() : (int) $args['post_id'];
		$this->q         = $args['q'];
		$this->highlight = $this->highlight_html( $args['highlight'] );
		$this->maxchars  = $this->countwords( absint( $args['n'] ) );
		$this->excerpt   = $this->get_the_excerpt();
	}
	
	public static function getInstance()
	{
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function get()
	{
		return $this->excerpt;
	}
	
	protected function countwords( $n = '' )
	{
		return extrp_max_chars( $n );
	}
	
	protected function post_content()
	{
		$post = get_post( $this->post_id );
		if ( !empty( $post->post_password ) ) {
			if ( stripslashes( $_COOKIE['wp-postpass_' . COOKIEHASH] ) != $post->post_password ) {
				return get_the_password_form();
			}
		}
		return strip_shortcodes( $post->post_content );
	}
	
	protected function get_query()
	{
		static $last = null;
		static $lastsplit = null;
		
		if ( $last == $this->q )
			return $lastsplit;
		$text = preg_replace( '/[._-]+/', '', $this->q );
		
		$r     = explode( ' ', $text );
		$words = extrp_filter_stopwords( $r );
		
		$last      = $text;
		$lastsplit = $words;
		return $words;
	}
	
	protected function highlight_excerpt()
	{
		$text = strip_tags( $this->content );
		for ( $i = 0; $i < sizeof( $this->keys ); $i++ )
			$this->keys[ $i ] = preg_quote( $this->keys[ $i ], '/' );
		$workkeys = $this->keys;
		$ranges   = array();
		$included = array();
		$length   = 0;
		while ( 256 > $length && count( $workkeys ) ) {
			foreach ( $workkeys as $k => $key ) {
				if ( 0 == strlen( $key ) ) {
					unset( $workkeys[ $k ] );
					continue;
				}
				if ( 256 <= $length ) {
					break;
				}
				
				if ( ! isset( $included[ $key ] ) ) {
					$included[ $key ] = 0;
				}
				
				if ( preg_match( '/' . $key . '/iu', $text, $match, PREG_OFFSET_CAPTURE, $included[ $key ] ) ) {
					$p       = $match[0][1];
					$success = 0;
					if ( false !== ( $q = strpos( $text, ' ', max( 0, $p - 60 ) ) ) && $q < $p ) {
						$end = substr( $text, $p, 80 );
						if ( false !== ( $s = strrpos( $end, ' ' ) ) && 0 < $s ) {
							$ranges[ $q ] = $p + $s;
							$length += $p + $s - $q;
							$included[ $key ] = $p + 1;
							$success        = 1;
						}
					}
					
					if ( ! $success ) {
						
						$q = $this->_jamul_find_1stbyte( $text, max( 0, $p - 60 ) );
						$q = $this->_jamul_find_delimiter( $text, $q );
						$s = $this->_jamul_find_1stbyte_reverse( $text, $p + 80, $p );
						$s = $this->_jamul_find_delimiter( $text, $s );
						if ( ( $s >= $p ) && ( $q <= $p ) ) {
							$ranges[ $q ] = $s;
							$length += $s - $q;
							$included[ $key ] = $p + 1;
						} else {
							unset( $workkeys[ $k ] );
						}
					}
				} else {
					unset( $workkeys[ $k ] );
				}
			}
		}
		
		if ( 0 == sizeof( $ranges ))
			return '<p>' . $this->_jamul_truncate( $text, $this->maxchars ) . '&nbsp;...</p>';
		
		ksort( $ranges );
		
		$newranges = array();
		foreach ( $ranges as $from2 => $to2 ) {
			if ( ! isset( $from1 ) ) {
				$from1 = $from2;
				$to1   = $to2;
				continue;
			}
			if ( $from2 <= $to1 ) {
				$to1 = max( $to1, $to2 );
			} else {
				$newranges[ $from1 ] = $to1;
				$from1             = $from2;
				$to1               = $to2;
			}
		}
		$newranges[ $from1 ] = $to1;
		
		$out = array();
		foreach ( $newranges as $from => $to )
		{
			$out[] = substr( $text, $from, $to - $from );
		}
		


		$text = ( isset( $newranges[0] ) ? '' : '...&nbsp;' ) . implode( '&nbsp;...&nbsp;', $out ) . '&nbsp;...';
		
		//maxchars
		$text = extrp_max_charlength(
				$this->maxchars, 
				$text
			);

		$text = preg_replace( 
			'/(' . implode( '|', $this->keys ) . ')/iu', $this->highlight, $text 
		);
		return "<p>$text</p>";
	}
	
	protected function highlight_html( $args )
	{
		if ( 'no' != $args[0] ) :
			$array = array(
				'strong',
				'mark',
				'em' 
			);
			if ( in_array( $args[0], $array ) ) :
				return sprintf( '<%1$s>\0</%1$s>', $args[0] );
			else :
				if ( 'col' == $args[0] )
					return sprintf( '<span style="color:%s">\0</span>', $args[1] );
				if ( 'bgcol' == $args[0] )
					return sprintf( '<span style="background-color:%s">\0</span>', $args[1] );
				if ( 'css' == $args[0] )
					return sprintf( '<span style="%s">\0</span>', $args[1] );
				if ( 'class' == $args[0] )
					return sprintf( '<span %s>\0</span>', $args[1] );
			endif;
		else :
			return '\0';
		endif;
	}
	
	protected function get_the_excerpt()
	{
		static $filter_deactivated = false;
		global $more;
		global $wp_query;
		
		if ( ! $filter_deactivated ) {
			remove_filter( 'the_excerpt', 'wpautop' );
			$filter_deactivated = true;
		}
		
		$more          = 1;
		$this->keys    = $this->get_query();
		$this->content = $this->post_content();
		
		return $this->highlight_excerpt();
	}
	
	protected function _jamul_find_1stbyte( $string, $pos = 0, $stop = -1 )
	{
		$len = strlen( $string );
		if ( 0 > $stop || $stop > $len ) {
			$stop = $len;
		}
		for ( ; $pos < $stop; $pos++ ) {
			if ( ( ord( $string[ $pos ] ) < 0x80 ) || ( ord( $string[ $pos ] ) >= 0xC0 ) ) {
				break;
			}
		}
		return $pos;
	}
	
	protected function _jamul_find_1stbyte_reverse( $string, $pos = -1, $stop = 0 )
	{
		$len = strlen( $string );
		if ( 0 > $pos || $pos >= $len ) {
			$pos = $len - 1;
		}
		for ( ; $pos >= $stop; $pos-- ) {
			if ( ( ord( $string[ $pos ] ) < 0x80 ) || ( ord( $string[ $pos ] ) >= 0xC0 ) ) {
				break;
			}
		}
		return $pos;
	}
	
	protected function _jamul_find_delimiter( $string, $pos = 0, $min = -1, $max = -1 )
	{
		$len = strlen( $string );
		if ( 0 == $pos || 0 > $pos || $pos >= $len ) {
			return $pos;
		}
		if ( 0 > $min ) {
			$min = max( 0, $pos - _EXTRP_LEN_SEARCH );
		}
		if ( 0 > $max || $max >= $len ) {
			$max = min( $len - 1, $pos + _EXTRP_LEN_SEARCH );
		}
		if ( ord( $string[ $pos ] ) < 0x80 ) {
			
			$pos3 = -1;
			for ( $pos2 = $pos; $pos2 <= $max; $pos2++ ) {
				if ( $string[ $pos2 ] == ' ' ) {
					break;
				} else if ( 0 > $pos3 && ord( $string[ $pos2 ] ) >= 0x80 ) {
					$pos3 = $pos2;
				}
			}
			if ( $pos2 > $max && 0 <= $pos3 ) {
				$pos2 = $pos3;
			}
			if ( $pos2 > $max ) {
				$pos3 = -1;
				for ( $pos2 = $pos; $pos2 >= $min; $pos2-- ) {
					if ( $string[ $pos2 ] == ' ' ) {
						break;
					} else if ( 0 > $pos3 && ord( $string[ $pos2 ] ) >= 0x80 ) {
						$pos3 = $pos2 + 1;
					}
				}
				if ( $pos2 < $min && 0 <= $pos3 ) {
					$pos2 = $pos3;
				}
			}
			if ( $pos2 <= $max && $pos2 >= $min ) {
				$pos = $pos2;
			}
		} else if ( ( ord( $string[ $pos ] ) >= 0x80 ) || ( ord( $string[ $pos ] ) < 0xC0 ) ) {
			$pos = $this->_jamul_find_1stbyte( $string, $pos, $max );
		}
		return $pos;
	}
	
	protected function _jamul_truncate( $string, $byte )
	{
		$len = strlen( $string );
		if ( $len <= $byte )
			return $string;
		$byte = $this->_jamul_find_1stbyte_reverse( $string, $byte );
		return substr( $string, 0, $byte );
	}
}

function extrp_excerpt( $args = array() )
{
	$extrp_excerpt = new EXTRP_Excerpt( $args );
	return $extrp_excerpt;
}
?>