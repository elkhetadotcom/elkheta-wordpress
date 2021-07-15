<?php

class USIN_Gravity_Forms_Submissions_Loader extends USIN_Period_Report_Loader{

	protected function load_data(){
		global $wpdb;

		$table_name = USIN_Gravity_Forms::get_entries_db_table_name();
		$form_id = $this->report->options['form_id'];
		$group_by = $this->get_period_group_by('date_created');

		$query = $wpdb->prepare("SELECT date_created AS $this->label_col, COUNT(*) AS $this->total_col".
			" FROM $table_name WHERE form_id = %d AND date_created >= %s AND date_created <= %s GROUP BY $group_by",
			$form_id, $this->get_period_start(), $this->get_period_end());

		return $wpdb->get_results( $query );
	}


}