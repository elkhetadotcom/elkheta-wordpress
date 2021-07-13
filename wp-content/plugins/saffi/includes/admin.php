<?php
// Make sure we don't expose any info if called directly
if( ! defined('SAFFI_PATH')) {
	echo 'Direct access is denied!';
	exit;
}

// Admin Menu Links
add_action('admin_menu', 'saffi_admin_menu');
function saffi_admin_menu() {
	add_menu_page( 'Saffi', 'Saffi', 'promote_users', 'saffi_main', 'saffi_admin_pages_main', 'dashicons-rest-api', 25);
	add_submenu_page('saffi_main', 'Saffi - User Stats', 'User Stats', 'promote_users', 'saffi_user_stats', 'saffi_admin_pages_user_stats' );
	add_submenu_page('saffi_main', 'Saffi - Settings', 'Settings', 'promote_users', 'saffi_settings', 'saffi_admin_pages_settings' );
	add_submenu_page('saffi_main', 'Saffi - Requests', 'Requests', 'promote_users', 'saffi_requests', 'saffi_admin_pages_requests' );
	add_submenu_page('saffi_main', 'Saffi - Guide', 'Guide', 'promote_users', 'saffi_guide', 'saffi_admin_pages_guide' );

	add_submenu_page(null, 'Saffi - User History', 'User History', 'promote_users', 'saffi_user_history', 'saffi_admin_pages_user_history' );
}


// Add Saffi history link to Users table in Admin
function saffi_user_list_affiliate_history_link( $actions, $user_object ) {
    if (current_user_can('promote_users', $user_object->ID)){
		$result_link = menu_page_url('saffi_user_history', false) . '&user_id=' . $user_object->ID;
		
    	$actions['affiliate_history'] = "<a href='{$result_link}'>Saffi</a>";
    }
    return $actions;
}
add_filter('user_row_actions', 'saffi_user_list_affiliate_history_link', 10, 2);


/////////////////
/// Admin Pages
/////////////////

// Saffi Main Page
function saffi_admin_pages_main()
{
	echo "<h1>Saffi - Overview</h1>";

	global $wpdb;
	$stats_table = $wpdb->prefix . "saffi_stats";

	$query = "SELECT 
			count(user_id) AS sum_active_referrers,
			sum(earned_points) AS sum_earned_points,
			sum(pending_points) AS sum_pending_points,
			sum(remaining_points) AS sum_remaining_points,
			sum(total_signups) AS sum_total_signups,
			sum(total_accepted) AS sum_total_accepted
		FROM {$stats_table} WHERE total_signups > 0";
	$sums = $wpdb->get_row($query);

	if( ! $sums){
		echo "<div style='color:red;font-weight:bold;'>Something went wrong! are you sure the plugin is installed and activated correctly?</div>";
	}elseif($sums->sum_active_referrers < 1){
		echo "<div class='row'><div class='card'>
				<h2 class='title'>No affiliations yet!</h2>
				<p>Stats will appear here as soon as there are at least one user who has joined through affiliation!</p>
			</div></div>";
	}
	else{
		echo "<div class='cards'>";
		echo "<style>.saffi-overview-stats{color:#1dae57;float:right;}</style>";
		echo "<div class='card'>
				<strong>Users</strong>
				<hr>
				Total Users with Active Referrals: <strong class='saffi-overview-stats'>{$sums->sum_active_referrers}</strong>
			</div>";
			echo "<div class='card'>
				<strong>Points Overview</strong>
				<hr>
				Sum of currently available <strong>Earned Points</strong> by all users: <strong class='saffi-overview-stats'>{$sums->sum_earned_points}</strong>
				<hr>
				Sum of currently available <strong>Pending Points</strong> by all users: <strong class='saffi-overview-stats'>{$sums->sum_pending_points}</strong>
				<hr>
				Sum of currently available <strong>Earned Points</strong> by all users: <strong class='saffi-overview-stats'>{$sums->sum_remaining_points}</strong>
			</div>";
			echo "<div class='card'>
				<strong>Registrations</strong>
				<hr>
				Total signups by referral links: <strong class='saffi-overview-stats'>{$sums->sum_total_signups}</strong>
				<hr>
				Total <strong>accepted</strong> signups by referral links: <strong class='saffi-overview-stats'>{$sums->sum_total_accepted}</strong>
			</div>";
		echo "</div>";
	}

}

