<?php
/* Cowpay Credit card Payment Gateway Class */
class COWPAY_Credit_Card extends WC_Payment_Gateway_CC
{

	// Setup our Gateway's id, description and other values
	function __construct()
	{

		// The global ID for this Payment method
		$this->id = "cowpay_credit_card";

		// The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
		$this->method_title = esc_html__("Cowpay Credit Card", 'cowpay');

		// The description for this Payment Gateway, shown on the actual Payment options page on the backend
		$this->method_description = esc_html__("Cowpay Credit Card Payment Gateway for WooCommerce", 'cowpay');

		// The title to be used for the vertical tabs that can be ordered top to bottom
		$this->title = esc_html__("Cowpay Credit Card", 'cowpay');

		// If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
		//$this->icon = plugin_dir_url(__FILE__) . 'LOGO.png';  //visa-credit.png
		$this->icon = plugin_dir_url(__FILE__) . '/assest/images/visa-credit.png';

		// Bool. Can be set to true if you want payment fields to show on the checkout 
		// if doing a direct integration, which we are doing in this case
		$this->has_fields = true;

		// Supports the default credit card form
		$this->supports = array('tokenization');

		// This basically defines your settings which are then loaded with init_settings()
		$this->init_form_fields();

		// After init_settings() is called, you can get the settings and load them into variables, e.g:
		// $this->title = $this->get_option( 'title' );
		$this->init_settings();

		// Turn these settings into variables we can use
		foreach ($this->settings as $setting_key => $value) {
			$this->$setting_key = $value;
		}

		// Lets check for SSL
		//add_action( 'admin_notices', array( $this,	'do_ssl_check_cowpay' ) );
		add_action('wp_enqueue_scripts', array($this, 'cowpay_enqueue_scripts'));

		// Save settings
		if (is_admin()) {
			// Versions over 2.0
			// Save our administration options. Since we are not going to be doing anything special
			// we have not defined 'process_admin_options' in this class so the method in the parent
			// class will be used instead
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		}
	} // End __construct()

