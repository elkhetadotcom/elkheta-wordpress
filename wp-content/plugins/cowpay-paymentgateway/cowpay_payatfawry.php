<?php
/* COWPAY_Payat_Fawry Payment Gateway Class */
class COWPAY_Payat_Fawry extends WC_Payment_Gateway {

	// Setup our Gateway's id, description and other values
	function __construct() {

		// The global ID for this Payment method
		$this->id = "cowpay_payat_fawry";

		// The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
		$this->method_title = esc_html__( "Cowpay Pay at Fawry", 'cowpay' );

		// The description for this Payment Gateway, shown on the actual Payment options page on the backend
		$this->method_description = esc_html__( "Cowpay Pay Using Cash at Fawry Payment Gateway for WooCommerce", 'cowpay' );

		// The title to be used for the vertical tabs that can be ordered top to bottom
		$this->title = esc_html__( "Cowpay Credit Card", 'cowpay' );

		// If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
		//$this->icon = plugin_dir_url(__FILE__) . 'LOGO.png';
		$this->icon = plugin_dir_url(__FILE__) . '/assest/images/fawry-logo.png';
		

		// Bool. Can be set to true if you want payment fields to show on the checkout 
		// if doing a direct integration, which we are doing in this case
		$this->has_fields = true;

		// Supports the default credit card form
		//$this->supports = array( 'default_credit_card_form' );

		// This basically defines your settings which are then loaded with init_settings()
		$this->init_form_fields();

		// After init_settings() is called, you can get the settings and load them into variables, e.g:
		// $this->title = $this->get_option( 'title' );
		$this->init_settings();
		
		// Turn these settings into variables we can use
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}
		
		// Lets check for SSL
		//add_action( 'admin_notices', array( $this,	'do_ssl_check_cowpay' ) );
		
		// Save settings
		if ( is_admin() ) {
			// Versions over 2.0
			// Save our administration options. Since we are not going to be doing anything special
			// we have not defined 'process_admin_options' in this class so the method in the parent
			// class will be used instead
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}		
	} // End __construct()

	// Build the administration fields for this specific Gateway
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'		=> esc_html__( 'Enable / Disable', 'cowpay' ),
				'label'		=> esc_html__( 'Enable this payment gateway', 'cowpay' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'title' => array(
				'title'		=> esc_html__( 'Title', 'cowpay' ),
				'type'		=> 'text',
				'desc_tip'	=> esc_html__( 'Payment title the customer will see during the checkout process.', 'cowpay' ),
				'default'	=> esc_html__( 'Pay at Fawry', 'cowpay' ),
			),
			'description' => array(
				'title'		=> esc_html__( 'Description', 'cowpay' ),
				'type'		=> 'textarea',
				'desc_tip'	=> esc_html__( 'Payment description the customer will see during the checkout process.', 'cowpay' ),
				'default'	=> esc_html__( 'Pay using Fawry reference code.', 'cowpay' ),
				'css'		=> 'max-width:350px;'
			),
		);		
	}
	

	// Submit payment and handle response
	public function process_payment( $order_id ) {
		global $woocommerce;
		
		// Get this Order's information so that we know
		// who to charge and how much
		$customer_order = new WC_Order( $order_id );
		 $thanks_link    = $this->get_return_url($order);
       $redirect       = add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, $thanks_link));
        $callback_url   = WC()->api_request_url('COWPAY_AuthorizeNet_AIM');
//	 die;
		// Are we testing right now or is it a real transaction
		$options = get_option('cowpay_settings');
		$environment = (isset($options['environment'])?esc_html($options['environment']): 1);
		$url = "https://cowpay.me/api/fawry/charge-request";
		if($environment == 1)
			$url = "https://cowpay.me/api/fawry/charge-request";//"https://cowpay.me/api/fawry/charge-request"
		else
			$url = "https://staging.cowpay.me/api/fawry/charge-request";
				//getting all line items
        foreach ($customer_order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $product_id = null;
            $product_sku = null;
            // Check if the product exists.
            if (is_object($product)) {
                $product_id = $product->get_id();
                $product_sku = $product->get_sku();
            }
            $order_data['line_items'][] = array(
                'itemId' => $item_id,
                'description' => get_the_title($product_id),
                'price' => round($customer_order->get_item_total($item, true, true), 2),
                'quantity' => wc_stock_amount($item['qty'])
            );
        }

$lineDetail= json_encode($order_data['line_items']);

