<?php
/**
* Plugin Name: Saffi
* Description: Saffi (Simple AFFIliation) is a custom plugin made from scratch, that allows sharing and usage of referral links to invite members, and keep track of affiliation points, as well as providing an admin area to review points.
* Version: 0.9.2
* Text Domain: saffi
* License: GPLv2
* License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

// Make sure we don't expose any info if called directly
if( ! defined('ABSPATH')) {
	echo 'Direct access is denied!';
	exit;
}

// Secret Salt (used for point coversion)
define('SAFFI_SALT', 'Eg_y0U73Vb');

// Saffi plugin path
define('SAFFI_PATH', plugin_dir_path( __FILE__ ));

// Check for Invite Code and set in Cookie
function set_saffi_referrer_cookie() {
	if(isset($_GET['invc'])){
		$obf_user_id = $_GET['invc'];
	
		if(is_numeric($obf_user_id) && strpos($obf_user_id, 'ref-') === false){
			$deobf_user_id = (int) $obf_user_id;
		}elseif(strpos($obf_user_id, 'ref-') !== false){
			$obf_user_id = str_replace('ref-','',$obf_user_id);
			$deobf_user_id = (base_convert($obf_user_id, 26, 10) / 8) - 888;
		}
	
		if(isset($deobf_user_id)){
			setcookie("referrer_user_id", intval($deobf_user_id), time()+36000, "/");
		}
	}
}
add_action('init', 'set_saffi_referrer_cookie');

// User Register Hook
add_action('user_register','saffi_registration_hook');

function saffi_registration_hook($user_id)
{
	if( ! isset($_COOKIE['referrer_user_id']) || ! is_numeric($_COOKIE['referrer_user_id'])){
		return false;
	}
	
	$referrer_user_id = intval($_COOKIE['referrer_user_id']);
	setcookie("referrer_user_id", null, -1);
	
	global $wpdb;
	$stats_table   = $wpdb->prefix . "saffi_stats";
	$history_table = $wpdb->prefix . "saffi_history";
	
	// Create row for Referrer on stats table
	// If it exists, simply update total_row_updates
	$sql = "INSERT INTO {$stats_table} (user_id) VALUES ('{$referrer_user_id}') ON DUPLICATE KEY UPDATE total_row_updates = total_row_updates + 1";
	$wpdb->query($sql);
	
	// Get User IP Address
	$user_ip_address = $_SERVER['REMOTE_ADDR'];
	// If behind Cloudflare
	if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])){
	    $user_ip_address = $_SERVER["HTTP_CF_CONNECTING_IP"];
	}
	
	// Store IP?
	if(get_option('saffi_store_user_ip', '0') < 1){
		$user_ip_address = null;
	}
	
	$points_per_signup = (int) get_option('saffi_points_per_signup', 1);
	
	// Insert to Saffi History Table
	$insert_history = $wpdb->insert($history_table,[
		'new_user_id' => $user_id,
		'referrer_id' => $referrer_user_id,
		'ip_address'  => $user_ip_address,
		'points_if_accepted' => $points_per_signup,
	],[
		'%d',
		'%d',
		'%s',
		'%d'
	]);
	
	// If History insert is successful (new_user_id is unique there, so no duplication), update stats
	if($insert_history){
		$sql = "UPDATE {$stats_table} SET pending_points = pending_points + {$points_per_signup}, total_signups = total_signups + 1, total_row_updates = total_row_updates + 1 WHERE user_id = {$referrer_user_id}";
		$wpdb->query($sql);
	}
}


// Install DB tables on plugin activation
register_activation_hook(__FILE__, 'saffi_install');
function saffi_install () {
	require_once SAFFI_PATH . 'includes/db_install.php';
}

// Admin
if(is_admin()) {
	require_once SAFFI_PATH . 'includes/admin.php';
}

// Shortcodes
require_once SAFFI_PATH . 'includes/shortcodes.php';


// Form conversion submitted?
add_action('plugins_loaded', 'saffi_convert_form_submitted_function');
function saffi_convert_form_submitted_function()
{
	if( ! isset($_POST['point_convert_token'],$_POST['points'])){
		return false;
	}
	
	$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	// Get and Check User ID
	$current_user_id = get_current_user_id();
	if( ! $current_user_id || ! is_numeric($current_user_id) || $current_user_id < 2){
		return '';
	}
	
	$cookie_expiry = time() + 5;
	
	global $wpdb;
	$stats_table = $wpdb->prefix . "saffi_stats";
	$convert_request_table = $wpdb->prefix . "saffi_convert_request";
	
	$points_requested = (int) $_POST['points'];
	
	// Minimum convertible points
	$minimum_required_points = get_option('saffi_min_convertible_points', false);
	
	// Get User's Stats
	$user_stats  = $wpdb->get_row("SELECT * FROM {$stats_table} WHERE user_id = {$current_user_id}");
	
	// If Points are less than minimum required, not enough!
	if( ! $user_stats || ! isset($user_stats->remaining_points) || ! is_numeric($user_stats->remaining_points) || ! is_numeric($minimum_required_points) || ! $minimum_required_points || $user_stats->remaining_points < $minimum_required_points){
		$enough_points = false;
	}else{
		$enough_points = true;
	}
	
	// Enough remaining points to convert?
	if($points_requested < $minimum_required_points || $user_stats->remaining_points < $_POST['points']){
		$enough_points = false;
	}
	
	if( ! $enough_points){
		setcookie( "affiliation_points_converted", 'false', $cookie_expiry);
		header('Location: '.$actual_link);
		exit;
	}
	
	if($user_stats && isset($user_stats->remaining_points, $user_stats->updated_at)){
		// Security hash, first string is a secret salt
		$to_hash = SAFFI_SALT . $current_user_id . $user_stats->remaining_points . $user_stats->updated_at;
		$convert_hash = hash('sha256', $to_hash);
	}
	
	if($convert_hash !== $_POST['point_convert_token']){
		setcookie( "affiliation_points_converted", 'false', $cookie_expiry);
		header('Location: '.$actual_link);
		exit;
	}
	
	// Submit Conversion Request
	// IMPORTANT: The following queries run in DB transaction, so either both are done, or neither

    // Begin Transaction
    $wpdb->query('START TRANSACTION');
	// Update User Stats
	$update_stats = $wpdb->query("UPDATE {$stats_table} SET remaining_points = remaining_points - {$points_requested}, coversion_pending_points = coversion_pending_points + {$points_requested} WHERE user_id = {$current_user_id} AND (remaining_points - {$points_requested}) >= 0");
	
	// Insert Request
	$insert_request = $wpdb->query("INSERT INTO {$convert_request_table} (user_id, points) VALUES ({$current_user_id}, {$points_requested})");

	if($insert_request && $update_stats && is_numeric($insert_request) && is_numeric($update_stats) && $insert_request == 1 && $update_stats == 1)
	{
		// Commit DB transaction
		$wpdb->query('COMMIT');
		// Success
		setcookie( "affiliation_points_converted", 'yes', $cookie_expiry);
		header('Location: '.$actual_link);
		exit;
	}
	else
	{
		// Rollback everything!
		$wpdb->query('ROLLBACK');
		
		// Fail
		setcookie( "affiliation_points_converted", 'false', $cookie_expiry);
		header('Location: '.$actual_link);
		exit;
	}
}


// Click-counter for new members (excluding the admin)
add_action('plugins_loaded', 'saffi_click_counter_function');
function saffi_click_counter_function()
{
	global $wpdb;
	
	$current_user_id = get_current_user_id();
	if($current_user_id && is_numeric($current_user_id) && $current_user_id >= 2)
	{
		$history_table = $wpdb->prefix . "saffi_history";
		$stats_table   = $wpdb->prefix . "saffi_stats";
	
		$user = $wpdb->get_row("SELECT * FROM {$history_table} WHERE new_user_id = {$current_user_id} AND accepted = 0");
		
		// Counter Time Limit
		$saffi_counter_time_limit = (int) get_option('saffi_counter_time_limit', '2');
		
		// Last Count
		if($user && isset($user->last_count) && is_numeric($user->last_count)){
			$last_count = $user->last_count;
		}elseif($user){
			$last_count = 1;
		}
		
		// Check Counter Time Limit
		$allow_counter = false;
		if(isset($last_count) && $last_count + $saffi_counter_time_limit <= time()){
			$allow_counter = true;
		}
	
		// If user has history in Saffi, and also not yet accepted, continue
		if($allow_counter && $user){
			$counter = (int) $user->counter;
			$saffi_accept_if_counter = get_option('saffi_accept_if_counter', 20);
		
			$referrer_id = (int) $user->referrer_id;
			// We use this instead of get_option('saffi_points_per_signup') here, because points_per_signup is saved at each row at the time of signup, to make its change later doesn't affect the point calculations
			$points_if_accepted = $user->points_if_accepted;
		
			// If enough counters accept
			if(($counter + 1) >= $saffi_accept_if_counter){
				// IMPORTANT: The following queries run in DB transaction, so either both are done, or neither
			
			    // Begin Transaction
			    $wpdb->query('START TRANSACTION');
				// Accept
				$accept = $wpdb->query("UPDATE {$history_table} SET counter = counter + 1, accepted = 1, accepted_time = now() WHERE new_user_id = {$current_user_id} AND accepted = 0");
				// Update Referrer Stats
				$update_stats = $wpdb->query("UPDATE {$stats_table} SET remaining_points = remaining_points + {$points_if_accepted}, pending_points = pending_points - {$points_if_accepted}, total_accepted = total_accepted + 1 WHERE user_id = {$referrer_id} AND (pending_points - {$points_if_accepted}) >= 0");
			
				if($accept && $update_stats && is_numeric($accept) && is_numeric($update_stats) && $accept == 1 && $update_stats == 1){
					// Commit DB transaction
					$wpdb->query('COMMIT');
				}else{
					// Rollback everything!
					$wpdb->query('ROLLBACK');
				}
				// End of Transaction
			
			}else{
				// Add to counter
				$last_count = time();
				$wpdb->query("UPDATE {$history_table} SET counter = counter + 1, last_count = {$last_count} WHERE new_user_id = {$current_user_id}");
			}
		
		}
	}
}
