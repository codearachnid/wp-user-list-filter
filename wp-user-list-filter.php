<?php

if ( !defined( 'ABSPATH' ) )
	die( '-1' );

if( !class_exists('WP_User_List_Filter') ){
	class WP_User_List_Filter {

		private static $_this;
		private $is_active = false;
		private $desired_screen = 'users.php';
		private $filters = array();
		private $selected = array();
		private $default;

		function __construct() {
			$this->default = apply_filters( 'wp_user_list_filter_default', array(
				'label' => __('Filter by', 'wp-user-list-filter'),
				'column' => null,
				'items' => array()
				));

			add_action( 'restrict_manage_users', array( $this, 'inline_filters' ) );
			add_filter( 'pre_user_query', array( $this, 'pre_user_query' ) );
		}

		function pre_user_query( $user_query ){
			if( $this->selected_filters() ){

				global $wpdb;

				remove_filter( 'pre_user_query', array( $this, 'pre_user_query' ) );

				$user_id_args  = array(
					'fields' => 'ID',
					'meta_query' => array()
					);

				foreach( $this->selected as $key => $value) {
					$user_id_args['meta_query'][] = array(
						'key' => $this->filters[ $key ]->column,
						'value' => $value
						);
				}

				$user_id_args = apply_filters( 'wp_user_list_filter_user_id_args', $user_id_args );
				$wp_user_query = new WP_User_Query($user_id_args);
				$user_ids = $wp_user_query->get_results();

				if( !empty($user_ids)){
					$user_query->query_vars['include'] = wp_parse_args( (array) $user_ids, (array) $user_query->query_vars['include'] );
					$user_query->query_where .= sprintf( " AND ID IN (%s) ", implode(",", $user_ids) );	
				}

				add_filter( 'pre_user_query', array( $this, 'pre_user_query' ) );

			}
			
			return $user_query;
		}

		function get_filter( $key ){
			return !empty( $this->filters[ $key ] ) ? $this->filters[ $key ] : $this->default;
		}

		function add_filter( $key, $args ){
			$this->filters[ $key ] = apply_filters( 'wp_user_list_filter_add_filter', (object) wp_parse_args( (array) $args, (array) $this->default ) );

			// setup default values for items
			if( empty($this->filters[ $key ]->items) )
				$this->set_items_from_db( $key );
		}

		function set_items_from_db( $key ){
			global $wpdb;
			$items = array();
			$values = $wpdb->get_col( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = '{$this->filters[ $key ]->column}' ORDER BY meta_value DESC;" );
			if( !empty($values)){
				foreach( $values as $value ){
					$value = maybe_unserialize( $value );
					$items[ $value ] = apply_filters( 'wp_user_list_filter_item_from_db', $value, $key, $this->filters[ $key ] );
				}
				$this->filters[ $key ]->items = apply_filters( 'wp_user_list_filter_set_items_from_db', $items );
			}
		}

		function selected_filters(){
			if( !empty($_REQUEST['filterit']) ){
				foreach( $this->filters as $key => $filter ) {
					if( isset( $_REQUEST[ $key ]) && ($_REQUEST[ $key ]==="0"||$_REQUEST[ $key ] ) ){
						$this->selected[ $key ] = apply_filters( 'wp_user_list_filter_selected_filter', $_REQUEST[ $key ], $key );
					}
				}
			}
			$this->selected = apply_filters( 'wp_user_list_filter_selected_filters', $this->selected );
			return count( $this->selected );
		}

		function inline_filters(){

			$filters = '';

			foreach( $this->filters as $key => $filter ) {

				$add_filter = false;
				$filter_html = sprintf('<label class="screen-reader-text" for="new_role">%s</label>',
					$filter->label
					);

				$filter_html .= sprintf('<select name="%s"><option value="">%s</option>', 
					$key,
					$filter->label 
					);

				foreach ( $filter->items as $index => $option ) {
					$add_filter = true;
					$selected = isset( $this->selected[ $key ] ) && $this->selected[ $key ] == $index ? ' selected="selected" ' : '';
					$filter_html .= sprintf('<option value="%s" %s>%s</option>', 
						esc_attr($index), 
						$selected, 
						$option
						);
				}
				
				$filter_html .= '</select>';
				if( $add_filter )
					$filters .= apply_filters( 'wp_user_list_filter_inline_filters', $filter_html, $key, $filter );
			
			}


			if( !empty($filters)){
				// close down "bulk change" and reopen div for filters
				echo '</div><div class="alignleft actions">' . $filters;
				submit_button( __( 'Filter', 'wp-user-list-filter' ), 'button', 'filterit', false );
			}

		}

		/**
		 * Static Singleton Factory Method
		 * 
		 * @return static $_this instance
		 * @readlink http://eamann.com/tech/the-case-for-singletons/
		 */
		public static function instance() {
			if ( !isset( self::$_this ) ) {
				$className = __CLASS__;
				self::$_this = new $className;
			}
			return self::$_this;
		}

	}
}