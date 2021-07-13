<?php
/* Cowpay callback */
add_action("init","cowpay_callback");
function cowpay_callback() {
	 if (isset($_POST["payment_status"]) ) {
	 	if($_POST["payment_status"] == 'PAID'){
	 		$order_status = (isset($options['order_status'])?esc_html($options['order_status']):"wc-processing");
			$order = wc_get_order($_POST["merchant_reference_id"]);
			$order->update_status($order_status);
			$order->payment_complete();
			$order->add_order_note(esc_html__('Successfully paid via fawry','cowpay'));
	 	}elseif ($_POST["payment_status"] == 'FAILED') {
	 		$order = wc_get_order($_POST["merchant_reference_id"]);
			$order->update_status("wc-cancelled",esc_html__('The order was failed','cowpay'));
			$order->add_order_note(esc_html__('The order was failed','cowpay'));
	 	}

	 	//echo json_encode($_POST["payment_status"]);
	 	echo json_encode(array('status' => $_POST["payment_status"], 'success' => true));
   		wp_die(); 
	}
	
	
	elseif (isset($_GET["action"]) && $_GET["action"] == "cowpay") {
		$data = json_decode(file_get_contents('php://input'), true);

		if (isset($data) && !empty($data) && isset($data["callback_type"])) {
			$options = get_option('cowpay_settings');
			if (isset($options['YOUR_MERCHANT_HASH']) && $options['YOUR_MERCHANT_HASH'] != "" && md5(esc_html($options['YOUR_MERCHANT_HASH']).$data["amount"].$data["cowpay_reference_id"].$data["merchant_reference_id"].$data["order_status"]) === $data["signature"]) {
				if ($data["order_status"] == "PAID") {
					$merchant_reference_id = explode("_", $data["merchant_reference_id"]);
					$order_status = (isset($options['order_status'])?esc_html($options['order_status']):"wc-processing");
					$order = wc_get_order($merchant_reference_id[0]);
					$order->update_status($order_status);
					$order->payment_complete();
					$order->add_order_note(esc_html__('Successfully paid via fawry','cowpay'));
				}else if ( $data["order_status"] == "EXPIRED") {//$data["order_status"] == "cancelled" ||
					$order = wc_get_order($data["merchant_reference_id"]);
					$order->update_status("wc-cancelled",esc_html__('The order was cancelled','cowpay'));
					$order->add_order_note(esc_html__('The order was cancelled','cowpay'));
				}else if ($data["order_status"] == "FAILED") {
					$order = wc_get_order($data["merchant_reference_id"]);
					$order->update_status("wc-cancelled",esc_html__('The order was failed','cowpay'));
					$order->add_order_note(esc_html__('The order was failed','cowpay'));
				}
			}
		}
		echo json_encode(array('status' => $data["order_status"], 'success' => true));
		die();
	}
}?>