// User Stats Page
function saffi_admin_pages_user_stats()
{
	$results_per_page = 20;

	// Accepted Orders
	$accepted_order_by = ['user_id', 'earned_points', 'pending_points', 'remaining_points', 'total_signups', 'total_accepted'];

	// Order By
	$order_by = "user_id";
	if(isset($_GET['order_by']) && in_array($_GET['order_by'], $accepted_order_by)){
		$order_by = $_GET['order_by'];
	}

	global $wpdb;

	$stats_table = $wpdb->prefix . "saffi_stats";
	$users_table = $wpdb->prefix . "users";

	$query        = "SELECT * FROM {$stats_table}";
	$total_query  = "SELECT COUNT(1) FROM ({$query}) AS combined_table";
	$total_result = $wpdb->get_var($total_query);
	$total_pages  = ceil($total_result / $results_per_page);
	$page         = isset($_GET['pagination_page']) ? abs((int) $_GET['pagination_page']) : 1;
	$offset       = ($page * $results_per_page) - $results_per_page;

	$query .= " JOIN {$users_table} ON {$stats_table}.user_id = {$users_table}.ID ORDER BY {$stats_table}.{$order_by} DESC LIMIT {$offset}, {$results_per_page}";
	$get_results = $wpdb->get_results($query);

	echo "<h1>Saffi - User Stats</h1>";
	echo "<small>Click on User ID to view that user's history</small>";

	// Display Results
	if($total_result < 1 || ! is_array($get_results)){
		echo "<div class='row'><div class='card'>
				<h2 class='title'>No affiliations yet!</h2>
				<p>Stats will appear here as soon as there are at least one user who has joined through affiliation!</p>
			</div></div>";
	}else{
		echo "<table class='widefat striped'>";
		echo "<thead><tr>
				<th>User ID</th>
				<th>Email</th>
				<th>Points</th>
				<th>Signups</th>
				<th>Last Updated</th>
			</tr></thead>";
		echo "<tbody>";

		// Loop through results
		foreach($get_results as $result){
			$result_link = menu_page_url('saffi_user_history', false);
			echo "<tr>
				<td><a class='button' href='{$result_link}&user_id={$result->user_id}'>{$result->user_id}</a></td>
				<td>{$result->user_email}</td>
				<td>
					Earned: {$result->earned_points}<br>
					Pending: {$result->pending_points}<br>
					Remaining: {$result->remaining_points}<br>
				</td>
				<td>
					Total: {$result->total_signups}<br>
					Accepted: {$result->total_accepted}<br>
				</td>
				<td>{$result->updated_at}</td>
			</tr>";
		}

		echo "</tbody></table>";
	}

	// Display Pagination if Pages > 1
	if($total_pages > 1){
		echo '<div><span>Page '.$page.' of '.$total_pages.'</span>'.paginate_links( array(
				'base' => add_query_arg( 'pagination_page', '%#%' ),
				'format' => '',
				'prev_text' => __('&laquo;'),
				'next_text' => __('&raquo;'),
				'total' => $total_pages,
				'current' => $page
			)).'</div><br>';
	}

	// Links for Order By
	$order_by_link = add_query_arg( 'order_by', '');
	echo "<br><small style=''>Order By: ";
	$o_b_i = 0;
	foreach($accepted_order_by as $order){
		$o_b_i++;
		$order_name = ucwords(str_replace('_',' ', $order));
		$order_link = "{$order_by_link}={$order}";
		$order_style = '';
		if($order === $order_by){
			$order_link = '#';
			$order_style = 'color:#666;text-decoration:none;cursor:not-allowed;';
		}
		if($o_b_i < count($accepted_order_by)){
			$order_style .= 'margin-right:7px;';
		}
		echo "<a style='{$order_style}' href='{$order_link}'>{$order_name}</a>";
	}
	echo "</small><br>";
}


