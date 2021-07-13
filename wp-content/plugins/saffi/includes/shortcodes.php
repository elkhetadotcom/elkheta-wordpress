<?php
// Make sure we don't expose any info if called directly
if( ! defined('SAFFI_PATH')) {
	echo 'Direct access is denied!';
	exit;
}

// Helper Functions
function saffi_return_current_user_ref_link()
{
	$user_id = get_current_user_id();
	if( ! $user_id) return '';
	
	// Obf?
	$do_obf = (int) get_option('saffi_obfuscate_user_id', 1);
	if($do_obf > 0){
		$invite_code = ($user_id + 888) * 8;
		$invite_code = base_convert($invite_code, 10, 26);
		$invite_code = 'ref-' . $invite_code;
	}else{
		$invite_code = $user_id;
	}
	
	$site_url = site_url();
	$ref_base_default = add_query_arg('invc', $invite_code, $site_url);
	
	$saved_ref_base = get_option('saffi_referral_url_base');
	
	// If saved ref_base is valid URL, return it, otherwise return the site_url() with added invc
	if($saved_ref_base && strlen($saved_ref_base) >= 6 && filter_var($saved_ref_base, FILTER_VALIDATE_URL) !== false){
		return $saved_ref_base . "?invc={$invite_code}";
	}else{
		return $ref_base_default;
	}
}


// ShortCode Functions
function saffi_user_ref_link_function()
{
	return saffi_return_current_user_ref_link();
}

function saffi_user_withdraw_form_function($atts)
{
	// Get and Check User ID
	$current_user_id = get_current_user_id();
	if( ! $current_user_id || ! is_numeric($current_user_id) || $current_user_id < 2){
		return '';
	}
	
	global $wpdb;
	
	// Minimum convertible points
	$minimum_required_points = get_option('saffi_min_convertible_points', false);
	
	// Get User's Stats
	$stats_table = $wpdb->prefix . "saffi_stats";
	$user_stats  = $wpdb->get_row("SELECT * FROM {$stats_table} WHERE user_id = {$current_user_id}");
	
	// Get/Set Shortcode Attributes
	extract(shortcode_atts(array(
		  'input_placeholder' => 'Amount of points to convert',
	      'button_name' => 'Convert Pending Points to Earned Points',
		  'not_enough_points_message' => 'You do not have enough points to convert!',
		  'success_message' => 'Your pending points are successfully converted to earned points!',
		  'fail_message' => 'There was an error while converting your points, please contact us!'
	), $atts));
	
	// If Points are less than minimum required, not enough!
	if( ! $user_stats || ! isset($user_stats->remaining_points) || ! is_numeric($user_stats->remaining_points) || ! is_numeric($minimum_required_points) || ! $minimum_required_points || $user_stats->remaining_points < $minimum_required_points){
		$enough_points = false;
	}else{
		$enough_points = true;
	}
	
	// Check if form handled, show message
	if(isset($_COOKIE['affiliation_points_converted'])){
		$saffi_form_handled_cookie_value = $_COOKIE['affiliation_points_converted'];
		
		if($saffi_form_handled_cookie_value === 'yes'){
			return "<div class='success point_convert_success'>{$success_message}</div>";
		}else{
			return "<div class='error point_convert_fail'>{$fail_message}</div>";
		}
	}
	
	if($user_stats && isset($user_stats->remaining_points, $user_stats->updated_at)){
		// Security hash, first string is a secret salt
		$to_hash = SAFFI_SALT . $current_user_id . $user_stats->remaining_points . $user_stats->updated_at;
		$convert_hash = hash('sha256', $to_hash);
	}
	
	$output = "<form method='POST' id='point_conversion_form'>";
	
	if($enough_points && isset($convert_hash))
	{
		// There are enough points!
		$output .= "<input type='hidden' name='point_convert_token' value='{$convert_hash}'>";
		// Number Input
		$output .= "<input class='input point_amount_input' type='number' placeholder='{$input_placeholder}' name='points' min='{$minimum_required_points}' max='{$user_stats->remaining_points}' step='1'>";
		// Submit button
		$output .= "<button type='submit' class='button button-primary point_convert_submit_button'>{$button_name}</button>";
	}
	else
	{
		// Not enough points message
		$output .= "<p class='not_enough_points'>{$not_enough_points_message}</p>";
		// Disabled Button
		$output .= "<br><button type='button' class='button button-primary point_convert_submit_button not_enough_points_button' disabled='disabled'>{$button_name}</button>";
	}
	
	$output .= "</form>";
	
	return $output;
}



