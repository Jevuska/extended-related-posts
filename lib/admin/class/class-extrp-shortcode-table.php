<?php
/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'EXTRP_PLUGIN_FILE' ) )
	exit;

if ( ! class_exists( 'WP_List_Table' ) )
{
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class EXTRP_Shortcode_Table extends WP_List_Table
{
	public function prepare_items()
    {
		$columns     = $this->get_columns();
        $hidden      = $this->get_hidden_columns();
        $sortable    = $this->get_sortable_columns();
		$table_class = $this->get_table_classes();
        $data        = $this->table_data();
		
        $this->_column_headers = array(
			$columns,
			$hidden,
			$sortable,
			$table_class
		);
		
		$this->items = $data;
	}
	
	private function table_data()
	{
		global $extrp_data, $extrp_sanitize;
			
		$setting = array_keys( extrp_default_setting( 'shortcode' ) );
		
		$data = [];
		foreach ( $setting as $k ) :
			$id       = absint( $extrp_sanitize->extrp_multidimensional_search( $extrp_data, array( 'parameter' => $k ) ) );
			
			$normal   = $extrp_data[ $id ]['normal'];
			$optional = $extrp_data[ $id ]['optional'];
			
			if ( is_array( $extrp_data[ $id ]['normal'] ) ) :
				$normal = '<kbd>' . implode( '</kbd><kbd>', array_keys( $normal ) ) . '</kbd>';
			else :
				if ( 'post__in' == $k || 'post__not_in' == $k || 'image_size' == $k || 'post_date' == $k )
					$normal = '<em>empty</em>';
				else
					$normal = '<kbd>' . $normal . '</kbd>';
			endif;
			
			if ( is_array( $extrp_data[ $id ]['optional'] ) ) :
				if ( 'relatedby' == $k || 'heading' == $k || 'postheading' == $k || 'display' == $k || 'shape' == $k || 'post_excerpt' == $k ) :
					$optional = '<kbd>' . implode( '</kbd><kbd>', array_values( $optional ) ) . '</kbd>';
				elseif ( 'highlight' == $k ) :
					$optional = '<kbd>' . implode( '</kbd><kbd>', array_keys( $optional ) ) . '</kbd><div class="hlt"></div>';
				else :
					$optional = '<kbd>' . implode( '</kbd><kbd>', array_keys( $optional ) ) . '</kbd>';
				endif;
			else :
				$optional = '<kbd>' . $optional . '</kbd>';;
			endif;
			
			if ( is_array( $extrp_data[ $id ]['description'] ) ) :
				$description = $extrp_data[ $id ][ 'description' ][0];
			else :
				$description = $extrp_data[ $id ]['description'];
			endif;
			
			$data[] = array(
				'id'          => $extrp_data[ $id ]['id'],
				'parameter'   => $extrp_data[ $id ]['parameter'],
				'normal'      => $normal,
				'optional'    => $optional,
				'lang'        => '<i>' . $extrp_data[ $id ]['lang'] . '</i>',
				'description' => $description
			);
		endforeach;
		return $data;
	}
	
	public function column_cb( $item )
	{
        return sprintf( '<label class="screen-reader-text" for="cb-%1$s">%1$s</label><input type="checkbox" value="" id="cb-%1$s" class="%1$s">',
			$item['parameter']
        );
    }
	
	public function get_columns()
    {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'parameter'   => __( 'Parameter', 'extrp' ),
			'normal'      => __( 'Default', 'extrp' ),
			'optional'    => __( 'Option Value', 'extrp' ),
			'lang'        => __( 'Language', 'extrp' ),
			'description' => __( 'Description', 'extrp' )
		);
        return $columns;
    }
	
	public function get_hidden_columns()
    {
        return array();
    }
	
	public function get_sortable_columns()
    {
		return array();
    }
	
	public function get_table_classes()
    {
		$classes   = parent::get_table_classes();
		$classes[] = 'table-extrp';
		return $classes;
    }

	public function column_default( $item, $column_name )
    {
        switch( $column_name )
		{
            case 'id' :
            case 'parameter' :
            case 'normal' :
            case 'optional' :
			case 'lang' :
            case 'description' :
                return $item[ $column_name ];
            default :
                return print_r( $item, true ) ;
        }
    }
}