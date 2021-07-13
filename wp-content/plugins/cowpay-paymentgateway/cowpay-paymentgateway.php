<?php
/*
* Plugin Name: Cowpay - WooCommerce Gateway
* Plugin URI: https://cowpay.me
* Description: Extends WooCommerce by Adding the Cowpay payemnt Gateway.
* Version: 12.0
* Author:       Cowpay
* Author URI:   https://cowpay.me
*
* Text Domain: cowpay
* Domain Path: /languages/
*/

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action( 'plugins_loaded', 'cowpay_payment_init', 0 );
function cowpay_payment_init() {
	load_plugin_textdomain('cowpay',false,dirname(plugin_basename(__FILE__)).'/languages/');
	// If the parent WC_Payment_Gateway class doesn't exist
	// it means WooCommerce is not installed on the site
	// so do nothing
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
	
	// If we made it this far, then include our Gateway Class
	include_once( 'cowpay_setting.php' );
	include_once( 'cowpay_callback.php' );
	include_once( 'cowpay_creditcard.php' );
    include_once( 'cowpay_payatfawry.php' );
	// Now that we have successfully included our class,
	// Lets add it too WooCommerce
	add_filter( 'woocommerce_payment_gateways', 'cowpay_add_payment_gateway' );
	function cowpay_add_payment_gateway( $methods ) {
		$methods[] = 'COWPAY_Credit_Card';
		$methods[] = 'COWPAY_Payat_Fawry';
		return $methods;
	}
}

// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'cowpay_payment_action_links' );
function cowpay_payment_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=cowpay_setting' ) . '">' . esc_html__( 'Settings', 'cowpay' ) . '</a>',
	);

	// Merge our new link with the default ones
	return array_merge( $plugin_links, $links );	
}
function filter_woocommerce_thankyou_order_received_text( $var, $order ) { 
    // make filter magic happen here... 
    global $wp,$wp_query;
    // Get the order ID
    $order_id  = absint( $wp->query_vars['order-received'] );
    $order = new WC_Order( $order_id );
    $transaction_id = $order->get_transaction_id();
    $payment_method = $order->get_payment_method();
    //print_r($payment_method);
    if ($payment_method == 'cowpay_payat_fawry'){
    	$var .= esc_html__('You can pay cash at Fawry using the following transaction number:','cowpay').' '.$transaction_id;
    }
    return $var; 
}; 
         
// add the filter 
add_filter( 'woocommerce_thankyou_order_received_text', 'filter_woocommerce_thankyou_order_received_text', 10, 2 ); 


/**
*  Add Custom Icon 
*/ 

// function custom_gateway_icon( $icon, $id ) {
//         return '<img src="' . plugin_dir_url(__FILE__) . 'LOGO.png' . '" > '; 
// }
// add_filter( 'woocommerce_gateway_icon', 'custom_gateway_icon', 10, 2 );