function saffi_user_stats_function($atts)
{
	// Get and Check User ID
	$current_user_id = get_current_user_id();
	if( ! $current_user_id || ! is_numeric($current_user_id) || $current_user_id < 2){
		return '';
	}
	
	$accepted_atts = ['earned_points','pending_points','remaining_points','coversion_pending_points','total_signups','total_accepted'];
	
	global $wpdb;
	$stats_table = $wpdb->prefix . "saffi_stats";
	
	// Get/Set Shortcode Attributes
	extract(shortcode_atts(array(
		  'show' => '',
	), $atts));
	
	if( ! in_array($show, $accepted_atts)){
		$show = 'earned_points';
	}
	
	$get_user_stats = $wpdb->get_row("SELECT * FROM {$stats_table} WHERE user_id = {$current_user_id}");
	
	if($get_user_stats && isset($get_user_stats->{$show})){
		return $get_user_stats->{$show};
	}else{
		return '0';
	}
	
	return $user_stats[$show];
}


function saffi_user_stats_row_function($atts)
{
	// Get Stat
	$stat = saffi_user_stats_function($atts);
	
	if( ! is_numeric($stat)){
		return '';
	}
	
	// Get/Set Shortcode Attributes
	extract(shortcode_atts(array(
		  'show' => '',
		  'title' => ''
	), $atts));
	
	$output = "<table class='table' class='affiliation_stats_single_row_table'><tbody><tr>";
	
	$output .= "<tr>";
	    $output .= "<th>{$title}</th><td>{$stat}</td>";
	$output .= "</tr>";
	
	$output .= "</table>";
	
	return $output;
}


function saffi_user_affiliate_stats_table_function($atts)
{
	// Get and Check User ID
	$current_user_id = get_current_user_id();
	if( ! $current_user_id || ! is_numeric($current_user_id) || $current_user_id < 2){
		return '';
	}
	
	global $wpdb;
	$stats_table = $wpdb->prefix . "saffi_stats";
	
	// Get/Set Shortcode Attributes
	extract(shortcode_atts(array(
		  'coversion_pending_points' => 'Requested for conversion',
		  'earned_points' => 'Points Earned',
		  'pending_points' => 'Pending Points',
		  'remaining_points' => 'Remaining Points',
		  'total_signups' => 'Total Signups',
		  'total_accepted' => 'Total Accepted Signups'
	), $atts));
	
	$get_user_stats = $wpdb->get_row("SELECT * FROM {$stats_table} WHERE user_id = {$current_user_id}");
	
	$output = '';
	
	if($get_user_stats && isset($get_user_stats->user_id)){
		$user_stats['earned_points']            = $get_user_stats->earned_points;
		$user_stats['pending_points']           = $get_user_stats->pending_points;
		$user_stats['remaining_points']         = $get_user_stats->remaining_points;
		$user_stats['coversion_pending_points'] = $get_user_stats->coversion_pending_points;
		$user_stats['total_signups']            = $get_user_stats->total_signups;
		$user_stats['total_accepted']           = $get_user_stats->total_accepted;
	}else{
		$user_stats['earned_points']            = 0;
		$user_stats['pending_points']           = 0;
		$user_stats['remaining_points']         = 0;
		$user_stats['coversion_pending_points'] = 0;
		$user_stats['total_signups']            = 0;
		$user_stats['total_accepted']           = 0;
	}
	
	$output .= "<table class='table' id='affiliation_stats_table'><tbody><tr>";
	
	$output .= "<tr>";
	    $output .= "<th>{$earned_points}</th><td>{$user_stats['earned_points']}</td>";
	$output .= "</tr>";
		
	$output .= "<tr>";
	    $output .= "<th>{$pending_points}</th><td>{$user_stats['pending_points']}</td>";
	$output .= "</tr>";
		
	$output .= "<tr>";
	    $output .= "<th>{$remaining_points}</th><td>{$user_stats['remaining_points']}</td>";
	$output .= "</tr>";
		
	$output .= "<tr>";
	    $output .= "<th>{$coversion_pending_points}</th><td>{$user_stats['coversion_pending_points']}</td>";
	$output .= "</tr>";
		
	$output .= "<tr>";
	    $output .= "<th>{$total_signups}</th><td>{$user_stats['total_signups']}</td>";
	$output .= "</tr>";
		
	$output .= "<tr>";
	    $output .= "<th>{$total_accepted}</th><td>{$user_stats['total_accepted']}</td>";
	$output .= "</tr>";

	$output .= "</table>";
	
	return $output;
}


// Init Short Codes
function register_shortcodes(){
   add_shortcode('saffi_user_ref_link', 'saffi_user_ref_link_function');
   add_shortcode('saffi_user_withdraw_form', 'saffi_user_withdraw_form_function');
   add_shortcode('saffi_user_affiliate_stats_table', 'saffi_user_affiliate_stats_table_function');
   add_shortcode('saffi_user_stats', 'saffi_user_stats_function');
   add_shortcode('saffi_user_stats_row', 'saffi_user_stats_row_function');
}

add_action( 'init', 'register_shortcodes');