// Saffi User History
function saffi_admin_pages_user_history()
{
	if( ! isset($_GET['user_id']) || ! is_numeric($_GET['user_id'])){
		echo "Wrong User ID!";
		return false;
	}

	$user_id = (int) $_GET['user_id'];
	$user_info = get_userdata($user_id);
	if( ! $user_info){
		echo "User data not found!";
		return false;
	}
	
	global $wpdb;

	$history_table = $wpdb->prefix . "saffi_history";
	$stats_table = $wpdb->prefix . "saffi_stats";
	$users_table = $wpdb->prefix . "users";
	$convert_request_table = $wpdb->prefix . "saffi_convert_request";

	$get_results = $wpdb->get_results("SELECT * FROM {$history_table} JOIN {$users_table} ON {$history_table}.new_user_id = {$users_table}.ID WHERE referrer_id = {$user_id} ORDER BY time DESC LIMIT 100");
	
	
	echo "<h1>User #{$user_info->ID} ({$user_info->user_email})</h1>";
	
	echo "<h2>Stats overview</h2>";
	
	$get_stats = $wpdb->get_row("SELECT * FROM {$stats_table} WHERE user_id = {$user_id}");
	
	echo "<table class='table wp-list-table widefat fixed striped table-view-list'>
		<tr>
			<th>Earned Points</th>
			<th>Pending Points</th>
			<th>Remaining Points</th>
			<th>Requested Points</th>
			<th>Rejected Points</th>
			<th>Total Signups</th>
			<th>Total Accepted</th>
		</tr>";
	if($get_stats && isset($get_stats->user_id)){
		echo "<tr>
				<td>{$get_stats->earned_points}</td>
				<td>{$get_stats->pending_points}</td>
				<td>{$get_stats->remaining_points}</td>
				<td>{$get_stats->coversion_pending_points}</td>
				<td>{$get_stats->rejected_points}</td>
				<td>{$get_stats->total_signups}</td>
				<td>{$get_stats->total_accepted}</td>
			</tr>";
	}else{
		echo "<tr>
				<td>0</td>
				<td>0</td>
				<td>0</td>
				<td>0</td>
				<td>0</td>
				<td>0</td>
				<td>0</td>
			</tr>";
	}
	
	echo "</table>";
	

	echo "<br><hr><h2>Affiliation History</h2>";

	if( ! $get_results || empty($get_results)){
		echo "<div class='row'><div class='card'>
				<h2 class='title'>No affiliations yet!</h2>
				<p>This user has no affiliation history.</p>
			</div></div>";
		return false;
	}

	echo "<table class='widefat striped'>";
	echo "<thead><tr>
				<th>User ID</th>
				<th>Email</th>
				<th>Points</th>
				<th>IP Address</th>
				<th>Signup Time</th>
				<th>Counter</th>
				<th>Accepted</th>
				<th>Accept Time</th>
			</tr></thead>";
	echo "<tbody>";

	// Loop through results
	foreach($get_results as $result){
		$accepted = '-';
		$counter = $result->counter;
		if($result->accepted > 0){
			$accepted = 'Yes';
			$counter = "Reached";
		}
		echo "<tr>
				<td>{$result->ID}</td>
				<td>{$result->user_email}</td>
				<td>{$result->points_if_accepted}</td>
				<td>{$result->ip_address}</td>
				<td>{$result->time}</td>
				<td>{$counter}</td>
				<td>{$accepted}</td>
				<td>{$result->accepted_time}</td>
			</tr>";
	}
	echo "</tbody></table>";

	echo '<small>* A maximum of 100 latest affiliations are listed (ordered by Signup Time).</small><br>';
	
	
	echo "<br><hr><h2>Convert requests</h2>";
	$get_requests = $wpdb->get_results("SELECT * FROM {$convert_request_table} WHERE user_id = {$user_id} ORDER BY id DESC LIMIT 100");
	
	echo "<table class='widefat striped'>";
	echo "<thead><tr>
				<th>Request ID</th>
				<th>Points</th>
				<th>Status</th>
				<th>Request Time</th>
				<th>Handle Time</th>
			</tr></thead>";
	echo "<tbody>";

	// Loop through results
	foreach($get_requests as $result){
		$row_status = 'Pending';
		if($result->accepted > 0){
			$row_status = 'Accepted';
		}
		if($result->rejected > 0){
			$row_status = 'Rejected';
		}
		$result_link = menu_page_url('saffi_user_history', false);
		echo "<tr>
				<td>{$result->id}</td>
				<td>{$result->points}</td>
				<td>{$row_status}</td>
				<td>{$result->time}</td>
				<td>{$result->handle_time}</td>";
		echo "</tr>";
	}
	echo "</tbody></table>";
	echo '<small>* A maximum of 100 latest requests are listed (ordered by Request ID).</small>';
}



