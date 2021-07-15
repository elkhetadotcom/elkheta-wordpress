<?php
/* Cowpay setting */

function cowpay_add_admin_page_setting() {
	$vpanel_page = add_menu_page(esc_html__('Cowpay Setting','cowpay'),esc_html__('Cowpay Setting','cowpay'),'manage_options','cowpay_setting','cowpay_setting','dashicons-cart');
	add_submenu_page('cowpay_setting',esc_html__('Cowpay Setting','cowpay'),esc_html__('Cowpay Setting','cowpay'),'manage_options','cowpay_setting','cowpay_setting');
	add_submenu_page('cowpay_setting',esc_html__('Credit Card','cowpay'),esc_html__('Credit Card','cowpay'),'manage_options','cowpay_credit','cowpay_credit');
	add_submenu_page('cowpay_setting',esc_html__('Pay at Fawry','cowpay'),esc_html__('Pay at Fawry','cowpay'),'manage_options','cowpay_fawry','cowpay_fawry');
	
}
add_action('admin_menu', 'cowpay_add_admin_page_setting');
function cowpay_setting () {?>
	<form action='options.php' method='post'>
		
	<?php settings_fields('cowpay');
	do_settings_sections('cowpay');
	submit_button();?>
	</form>
<?php }
function cowpay_credit () {
	wp_safe_redirect(admin_url("admin.php?page=wc-settings&tab=checkout&section=cowpay_credit_card"));
	die();
}
function cowpay_fawry () {
	wp_safe_redirect(admin_url("admin.php?page=wc-settings&tab=checkout&section=cowpay_payat_fawry"));
	die();
}
add_action( 'admin_init', 'cowpay_settings_init' );
function cowpay_settings_init(  ) {
    register_setting('cowpay','cowpay_settings');
    add_settings_section('cowpay_section_settings',esc_html__('Cowpay Setting','cowpay'),'','cowpay');
    //add_settings_field('COWPAY_API_URL',esc_html__('Cowpay api url','cowpay'),'COWPAY_API_URL','cowpay','cowpay_section_settings');
    add_settings_field('YOUR_MERCHANT_CODE',esc_html__('Merchant Code','cowpay'),'YOUR_MERCHANT_CODE','cowpay','cowpay_section_settings');
    add_settings_field('YOUR_MERCHANT_HASH',esc_html__('Merchant Hash','cowpay'),'YOUR_MERCHANT_HASH','cowpay','cowpay_section_settings');
    add_settings_field('YOUR_AUTHORIZATION_TOKEN',esc_html__('Authorization Token','cowpay'),'YOUR_AUTHORIZATION_TOKEN','cowpay','cowpay_section_settings');
    add_settings_field('description',esc_html__('Description','cowpay'),'description','cowpay','cowpay_section_settings');
    add_settings_field('cowpay_callbackurl',esc_html__('Callback URL','cowpay'),'cowpay_callbackurl','cowpay','cowpay_section_settings');
    //add_settings_field('environment',esc_html__('Test mode','cowpay'),'environment','cowpay','cowpay_section_settings');
    add_settings_field('order_status',esc_html__('Order Status','cowpay'),'order_status','cowpay','cowpay_section_settings');

    add_settings_field('environment',esc_html__('Environment','cowpay'),'environment','cowpay','cowpay_section_settings',array( 'value' => 1 ));


}
function COWPAY_API_URL() {
	$options = get_option('cowpay_settings');?>
	<input type='text' name='cowpay_settings[COWPAY_API_URL]' value='<?php echo esc_url($options['COWPAY_API_URL'])?>'>
	<p><?php esc_html_e('This is the Client Code provided by Cowpay when you signed up for an account.','cowpay')?></p>
<?php }
function YOUR_MERCHANT_CODE() {
	$options = get_option('cowpay_settings');?>
	<input type='text' name='cowpay_settings[YOUR_MERCHANT_CODE]' value='<?php echo esc_html($options['YOUR_MERCHANT_CODE'])?>'>
	<p><?php esc_html_e('This is the MERCHANT CODE provided by Cowpay when you signed up for an account.(TerminialIdentifier codes).','cowpay')?></p>
<?php }
function YOUR_MERCHANT_HASH() {
	$options = get_option('cowpay_settings');?>
	<input type='text' name='cowpay_settings[YOUR_MERCHANT_HASH]' value='<?php echo esc_html($options['YOUR_MERCHANT_HASH'])?>'>
	<p><?php esc_html_e('This is the MERCHANT HASH provided by Cowpay when you signed up for an account.','cowpay')?></p>
<?php }
function YOUR_AUTHORIZATION_TOKEN() {
	$options = get_option('cowpay_settings');?>
	<input type='text' name='cowpay_settings[YOUR_AUTHORIZATION_TOKEN]' value='<?php echo esc_html($options['YOUR_AUTHORIZATION_TOKEN'])?>'>
	<p><?php esc_html_e('This is the AUTHORIZATION TOKEN provided by Cowpay when you signed up for an account.','cowpay')?></p>
<?php }
function description() {
	$options = get_option('cowpay_settings');?>
	<textarea name='cowpay_settings[description]'><?php echo (isset($options['description'])?stripslashes($options['description']):'')?></textarea>
	<p><?php esc_html_e('This is your description.','cowpay')?></p>
<?php }
function cowpay_callbackurl() {
	$options = get_option('cowpay_settings');?>
	<input type='text' name='cowpay_settings[cowpay_callbackurl]' value='<?php echo esc_url($options['cowpay_callbackurl'] != ""?$options['cowpay_callbackurl']:add_query_arg('action','cowpay',home_url('/')))?>'>
	<p><?php esc_html_e('Please modify "Callback URL" in your Cowpay.me account to:','cowpay')?></p>
<?php }
function environment() {
	$options = get_option('cowpay_settings');?>
	<input id="production" type='radio' name='cowpay_settings[environment]' value='1'  <?php checked($options['environment'],1)?>/><label for="production">Production</label>
	<input id="staging" type='radio' name='cowpay_settings[environment]' value='2' <?php checked($options['environment'],2)?>  /><label for="staging">Staging</label>
	<p><?php esc_html_e('Choose enviroment for paymeny getway','cowpay')?></p>
<?php }
function order_status() {
	$options = get_option('cowpay_settings');?>
	<select name='cowpay_settings[order_status]'>
		<option value='wc-processing' <?php selected($options['order_status'],"wc-processing")?>><?php esc_html_e("Processing","cowpay")?></option>
		<option value='wc-completed' <?php selected($options['order_status'],"wc-completed")?>><?php esc_html_e("Completed","cowpay")?></option>
	</select>
	<p><?php esc_html_e('Choose the order status when the order paid.','cowpay')?></p>
<?php }?>