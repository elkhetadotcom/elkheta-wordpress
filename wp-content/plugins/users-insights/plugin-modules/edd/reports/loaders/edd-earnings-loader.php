<?php

class USIN_Edd_Earnings_Loader extends USIN_Period_Report_Loader {
	
	protected function load_data(){
		global $wpdb;

		$query = USIN_EDD::is_edd_v30() ? $this->get_query() : $this->get_legacy_query();

		return $wpdb->get_results( $query );
	}

	private function get_query(){
		global $wpdb;

		$group_by = $this->get_period_group_by('date_created');
		$orders_table = $wpdb->prefix.'edd_orders';
		$date_selector = USIN_EDD_Query::get_gmt_offset_date_select('date_created');
		return $wpdb->prepare("SELECT SUM( total ) AS $this->total_col, $date_selector AS $this->label_col ".
			"FROM $orders_table ".
			"WHERE type = 'sale' AND status IN ('complete', 'revoked') AND $date_selector >= %s AND $date_selector <= %s ".
			"GROUP BY $group_by", $this->get_period_start(), $this->get_period_end());
	}

	private function get_legacy_query(){
		global $wpdb;

		$group_by = $this->get_period_group_by('orders.post_date');
		return $wpdb->prepare("SELECT SUM( totals.meta_value) AS $this->total_col, orders.post_date AS $this->label_col ".
			"FROM $wpdb->posts AS orders ".
			"INNER JOIN $wpdb->postmeta AS totals ON ( orders.ID = totals.post_id AND totals.meta_key = '_edd_payment_total' ) ".
			"WHERE orders.post_type = %s AND orders.post_status IN ('publish', 'revoked') AND orders.post_date >= %s AND orders.post_date <= %s GROUP BY $group_by",
			USIN_EDD::ORDER_POST_TYPE, $this->get_period_start(), $this->get_period_end());
	}
}