// Saffi Requests Page
function saffi_admin_pages_requests()
{
	global $wpdb;
	$allowed_status = ['pending','handled'];
	
	$convert_request_table = $wpdb->prefix . "saffi_convert_request";
	$stats_table = $wpdb->prefix . "saffi_stats";
	$users_table = $wpdb->prefix . "users";
	
	
	// Handle Accept/Reject forms
	if(isset($_POST['request_id']) && is_numeric($_POST['request_id']))
	{
		$request_id = (int) $_POST['request_id'];
		$convert_request = $wpdb->get_row("SELECT * FROM {$convert_request_table} WHERE id = {$request_id} AND status = 0");
		
		if($convert_request && isset($convert_request->user_id, $convert_request->points)){
			$request_points  = (int) $convert_request->points;
			$request_user_id = (int) $convert_request->user_id;
			
			$accept_hash = hash('sha256', SAFFI_SALT . 'accept' . $request_id . $request_user_id . $convert_request->time);
			$reject_hash = hash('sha256', SAFFI_SALT . 'reject' . $request_id . $request_user_id . $convert_request->time);
		}
		
		// Checks
		$accept_confirmed = false;
		$reject_confirmed = false;
		if(isset($_POST['reject_token'],$request_user_id) && $reject_hash === $_POST['reject_token'])
		{
			$reject_confirmed = true;
		}
		elseif(isset($_POST['accept_token'],$request_user_id) && $accept_hash === $_POST['accept_token'])
		{
			$accept_confirmed = true;
		}
		
		// IMPORTANT: The following queries run in DB transaction, so either both are done, or neither
		if($accept_confirmed || $reject_confirmed)
		{
		    // Begin Transaction
		    $wpdb->query('START TRANSACTION');
		}
		
		// Handle Accept
		if($accept_confirmed){
			// Handle Request
			$handled_request = $wpdb->query("UPDATE {$convert_request_table} SET status = 1, handle_time = now(), accepted = 1 WHERE id = {$request_id} AND user_id = {$request_user_id}");
			// Update Stats
			$update_stats = $wpdb->query("UPDATE {$stats_table} SET coversion_pending_points = coversion_pending_points - {$request_points}, earned_points = earned_points + {$request_points} WHERE user_id = {$request_user_id} AND (coversion_pending_points - {$request_points}) >= 0");
		}
		
		// Handle Reject
		if($reject_confirmed){
			// Handle Request
			$handled_request = $wpdb->query("UPDATE {$convert_request_table} SET status = 1, handle_time = now(), rejected = 1 WHERE id = {$request_id} AND user_id = {$request_user_id}");
			// Update Stats
			$update_stats = $wpdb->query("UPDATE {$stats_table} SET coversion_pending_points = coversion_pending_points - {$request_points}, rejected_points = rejected_points + {$request_points} WHERE user_id = {$request_user_id} AND (coversion_pending_points - {$request_points}) >= 0");
		}
		
		// Commit or Rollback
		if($accept_confirmed || $reject_confirmed)
		{
			if($handled_request && $update_stats && is_numeric($handled_request) && is_numeric($update_stats) && $handled_request == 1 && $update_stats == 1){
				// Commit DB transaction
				$wpdb->query('COMMIT');
			}else{
				// Rollback everything!
				$wpdb->query('ROLLBACK');
			}
		}
		
		// End of DB Transaction
	}
	
	
	$status = '0'; // Pending
	$filter_status = "Pending";
	if(isset($_GET['status']) && in_array($_GET['status'], $allowed_status)){
		if($_GET['status'] === 'handled'){
			$status = '1';
			$filter_status = 'Handled';
		}
	}
	
	$get_results = $wpdb->get_results("SELECT *,{$convert_request_table}.id as request_id, {$convert_request_table}.status as request_status FROM {$convert_request_table} JOIN {$users_table} ON {$convert_request_table}.user_id = {$users_table}.ID WHERE {$convert_request_table}.status = {$status} ORDER BY {$convert_request_table}.id DESC LIMIT 300;");
	
	echo "<h1>Saffi - Point Conversion Requests (Displaying: {$filter_status})</h1>";

	if( ! $get_results || empty($get_results)){
		echo "<div class='row'><div class='card'>
				<h2 class='title'>No <strong>{$filter_status}</strong> requests yet!</h2>
				<p>{$filter_status} requests will show up here when there is at least one.</p>
			</div></div>";
			// Links for Order By
			$status_link = add_query_arg( 'status', '');
			echo "<br><small style=''>Display: ";
			$o_b_i = 0;
			foreach($allowed_status as $order){
				$o_b_i++;
				$order_name = ucwords(str_replace('_',' ', $order));
				$order_link = "{$status_link}={$order}";
				$order_style = '';
				if($order === ($_GET['status'] ?? 'pending')){
					$order_link = '#';
					$order_style = 'color:#666;text-decoration:none;cursor:not-allowed;';
				}
				if($o_b_i < count($allowed_status)){
					$order_style .= 'margin-right:7px;';
				}
				echo "<a style='{$order_style}' href='{$order_link}'>{$order_name}</a>";
			}
			echo "</small><br>";
		return false;
	}

	echo "<table class='widefat striped'>";
	echo "<thead><tr>
				<th>Request ID</th>
				<th>User ID</th>
				<th>Email</th>
				<th>Points</th>
				<th>Status</th>
				<th>Request Time</th>
				<th>Action</th>
			</tr></thead>";
	echo "<tbody>";

	// Loop through results
	foreach($get_results as $result){
		$row_status = 'Pending';
		if($result->accepted > 0){
			$row_status = 'Accepted';
		}
		if($result->rejected > 0){
			$row_status = 'Rejected';
		}
		$result_link = menu_page_url('saffi_user_history', false);
		echo "<tr>
				<td>{$result->request_id}</td>
				<td><a class='button' href='{$result_link}&user_id={$result->user_id}'>{$result->user_id}</a></td>
				<td>{$result->user_email}</td>
				<td>{$result->points}</td>
				<td>{$row_status}</td>
				<td>{$result->time}</td>";
				
				// If handled, show time, otherwise show buttons
				if($result->request_status > 0)
				{
					echo "<td>Handled on<br><small>{$result->handle_time}</small></td>";
				}
				else
				{
					$accept_hash = hash('sha256', SAFFI_SALT . 'accept' . $result->request_id . $result->user_id . $result->time);
					$reject_hash = hash('sha256', SAFFI_SALT . 'reject' . $result->request_id . $result->user_id . $result->time);
					
					echo "<td>";
					
					// Accept Form
					echo "<form method='POST'><input type='hidden' name='request_id' value='{$result->request_id}'><input type='hidden' name='accept_token' value='{$accept_hash}'><button type='submit' class='button'>Accept</button></form>";
					
					// Reject Form
					echo "<form method='POST'><input type='hidden' name='request_id' value='{$result->request_id}'><input type='hidden' name='reject_token' value='{$reject_hash}'><button type='submit' class='button'>Reject</button></form>";
					
					echo "</td>";
				}
		
		echo "</tr>";
	}
	echo "</tbody></table>";
	
	// Links for Order By
	$status_link = add_query_arg( 'status', '');
	echo "<br><small style=''>Display: ";
	$o_b_i = 0;
	foreach($allowed_status as $order){
		$o_b_i++;
		$order_name = ucwords(str_replace('_',' ', $order));
		$order_link = "{$status_link}={$order}";
		$order_style = '';
		if($order === ($_GET['status'] ?? 'pending')){
			$order_link = '#';
			$order_style = 'color:#666;text-decoration:none;cursor:not-allowed;';
		}
		if($o_b_i < count($allowed_status)){
			$order_style .= 'margin-right:7px;';
		}
		echo "<a style='{$order_style}' href='{$order_link}'>{$order_name}</a>";
	}
	echo "</small><br>";
}


