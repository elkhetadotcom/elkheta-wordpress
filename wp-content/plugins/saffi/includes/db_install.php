<?php
// Database Structure Version (this is not the same as the plugin version)
$saffi_db_version = '0.9.2';

// Make sure we don't expose any info if called directly
if( ! defined('SAFFI_PATH')) {
	echo 'Direct access is denied!';
	exit;
}

global $wpdb;

////////////
// Install
////////////
$stats_table_name   = $wpdb->prefix . "saffi_stats";
$history_table_name = $wpdb->prefix . "saffi_history";
$convert_request_table_name = $wpdb->prefix . "saffi_convert_request";
$charset_collate = $wpdb->get_charset_collate();

// Stats Table
$sql_queries[] = "CREATE TABLE $stats_table_name (
  user_id bigint(20) unsigned NOT NULL,
  earned_points bigint(20) unsigned NOT NULL DEFAULT 0,
  pending_points bigint(20) unsigned NOT NULL DEFAULT 0,
  remaining_points bigint(20) unsigned NOT NULL DEFAULT 0,
  coversion_pending_points bigint(20) unsigned NOT NULL DEFAULT 0,
  rejected_points bigint(20) unsigned NOT NULL DEFAULT 0,
  total_signups bigint(20) unsigned NOT NULL DEFAULT 0,
  total_accepted bigint(20) unsigned NOT NULL DEFAULT 0,
  total_clicks bigint(20) unsigned NOT NULL DEFAULT 0,
  total_row_updates bigint(20) unsigned NOT NULL DEFAULT 0,
  updated_at timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (user_id)
) $charset_collate;";

// History Table
$sql_queries[] = "CREATE TABLE $history_table_name (
  new_user_id bigint(20) unsigned NOT NULL,
  referrer_id bigint(20) unsigned NOT NULL,
  ip_address varchar(60) NULL DEFAULT NULL,
  accepted tinyint(1) unsigned NOT NULL DEFAULT 0,
  counter tinyint(3) unsigned NOT NULL DEFAULT 0,
  last_count integer(10) unsigned NULL DEFAULT NULL,
  points_if_accepted smallint(5) unsigned NOT NULL DEFAULT 0,
  time timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  accepted_time timestamp NULL DEFAULT NULL,
  PRIMARY KEY  (new_user_id),
  KEY  referrer_id (referrer_id)
) $charset_collate;";

// Convert Request Table
$sql_queries[] = "CREATE TABLE $convert_request_table_name (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint(20) unsigned NOT NULL,
  points bigint(20) unsigned NOT NULL DEFAULT 0,
  status tinyint(1) unsigned NOT NULL DEFAULT 0,
  accepted tinyint(1) unsigned NOT NULL DEFAULT 0,
  rejected tinyint(1) unsigned NOT NULL DEFAULT 0,
  admin_message varchar(500) NULL DEFAULT NULL,
  time timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  handle_time timestamp NULL DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY  user_id (user_id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql_queries);

// Add DB Version
add_option('saffi_db_version', $saffi_db_version);

// Settings
add_option('saffi_points_per_signup', '1');
add_option('saffi_min_convertible_points', '50');
add_option('saffi_accept_if_counter', '5');
add_option('saffi_counter_time_limit', '3');
add_option('saffi_obfuscate_user_id', '0');
add_option('saffi_store_user_ip', '0');
add_option('saffi_referral_url_base', '');
