<?php

class USIN_Learndash_Course_Completions_Loader extends USIN_Period_Report_Loader {

	protected function load_data(){
		global $wpdb;

		$group_by = $this->get_period_group_by($this->label_col);
		$start = mysql2date('U', $this->get_period_start());
		$end =  mysql2date('U', $this->get_period_end());

		$focus_course_id = $this->get_focus_course_id();
		$course_condition = $focus_course_id ? $wpdb->prepare(' AND post_id = %d', $focus_course_id) : '';

		$query = $wpdb->prepare("SELECT COUNT(*) as $this->total_col, FROM_UNIXTIME(activity_completed) AS $this->label_col".
			" FROM ".$wpdb->prefix."learndash_user_activity WHERE activity_type = 'course'".$course_condition.
			" AND activity_status = 1 AND activity_completed != 0 AND activity_completed >= %d AND activity_completed <= %d GROUP BY $group_by",
			$start, $end);

		return $wpdb->get_results( $query );
	}

	protected function get_focus_course_id(){
		if($this->report->id != 'learndash_course_completions'){
			return USIN_LearnDash_Course_Analytics::get_course_id_by_field_id($this->report->id);
		}
	}
}