$time= explode('/', $_POST['cowpay_authorizenet_aim-card-expiry']);
$options = get_option('cowpay_settings');
$merchant_code = (isset($options['YOUR_MERCHANT_CODE'])?esc_html($options['YOUR_MERCHANT_CODE']):"");
$merchant_reference_id=str_replace( "#", "", $customer_order->get_order_number() );
$merchant_reference_id = $merchant_reference_id.'_'.time();
$customer_merchant_profile_id=($customer_order->user_id > 0?$customer_order->user_id:$merchant_reference_id);
$payment_method='PAYATFAWRY';
if ( is_user_logged_in() ) {
    $current_user = wp_get_current_user();
$customer_name= $current_user->user_firstname .' '.$current_user->user_lastname;
$customer_mobile=$customer_order->billing_phone;
$customer_email=$current_user->user_email;
} else {
$customer_name= $customer_order->billing_first_name .' '.$customer_order->billing_last_name;
$customer_mobile=$customer_order->billing_phone;
$customer_email=$customer_order->billing_email;
}

$amount=$customer_order->order_total;
$currency_code="EGP";
$description = (isset($options['description'])?stripslashes($options['description']):"Pay using Fawry reference code");
$charge_items=$lineDetail;
$secure_hash=esc_html($options['YOUR_MERCHANT_HASH']);
$signature= hash('sha256',$merchant_code.$merchant_reference_id.$customer_merchant_profile_id.$payment_method.$amount.$secure_hash);
	
$postdata['merchant_code'] = $merchant_code;
$postdata['merchant_reference_id']= $merchant_reference_id;
$postdata['customer_merchant_profile_id']= $customer_merchant_profile_id;
$postdata['payment_method']= $payment_method;
//$postdata['card_number']= $card_number;
//$postdata['expiry_year']= $expiry_year;
//$postdata['expiry_month']= $expiry_month;
//$postdata['cvv']= $cvv;
//$postdata['save_card']= $save_card;
$postdata['customer_name']= $customer_name;
$postdata['customer_mobile']= $customer_mobile;
$postdata['customer_email']= $customer_email;
$postdata['amount']= $amount;
$postdata['currency_code']= $currency_code;
$postdata['description']= $description;
$postdata['charge_items']= $charge_items;
$postdata['signature']= $signature;

foreach ( $postdata as $key => $value) {
    $post_items[] = $key . '=' . $value;
}

//create the final string to be posted using implode()
$post_string = implode ('&', $post_items);
//$postdata= json_encode($postdata);
//echo $post_string;
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $url,//"https://cowpay.me/api/fawry/charge-request",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 300000,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $post_string,
  CURLOPT_HTTPHEADER => array(
    "Accept: */*",
    "Cache-Control: no-cache",
    "Connection: keep-alive",
    "Content-Type: application/x-www-form-urlencoded",
    "Host: cowpay.me",
    "Postman-Token: 7cc9e691-4c58-445a-9e07-0abf3008a8a4,b8ee01e7-6df2-4136-b76b-8784c5c81cf1",
    "User-Agent: PostmanRuntime/7.13.0",
    "accept-encoding: gzip, deflate",
    "cache-control: no-cache",
    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
    "cookie: laravel_session=Y0QJWNFcprRZivfiYtHt76nv4heVotXwRefrmZ6g"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
//echo "cURL Error #:" . $err;
} else {
 $response;
} 
//echo "dnjnds";
$response=json_decode($response);

if ($response->status_code==200) {
  // Handle success case
  $customer_order->add_order_note( __( $response->status_description, 'cowpay' ) );
			// Mark order as Paid
  			//$order_status = (isset($options['order_status'])?esc_html($options['order_status']):"wc-processing");
  			//$customer_order->update_status($order_status);
			//$customer_order->payment_complete($response->payment_gateway_reference_id);
  
			update_post_meta($order_id, '_transaction_id',$response->payment_gateway_reference_id);

			// Empty the cart (Very important step)
			$woocommerce->cart->empty_cart();
			// Redirect to thank you page
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $customer_order ),
			);
}
else {
  // Transaction was not succesful
			// Add notice to the cart
			wc_add_notice( $response->status_description, 'error' );
			// Add note to the order for your reference
			$customer_order->add_order_note( 'Error:  Failure' );
			//$customer_order->update_status("wc-cancelled",esc_html__('The order was failed','cowpay'));
 //echo "<pre>"; print_r($result);
}
		

	} 
	
	
	// Check if we are forcing SSL on checkout pages
	// Custom function not required by the Gateway
	public function do_ssl_check_cowpay() {
		if( $this->enabled == "yes" ) {
			if( get_option( 'woocommerce_force_ssl_checkout' ) == "no" ) {
				echo "<div class=\"error\"><p>". sprintf( __( "<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>", 'cowpay' ), $this->method_title, admin_url( 'admin.php?page=cowpay_setting' ) ) ."</p></div>";	
			}
		}		
	}

} // End of S