<?php

/**
 * Plugin Name: WordPress User List Filter
 * Plugin URI:
 * Description: A small class to enable advanced sorting and filtering and custom columns for WordPress admin user lists.
 * Version: 1.0
 * Author: Timothy Wood (@codearachnid)
 * Author URI: http://www.codearachnid.com
 * Author Email: tim@imaginesimplicity.com
 * Text Domain: 'wp-user-list-filter' 
 * License:
 * 
 *     Copyright 2013 Imagine Simplicity (tim@imaginesimplicity.com)
 *     License: GNU General Public License v3.0
 *     License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * 
 */

if ( !defined( 'ABSPATH' ) )
	die( '-1' );


/**
 *  Include required files to get this show on the road
 */

require_once 'wp-user-list-filter.php';

function wp_user_list_filter(){
	// check if user can list users & WooCommerce is active
	if( current_user_can( 'list_users' ) && 
		in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
		// load up filters
		$wp_user_list_filter = WP_User_List_Filter::instance();
		$wp_user_list_filter->add_filter('paying_customer', array(
				'label' => __( 'Is paying customer' ),
				'column' => 'paying_customer',
				'items' => array( 1 => 'True', 0 => 'False')
				));

		$wp_user_list_filter->add_filter('completed_orders', array(
				'label' => __( 'Completed orders' ),
				'column' => '_order_count'
				));
	}
}
add_action( 'admin_init', 'wp_user_list_filter', 1 );



function wp_user_list_paying( $value, $key ){
	if( $key == 'paying_customer' ){
		return $value == 1 ? 'True' : 'False';
	} else {
		return $value;
	}
}
add_filter( 'wp_user_list_filter_item_from_db', 'wp_user_list_paying', 10, 2);