// Saffi Settings Page
function saffi_admin_pages_settings()
{	
	// Update Settings if Posted data
	if(isset($_POST['saffi_points_per_signup']) && ctype_digit($_POST['saffi_points_per_signup']) && $_POST['saffi_points_per_signup'] > 0 && $_POST['saffi_points_per_signup'] <= 50){
		update_option('saffi_points_per_signup', $_POST['saffi_points_per_signup']);
		$updated = true;
	}
	if(isset($_POST['saffi_min_convertible_points']) && ctype_digit($_POST['saffi_min_convertible_points']) && $_POST['saffi_min_convertible_points'] >= 5){
		update_option('saffi_min_convertible_points', $_POST['saffi_min_convertible_points']);
	}
	if(isset($_POST['saffi_accept_if_counter']) && ctype_digit($_POST['saffi_accept_if_counter']) && $_POST['saffi_accept_if_counter'] > 1 && $_POST['saffi_accept_if_counter'] < 1000){
		update_option('saffi_accept_if_counter', $_POST['saffi_accept_if_counter']);
	}
	if(isset($_POST['saffi_counter_time_limit']) && ctype_digit($_POST['saffi_counter_time_limit']) && $_POST['saffi_counter_time_limit'] >= 1 && $_POST['saffi_counter_time_limit'] < 1000){
		update_option('saffi_counter_time_limit', $_POST['saffi_counter_time_limit']);
	}
	if(isset($_POST['saffi_obfuscate_user_id']) && ctype_digit($_POST['saffi_obfuscate_user_id'])){
		update_option('saffi_obfuscate_user_id', $_POST['saffi_obfuscate_user_id']);
	}
	if(isset($_POST['saffi_store_user_ip']) && ctype_digit($_POST['saffi_store_user_ip'])){
		update_option('saffi_store_user_ip', $_POST['saffi_store_user_ip']);
	}
	if(isset($_POST['saffi_referral_url_base']) && filter_var($_POST['saffi_referral_url_base'], FILTER_VALIDATE_URL)){
		update_option('saffi_referral_url_base', $_POST['saffi_referral_url_base']);
	}
	
	echo "<h1>Saffi - Settings</h1>";
	echo "<style>.saffi-settings-input{float:right;}.saffi-settings-box hr{clear:both;margin-top:25px;margin-bottom:10px;}.success{color:green;padding:10px 0;font-weight:bold;}</style>";
	
	if(isset($updated)){
		echo "<div class='success'>Setting updated</div>";
	}
	
	$saffi_points_per_signup      = get_option('saffi_points_per_signup');
	$saffi_min_convertible_points = get_option('saffi_min_convertible_points');
	$saffi_accept_if_counter      = get_option('saffi_accept_if_counter');
	$saffi_counter_time_limit     = get_option('saffi_counter_time_limit');
	$saffi_obfuscate_user_id      = get_option('saffi_obfuscate_user_id');
	$saffi_store_user_ip          = get_option('saffi_store_user_ip');
	
	
	// Referral Base URL
	$saffi_referral_url_base = get_option('saffi_referral_url_base');
	if( ! $saffi_referral_url_base || ! filter_var($saffi_referral_url_base, FILTER_VALIDATE_URL)){
		$saffi_referral_url_base = site_url();
	}
	
	echo "<div class='card saffi-settings-box'><h2>Settings</h2><form method='POST'>";
	
	// Inputs
	echo "Points earned per referral signup: <input class='saffi-settings-input' type='text' name='saffi_points_per_signup' value='{$saffi_points_per_signup}'>";
	
	echo "<hr>Minimum Convertible Points: <input class='saffi-settings-input' type='text' name='saffi_min_convertible_points' value='{$saffi_min_convertible_points}'>";
	
	echo "<hr>Clicks (Views) required to accept signup: <input class='saffi-settings-input' type='text' name='saffi_accept_if_counter' value='{$saffi_accept_if_counter}'>";
	
	echo "<hr>Time limit between clicks (seconds): <input class='saffi-settings-input' type='text' name='saffi_counter_time_limit' value='{$saffi_counter_time_limit}'>";
	
	echo "<hr>Referral URL base <input class='saffi-settings-input' type='text' name='saffi_referral_url_base' value='{$saffi_referral_url_base}'><br><small>(full URL with https:// in the beginning)</small>";
	
	echo "<hr>Obfuscate User ID in Invite Link:
	<div><br>
		<input type='radio' id='saffi_obfuscate_user_id1' name='saffi_obfuscate_user_id' value='1'"; if($saffi_obfuscate_user_id > 0){echo "checked='checked'";} echo "><label for='saffi_obfuscate_user_id1'> Yes</label> 
		<input type='radio' id='saffi_obfuscate_user_id2' name='saffi_obfuscate_user_id' value='0'"; if($saffi_obfuscate_user_id < 1){echo "checked='checked'";} echo "><label for='saffi_obfuscate_user_id2'> No</label>
		</div>
	";
	
	echo "<hr>Store User IP Address in Affiliation History:
	<div><br>
		<input type='radio' id='saffi_store_user_ip1' name='saffi_store_user_ip' value='1'"; if($saffi_store_user_ip > 0){echo "checked='checked'";} echo "><label for='saffi_store_user_ip1'> Yes</label> 
		<input type='radio' id='saffi_store_user_ip2' name='saffi_store_user_ip' value='0'"; if($saffi_store_user_ip < 1){echo "checked='checked'";} echo "><label for='saffi_store_user_ip2'> No</label>
		</div>
	";
	
	echo "<hr><br><input type='submit' value='Save Settings' class='button'>";
	
	echo "</form></div>";
}


