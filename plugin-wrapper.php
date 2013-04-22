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
	if( current_user_can( 'list_users' ) ){
		// if user can manage users let's load up filters
		WP_User_List_Filter::instance();
	}
}
add_action( 'admin_init', 'wp_user_list_filter', 1 );
