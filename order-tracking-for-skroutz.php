<?php

/***
Plugin Name: Order Tracking for Skroutz
Plugin URI: https://frontseries.gr/contact/
Description: Track your WooCommerce Orders that Coming from Skroutz
Version: 1.0.0
Author: Front Series
Author URI: https://frontseries.gr/
WC requires at least: 4.0
WC tested up to: 5.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
***/

if( !defined('ABSPATH') ) {
	die( 'You are not allowed to call this page directly.' );
}

// Defines
define( 'SKROUTZ_ORDER_TRACKING__VERSION', '1.0.0' );
define( 'SKROUTZ_ORDER_TRACKING__BASENAME', plugin_basename(dirname(__FILE__)) );
define( 'SKROUTZ_ORDER_TRACKING__DIR', plugin_dir_path(__FILE__) );

// Actions
add_action('woocommerce_checkout_create_order', 'fs_skroutz_order_tracking__meta', 20, 2);
add_action( 'manage_shop_order_posts_custom_column', 'fs_skroutz_order_tracking__list', 2 );
add_action( 'woocommerce_admin_order_data_after_billing_address', 'fs_skroutz_order_tracking__notice', 10, 1 );
add_action( 'restrict_manage_posts', 'fs_skroutz_order_tracking__filter', 50 );

if (is_admin()) {
	add_filter( 'parse_query', 'fs_skroutz_order_tracking__filter_action');
}

// Functions

/**
 * Listen for Skroutz Cookie and Set Meta Data
 * @param  [object] [$order]
 * @param  [object] [$data]
 * @since  1.0.0
 */
function fs_skroutz_order_tracking__meta( $order, $data ) {

	if( isset( $_COOKIE['__skr_nltcs_ss'] ) || isset( $_COOKIE['__skr_nltcs_mt'] ) ) {
		$order->update_meta_data( 'fs_skroutz_tracking', 1 );
	}
    
}


/**
 * Add Skroutz Tag in WooCommerce Orders List
 * @param  [string] $column
 * @since  1.0.0
 */
function fs_skroutz_order_tracking__list( $column ) {

    global $post, $the_order;

    if ( empty( $the_order ) || $the_order->get_id() !== $post->ID ) {
        $the_order = wc_get_order( $post->ID );
    }

    if ( empty( $the_order ) ) {
        return;
    }

    $order=wc_get_order( $the_order->get_id() );
    $is_skroutz = $order->get_meta( 'fs_skroutz_tracking' );

    if ( !empty($is_skroutz) && $column == 'order_number' ) {
    	echo '<img src="' . plugin_dir_url(__FILE__) . 'assets/img/skroutzsc_orange.png'.'" style="position: absolute;transform: translate(-10px, -10px);">';
    }
}


/**
 * Add Skroutz Tag in WooCommerce Order Details
 * @param  [object] $order
 * @since  1.0.0
 */
function fs_skroutz_order_tracking__notice( $order ){

    $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
    $is_skroutz = get_post_meta( $order_id, 'fs_skroutz_tracking', true );

    if ( !empty($is_skroutz) ) {
    	echo '<img src="' . plugin_dir_url(__FILE__) . 'assets/img/header_image.png'.'">';
    }

}


if (is_admin()) {

	/**
	 * Add Skroutz Filter in WooCommerce Orders List
	 * @since 1.0.0
	 */
	function fs_skroutz_order_tracking__filter() {

		global $typenow;

		if ( 'shop_order' != $typenow ) {
			return;
		}

		$getData = sanitize_text_field( $_GET['fs_skroutz_order_tracking__filter'] );

		?>

		<select name='fs_skroutz_order_tracking__filter' id='fs_skroutz_order_tracking__filter'>
			<option
			<?php
			if ( !isset( $getData ) && !$getData ) {
				selected( 'no', '' );
			}
			?>
			value="no"><?php esc_html_e( 'Skroutz Orders', 'fs_skroutz_order_tracking__filter_orders' ); ?></option>
			<option <?php
			        if ( isset( $getData ) && $getData ) {
				        selected( 'yes', $getData );
			        }
			        ?>value="yes"><?php esc_html_e( __('Yes'), 'fs_skroutz_order_tracking__filter_orders' ); ?></option>
		</select>

		<?php
	}


	/**
	 * Listen for Skroutz Filter Action
	 * @since 1.0.0
	 */
	function fs_skroutz_order_tracking__filter_action( $query ) {

		global $pagenow;

		$postClear = sanitize_text_field( $_GET['post_type'] );
		$getData = sanitize_text_field( $_GET['fs_skroutz_order_tracking__filter'] );

		$post_type = isset( $postClear ) ? $postClear : '';

		if ( $post_type == 'shop_order' && isset( $getData ) && $getData =='yes' ) {
			$query->query_vars['meta_key'] = 'fs_skroutz_tracking';
			$query->query_vars['meta_value'] = 1;
			$query->query_vars['meta_compare'] = '=';
		}

	}

}