	// Build the administration fields for this specific Gateway
	public function init_form_fields()
	{
		$this->form_fields = array(
			'enabled' => array(
				'title'		=> esc_html__('Enable / Disable', 'cowpay'),
				'label'		=> esc_html__('Enable this payment gateway', 'cowpay'),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'title' => array(
				'title'		=> esc_html__('Title', 'cowpay'),
				'type'		=> 'text',
				'desc_tip'	=> esc_html__('Payment title the customer will see during the checkout process.', 'cowpay'),
				'default'	=> esc_html__('Credit card', 'cowpay'),
			),
			'description' => array(
				'title'		=> esc_html__('Description', 'cowpay'),
				'type'		=> 'textarea',
				'desc_tip'	=> esc_html__('Payment description the customer will see during the checkout process.', 'cowpay'),
				'default'	=> esc_html__('Pay securely using your credit card.', 'cowpay'),
				'css'		=> 'max-width:350px;'
			),
		);
	}


	// Submit payment and handle response
	public function process_payment($order_id)
	{

		global $woocommerce;

		// Get this Order's information so that we know
		// who to charge and how much
		$customer_order = new WC_Order($order_id);
		$thanks_link    = $this->get_return_url($customer_order);
		$redirect       = add_query_arg('key', $customer_order->order_key, add_query_arg('order', $order_id, $thanks_link));
		$callback_url   = WC()->api_request_url('COWPAY_Credit_Card');
		//	 die;
		// Are we testing right now or is it a real transaction
		$options = get_option('cowpay_settings');
		$environment = (isset($options['environment']) ? esc_html($options['environment']) : 1);
		$url = "https://cowpay.me/api/v1/charge/card";
		if($environment == 1)
			$url = "https://cowpay.me/api/v1/charge/card";
		else
			$url = "https://staging.cowpay.me/api/v1/charge/card";

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



		//echo "<pre>";print_r($_POST);echo "</pre>";
		$lineDetail = json_encode($order_data['line_items']);
		$options = get_option('cowpay_settings');
		$merchant_code = (isset($options['YOUR_MERCHANT_CODE']) ? esc_html($options['YOUR_MERCHANT_CODE']) : "");
		$merchant_reference_id = str_replace("#", "", $customer_order->get_order_number());
		$merchant_reference_id = $merchant_reference_id.'_'.time();
		$customer_merchant_profile_id = ($customer_order->user_id > 0 ? $customer_order->user_id : $merchant_reference_id);
		$payment_method = 'CARD';
		$card_number=str_replace( array(' ', '' ), '', $_POST['cowpay_credit_card-card-number'] );
		//$card_number = '5123456789012346';
		$expiry_year = isset($_POST['cowpay_credit_card-expiry-year']) ? esc_html($_POST['cowpay_credit_card-expiry-year']) : '21';
		$expiry_month = isset($_POST['cowpay_credit_card-expiry-month']) ? esc_html($_POST['cowpay_credit_card-expiry-month']) : '05';
		$cvv = (isset($_POST['cowpay_credit_card-card-cvc'])) ? $_POST['cowpay_credit_card-card-cvc'] : '';
		$save_card = (isset($_POST['cowpay_credit_card_save_card'])) ? $_POST['cowpay_credit_card_save_card'] : '';
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$customer_name = $current_user->user_firstname . ' ' . $current_user->user_lastname;
			$customer_mobile = $customer_order->billing_phone;
			$customer_email = $current_user->user_email;
		} else {
			$customer_name = $customer_order->billing_first_name . ' ' . $customer_order->billing_last_name;
			$customer_mobile = $customer_order->billing_phone;
			$customer_email = $customer_order->billing_email;
		}
		$amount =$customer_order->order_total;
		$currency_code = "EGP";
		$description = (isset($options['description']) ? stripslashes($options['description']) : "Pay securely using your credit card.");
		$charge_items = $lineDetail;
		$secure_hash = esc_html($options['YOUR_MERCHANT_HASH']);
		//$signature= hash('sha256',$merchant_code.$merchant_reference_id.$customer_merchant_profile_id.$payment_method.$amount.$secure_hash);

		$postdata['merchant_code'] = $merchant_code;
		$postdata['merchant_reference_id'] = $merchant_reference_id;
		$postdata['customer_merchant_profile_id'] = $customer_merchant_profile_id;
		$postdata['payment_method'] = $payment_method;
		if (isset($_POST['wc-cowpay_credit_card-payment-token']) && 'new' !== $_POST['wc-cowpay_credit_card-payment-token']) {
			$token_id = wc_clean($_POST['wc-cowpay_credit_card-payment-token']);
			$token = WC_Payment_Tokens::get($token_id);
			$postdata['card_token'] = $token->get_token();
			//print_r($postdata['card_token']);	
		} else {
			$postdata['card_number'] = str_ireplace(" ", "", $card_number);
			$postdata['expiry_year'] = $expiry_year;
			$postdata['expiry_month'] = $expiry_month;
			$postdata['cvv'] = $cvv;
			$postdata['save_card'] = $save_card;
		}
		$postdata['customer_name'] = $customer_name;
		$postdata['customer_mobile'] = $customer_mobile;
		$postdata['customer_email'] = $customer_email;
		$postdata['amount'] = $amount;
		$postdata['currency_code'] = $currency_code;
		$postdata['description'] = $description;
		$postdata['charge_items'] = $charge_items;
		if (isset($postdata['card_token'])) {
			// $signature= hash('sha256',$merchant_code.$merchant_reference_id.$customer_merchant_profile_id.$payment_method.$amount.$postdata['card_token'].$secure_hash);
			//$signature = hash('sha256', $merchant_code . $merchant_reference_id . $customer_merchant_profile_id . $amount . $postdata['card_token'] . $secure_hash);
			$signature = hash('sha256', $merchant_code . $merchant_reference_id . $customer_merchant_profile_id . $amount . $secure_hash);
			$cowurl = "https://cowpay.me/api/fawry/charge-request";
		} else {
			//$signature= hash('sha256',$merchant_code.$merchant_reference_id.$customer_merchant_profile_id.$payment_method.$amount.$secure_hash);
			$signature = hash('sha256', $merchant_code . $merchant_reference_id . $customer_merchant_profile_id . $amount . $secure_hash);
			$cowurl = "https://cowpay.me/api/fawry/charge-request-cc";
		}
		$postdata['signature'] = $signature;

		foreach ($postdata as $key => $value) {
			$post_items[] = $key . '=' . $value;
		}
		//echo $cowurl; die;
		//create the final string to be posted using implode()
		$post_string = implode('&', $post_items);
		



		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,//"https://cowpay.me/api/v1/charge/card",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode(array('merchant_reference_id' => $postdata['merchant_reference_id'], 'customer_merchant_profile_id' => $postdata['customer_merchant_profile_id'], 'card_number' => $postdata['card_number'], 'cvv' => $postdata['cvv'], 'expiry_month' => $postdata['expiry_month'], 'expiry_year' => $postdata['expiry_year'] , 'customer_name' => $postdata['customer_name'], 'customer_email' => $postdata['customer_email'], 'customer_mobile' => $postdata['customer_mobile'], 'amount' => $postdata['amount'], 'signature' => $postdata['signature'], 'description' => $postdata['description'])),
			// CURLOPT_POSTFIELDS => json_encode(array('merchant_reference_id' => '147257', 'customer_merchant_profile_id' => '147257', 'card_number' => '5123456789012346', 'cvv' => '123', 'expiry_month' => '05', 'expiry_year' => '21', 'customer_name' => 'Testing', 'customer_email' => 'dev@cowpay.me', 'customer_mobile' => '+201096545211', 'amount' => '10.00', 'signature' => '4bf26ea9f0f0ae44f09bd795548f0a038933b43b33c1588a59299e8c32fa4c5b', 'description' => 'Charge request description')),
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json",
				"Accept: application/json",
				"Authorization: Bearer ".esc_html($options['YOUR_AUTHORIZATION_TOKEN']),
				//eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImJmNjNkMTZkYmRiZTk4OGFjYWVlMTQ1ODQ2Nzk2NjBkMDA5MWEwMzE5MGQzZjM1YmVhYTQ2ZTQ1MjU5Nzc5M2EzMDA4MWQwN2QwNzY2MjBkIn0.eyJhdWQiOiIzIiwianRpIjoiYmY2M2QxNmRiZGJlOTg4YWNhZWUxNDU4NDY3OTY2MGQwMDkxYTAzMTkwZDNmMzViZWFhNDZlNDUyNTk3NzkzYTMwMDgxZDA3ZDA3NjYyMGQiLCJpYXQiOjE2MDE1NzkzNTIsIm5iZiI6MTYwMTU3OTM1MiwiZXhwIjoxNjMzMTE1MzUyLCJzdWIiOiIxNyIsInNjb3BlcyI6W119.TZF7KtU84HRpcudq8Ey5hOb8IyLVHWb1HCXopqGMpM9Brd_e9yM5Eej4ROrpYz8NHpTpwWVM5wTEWZlgdbN3YQ",
				"Host: cowpay.me",
				"Cookie: laravel_session=1bXJSrULiwnLNA2PbOkornrnNIKwN5f6fraZUCy2"
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);

		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		}


		$response = json_decode($response);
		// var_dump($response);
		//die;
		//wp_die();
		if ($response->status_code == 200) {

			if (isset($response->three_d_secured) && $response->three_d_secured == true) {
				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url($customer_order),
					'otp_check' => true,
					'cowpay_reference_id' => $response->cowpay_reference_id,
					'redirect_callback' => WC()->api_request_url('COWPAY_Credit_Card'),
					'merchant_reference_id' => $postdata['merchant_reference_id'],
				);
			} else {
				// Build the token
				if ($save_card == 1) {
					//  echo $response->card_token;
					$token = new WC_Payment_Token_CC();
					$token->set_token($response->card_token); // Token comes from payment processor
					$token->set_gateway_id('cowpay_credit_card');
					$token->set_last4($response->card_last_four_digits);
					$token->set_expiry_year($expiry_year);
					$token->set_expiry_month($expiry_month);
					$token->set_card_type('visa');
					$token->set_user_id(get_current_user_id());
					// Save the new token to the database
					$token->save();
					//die;
					// Set this token as the users new default token
					WC_Payment_Tokens::set_users_default(get_current_user_id(), $token->get_id());
				}
				$customer_order->add_order_note(__($response->status_description, 'cowpay'));


				// Mark order as Paid
				$customer_order->payment_complete($response->payment_gateway_reference_id);

				// Empty the cart (Very important step)
				$woocommerce->cart->empty_cart();

				// Redirect to thank you page
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url($customer_order),
				);
			}
		} else {
			// Transaction was not succesful
			// Add notice to the cart
			wc_add_notice($response->status_description, 'error');
			// Add note to the order for your reference
			$customer_order->add_order_note('Error:  Failure');
			//$customer_order->update_status("wc-cancelled",esc_html__('The order was failed','cowpay'));
			//echo "<pre>"; print_r($result);
		}
	}


	public function form()
	{
		wp_enqueue_script('wc-credit-card-form');

		$fields = array();

		$year_field = '<select id="' . esc_attr($this->id) . '-expiry-year" name="' . esc_attr($this->id) . '-expiry-year" class="cowpay_feild input-text  wc-credit-card-form-expiry-year" inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" maxlength="2" ' . $this->field_name('expiry-year') . ' style="width:100px">
	    <option value="" disabled="disabled">' . esc_html__("Year", "wpqa") . '</option>';
		for ($i = 0; $i <= 10; $i++) {
			$year_field .= '<option value="' . date('y', strtotime('+' . $i . ' year')) . '">' . date('y', strtotime('+' . $i . ' year')) . '</option>';
		}
		$year_field .= '</select>';


		$cvc_field = '<p class="form-row form-row-last">
			<label for="' . esc_attr($this->id) . '-card-cvc">' . esc_html__('Card code', 'cowpay') . '&nbsp;<span class="required">*</span></label>
			<input  id="' . esc_attr($this->id) . '-card-cvc" name="' . esc_attr($this->id) . '-card-cvc" class="cowpay_feild input-text wc-credit-card-form-card-cvc" inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" maxlength="3" placeholder="' . esc_attr__('CVC', 'cowpay') . '" ' . $this->field_name('card-cvc') . ' style="width:100px" />
		</p>';

		$default_fields = array(
			'card-number-field' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr($this->id) . '-card-number">' . esc_html__('Card number', 'cowpay') . '&nbsp;<span class="required">*</span></label>
				<input  maxlength="22" id="' . esc_attr($this->id) . '-card-number" name="' . esc_attr($this->id) . '-card-number" class="cowpay_feild input-text wc-credit-card-form-card-number" inputmode="numeric" autocomplete="cc-number" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" ' . $this->field_name('card-number') . ' />
			</p>',
			'card-expiry-field' => '<p class="form-row form-row-first">
			<label for="' . esc_attr($this->id) . '-expiry-month">' . esc_html__('Expiry (MM/YY)', 'cowpay') . '&nbsp;<span class="required">*</span></label>
			<select id="' . esc_attr($this->id) . '-expiry-month" name="' . esc_attr($this->id) . '-expiry-month" class="cowpay_feild input-text js_field-country wc-credit-card-form-expiry-month" inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" maxlength="2" ' . $this->field_name('expiry-month') . ' style="width:100px;float:left;">
				<option value="" disabled="disabled">' . esc_html__("Month", "wpqa") . '</option>
				<option value="01">01 - ' . esc_html__("January", "wpqa") . '</option>
				<option value="02">02 - ' . esc_html__("February", "wpqa") . '</option>
				<option value="03">03 - ' . esc_html__("March", "wpqa") . '</option>
				<option value="04">04 - ' . esc_html__("April", "wpqa") . '</option>
				<option value="05">05 - ' . esc_html__("May", "wpqa") . '</option>
				<option value="06">06 - ' . esc_html__("June", "wpqa") . '</option>
				<option value="07">07 - ' . esc_html__("July", "wpqa") . '</option>
				<option value="08">08 - ' . esc_html__("August", "wpqa") . '</option>
				<option value="09">09 - ' . esc_html__("September", "wpqa") . '</option>
				<option value="10">10 - ' . esc_html__("October", "wpqa") . '</option>
				<option value="11">11 - ' . esc_html__("November", "wpqa") . '</option>
				<option value="12">12 - ' . esc_html__("December", "wpqa") . '</option>
			</select>
			' . $year_field . '
		</p>',
		);

		if (!$this->supports('credit_card_form_cvc_on_saved_method')) {
			$default_fields['card-cvc-field'] = $cvc_field;
		}

		$fields = wp_parse_args($fields, apply_filters('woocommerce_credit_card_form_fields', $default_fields, $this->id));
?>
		
		<fieldset id="wc-<?php echo esc_attr($this->id); ?>-cc-form" class='wc-credit-card-form wc-payment-form'>
			<?php do_action('woocommerce_credit_card_form_start', $this->id); ?>
			
			<?php
			foreach ($fields as $field) {
				echo $field; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			}
			?>
			<?php do_action('woocommerce_credit_card_form_end', $this->id); ?>
			<div class="clear"></div>

		</fieldset>
		
		<?php

		if ($this->supports('credit_card_form_cvc_on_saved_method')) {
			echo '<fieldset>' . $cvc_field . '</fieldset>'; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
		}
		?>
		<div id="cowpay-otp-container"></div>
<?php

	}


	public function payment_fields()
	{
		// echo "<p>Pay securely using your credit card.</p>";
		if ($this->supports('tokenization') && is_checkout()) {
			$this->tokenization_script();
			$this->saved_payment_methods();
			$this->form();
			$this->save_payment_method_checkbox();
		} else {
			$this->form();
		}
		/* ?>
   
	<fieldset>
			<p class="form-row form-row-wide">
				<label for="<?php echo $this->id; ?>-name-on-card">Name on Card <span class="required">*</span></label>
				<input style="padding: 8px" id="<?php echo $this->id; ?>-name-on-card" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" class="input-text" type="text" name="cowpay_credit_card_name-on-card">
			</p>						
			<div class="clear"></div>
          
			
			<div class="clear"></div>
		</fieldset>
		<?php
		$this->credit_card_form(); 
	echo '<fieldset><p class="form-row form-row-first card_save_card">
				<label for="cowpay_credit_card_save_card">Save this card for future payments<span class="required">*</span></label>
				<select id="cowpay_credit_card_save_card" class="input-text" name="cowpay_credit_card_save_card">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                  
            </select>
</p></fieldset>';
*/
		echo '<style> .form-row.woocommerce-SavedPaymentMethods-saveNew {
    	display: none !important;}</style>';
	}

	// Validate fields
	public function validate_fields()
	{
		return true;
	}




	// Check if we are forcing SSL on checkout pages
	// Custom function not required by the Gateway
	public function do_ssl_check_cowpay()
	{
		if ($this->enabled == "yes") {
			if (get_option('woocommerce_force_ssl_checkout') == "no") {
				echo "<div class=\"error\"><p>" . sprintf(__("<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>", 'cowpay'), $this->method_title, admin_url('admin.php?page=cowpay_setting')) . "</p></div>";
			}
		}
	}

	/**
	 * register cowpay otp script
	 * method will be fired by wp_enqueue_scripts action
	 * the script registration will ensure that the file will only be loaded once and only when needed
	 * @return void
	 */
	public function cowpay_enqueue_scripts()
	{
		wp_enqueue_script('cowpay_otp_js', 'https://cowpay.me/js/plugins/OTPPaymentPlugin.js');
		wp_enqueue_script('cowpay_js', plugin_dir_url(__FILE__) . '/assest/script/cowpay.js', ['cowpay_otp_js']);

		wp_enqueue_style( 'cowpay_css', plugin_dir_url(__FILE__) . '/assest/css/cowpay.css' );
		
		// Pass ajax_url to cowpay_js
	   wp_localize_script( 'cowpay_js', 'plugin_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
} // End of S