// Saffi Guide Page
function saffi_admin_pages_guide()
{
	echo "<h1>Saffi - Guide</h1>";
	
	echo "<div class='card'>";
	echo "<h2>Shortcodes:</h2>";
	echo "<small>Simply put the following shortcodes in any page you see fit! these use the default messages (English), but you can customize them to any message/language!</small><br><br>";
	echo "Display user's referral link:<pre>[saffi_user_ref_link]</pre><hr>";
	echo "Display point conversion form:<pre>[saffi_user_withdraw_form]</pre><hr>";
	echo "Display user's stats table:<pre>[saffi_user_affiliate_stats_table]</pre><hr>";
	echo "Display single user stats with title in a single row table:<pre>[saffi_user_stats_row show='CHANGETHIS' title='TEXT']</pre><small style='color:#777;'>- Valid values to replace with CHANGETHIS: earned_points, pending_points, remaining_points, coversion_pending_points, total_signups, total_accepted<br>- Replace the row title with TEXT</small><br><hr>";
	echo "Display single user stats without table and title:<pre>[saffi_user_stats show='CHANGETHIS']</pre><small style='color:#777;'>- Valid values to replace with CHANGETHIS: earned_points, pending_points, remaining_points, coversion_pending_points, total_signups, total_accepted</small><br><hr>";
	echo "</div>";
	
	// Point Conversion
	echo "<div class='card'>";
	echo "<h2>Customize 'Point Conversion Shortcode':</h2>";
	echo "<h4>Customize Messages/Language:<br><small>Simply change the values below and use any message/language!</small></h4>";
	echo "<code>[saffi_user_affiliate_stats_table input_placeholder='Amount of points to convert' button_name='Convert Pending Points to Earned Points' not_enough_points_message='You do not have enough points to convert!' success_message='Your pending points are successfully converted to earned points!' fail_message='There was an error while converting your points, please contact us!']</code><hr>";
	
	echo "<h4>Customize Style (CSS):</h4>";
	echo "Form CSS ID: <pre>#point_conversion_form</pre><hr>";
	echo "Success message CSS Class: <pre>.point_convert_success</pre><hr>";
	echo "Fail message CSS Class: <pre>.point_convert_fail</pre><hr>";
	echo "Input for Amount of Points: <pre>.point_amount_input</pre><hr>";
	echo "Submit Button CSS Class: <pre>.point_convert_submit_button</pre><hr>";
	echo "Not enough points message CSS Class: <pre>.not_enough_points</pre><hr>";
	echo "Disabled submit Button CSS Class (when not enough points): <pre>.not_enough_points_button</pre><hr>";
	echo "</div>";
	
	// User Stats Table
	echo "<div class='card'>";
	echo "<h2>Customize 'User Stats Table Shortcode':<br><small>Simply change the values below and use any message/language!</small></h2>";
	echo "<code>[saffi_user_affiliate_stats_table coversion_pending_points='Requested for conversion' earned_points='Points Earned' pending_points='Pending Points' remaining_points='Remaining Points' total_signups='Total Signups' total_accepted='Total Accepted Signups']</code><hr>";
	
	echo "<h4>Customize Style (CSS):</h4>";
	echo "Table CSS ID: <pre>#affiliation_stats_table</pre><hr>";
	echo "</div>";
	
	echo "<div class='card'>";
	echo "<h2>Notes:</h2>";
	echo "- Only Admins with the permission 'promote_users' can access the Saffi plugin pages and features.";
	echo "</div>";
	
}
