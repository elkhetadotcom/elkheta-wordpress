<?php

class USIN_LearnDash_Query{
	
	protected $has_subscription_status_join_applied = false;
	protected $topic_type = 'topic';
	protected $course_type = 'course';
	protected $lesson_type = 'lesson';
	protected $quiz_type = 'quiz';
	protected $count = 0;
	
	public function __construct(){
		$this->init();
	}
	
	public function init(){
		add_filter('usin_db_map', array($this, 'filter_db_map'));
		add_filter('usin_query_join_table', array($this, 'filter_query_joins'), 10, 2);
		add_filter('usin_custom_query_filter', array($this, 'apply_custom_query_filters'), 10, 2);
	}
	
	public function filter_db_map($db_map){
		global $wpdb;
		$db_map['ld_lessons_completed'] = array('db_ref'=>'lessons_completed', 'db_table'=>'ld_completed', 'null_to_zero'=>true, 'set_alias'=>true);
		$db_map['ld_topics_completed'] = array('db_ref'=>'topics_completed', 'db_table'=>'ld_completed', 'null_to_zero'=>true, 'set_alias'=>true);
		$db_map['ld_courses_completed'] = array('db_ref'=>'courses_completed', 'db_table'=>'ld_completed', 'null_to_zero'=>true, 'set_alias'=>true);
		$db_map['ld_courses_in_progress'] = array('db_ref'=>'courses_in_progress', 'db_table'=>'ld_courses_in_progress', 'null_to_zero'=>true, 'set_alias'=>true);
		$db_map['ld_quiz_attempts'] = array('db_ref'=>'attempts', 'db_table'=>'ld_quizes', 'null_to_zero'=>true, 'set_alias'=>true);
		$db_map['ld_quiz_passes'] = array('db_ref'=>'passes', 'db_table'=>'ld_quizes', 'null_to_zero'=>true, 'set_alias'=>true);
		$db_map['ld_last_activity'] = array('db_ref'=>'last_activity', 'db_table'=>'ld_last_activity', 'set_alias'=>true, 'nulls_last' => true);
		$db_map['ld_has_completed_course'] = array('db_ref'=>'', 'db_table'=>'', 'no_select'=>true);
		$db_map['ld_has_not_completed_course'] = array('db_ref'=>'', 'db_table'=>'', 'no_select'=>true);
		$db_map['ld_has_enrolled_course'] = array('db_ref'=>'', 'db_table'=>'', 'no_select'=>true);
		$db_map['ld_has_not_enrolled_course'] = array('db_ref'=>'', 'db_table'=>'', 'no_select'=>true);
		$db_map['ld_has_passed_quiz'] = array('db_ref'=>'', 'db_table'=>'', 'no_select'=>true);
		$db_map['ld_has_not_passed_quiz'] = array('db_ref'=>'', 'db_table'=>'', 'no_select'=>true);
		$db_map['ld_group'] = array('db_ref'=>'ld_group', 'db_table'=>'ld_groups', 'no_select'=>true);

		// quiz results
		$quiz_results_field_ids = USIN_LearnDash_Quiz_Results::get_field_ids();
		foreach ($quiz_results_field_ids as $field_id ) {
			$db_map[$field_id] = array('db_ref'=>'meta_value', 'db_table'=>'ld_quiz_results');
		}

		// course_analytics results
		$course_analytics_ids = USIN_LearnDash_Course_Analytics::get_enabled_course_ids();
		foreach ($course_analytics_ids as $course_id ) {
			$started_field = USIN_LearnDash_Course_Analytics::get_started_field_id($course_id);
			$completed_field = USIN_LearnDash_Course_Analytics::get_completed_field_id($course_id);
			$table_alias = USIN_LearnDash_Course_Analytics::get_db_table_alias($course_id);

			$db_map[$started_field] = array('db_ref'=>'started_on', 'db_table'=>$table_alias, 'nulls_last' => true);
			$db_map[$completed_field] = array('db_ref'=>'completed_on', 'db_table'=>$table_alias, 'nulls_last' => true);
		}

		return $db_map;
	}

	public function filter_query_joins($query_joins, $table){
		global $wpdb;

		$ld_activity_table = $this->get_ld_table_name();
		
		if($table =='ld_completed'){
			$query_joins.= " LEFT JOIN (
				SELECT user_id,
				SUM(CASE WHEN activity_type = '$this->lesson_type' THEN 1 ELSE 0 END) AS lessons_completed,
				SUM(CASE WHEN activity_type = '$this->course_type' THEN 1 ELSE 0 END) AS courses_completed,
				SUM(CASE WHEN activity_type = '$this->topic_type' THEN 1 ELSE 0 END) AS topics_completed
				FROM $ld_activity_table
				WHERE activity_status=1 AND activity_type IN ('$this->lesson_type', '$this->course_type', '$this->topic_type')
				GROUP BY user_id
				) AS ld_completed ON $wpdb->users.ID = ld_completed.user_id";
		}elseif($table =='ld_courses_in_progress'){
			$query_joins.= " LEFT JOIN (
				SELECT user_id, COUNT(activity_id) as courses_in_progress
				FROM $ld_activity_table
				WHERE activity_status=0 AND activity_type = '$this->course_type'
				GROUP BY user_id
				) AS ld_courses_in_progress ON $wpdb->users.ID = ld_courses_in_progress.user_id";
		}elseif($table == 'ld_quizes'){
			$query_joins.= " LEFT JOIN (
				SELECT user_id,
				COUNT(activity_id) as attempts,
				SUM(CASE WHEN activity_status = 1 THEN 1 ELSE 0 END) AS passes
				FROM $ld_activity_table
				WHERE activity_type = '$this->quiz_type'
				GROUP BY user_id
			)  AS ld_quizes ON $wpdb->users.ID = ld_quizes.user_id";
		}elseif($table == 'ld_last_activity'){
			$query_joins.= " LEFT JOIN (
				SELECT user_id, MAX(from_unixtime(activity_updated)) AS last_activity
				FROM $ld_activity_table
				GROUP BY user_id
			)  AS ld_last_activity ON $wpdb->users.ID = ld_last_activity.user_id";
		}elseif($table == 'ld_quiz_results'){
			$query_joins.= " LEFT JOIN $wpdb->usermeta AS ld_quiz_results ON $wpdb->users.ID = ld_quiz_results.user_id AND ld_quiz_results.meta_key = '_sfwd-quizzes'";
		}elseif(USIN_LearnDash_Course_Analytics::is_course_analytics_table($table)){
			$course_id = USIN_LearnDash_Course_Analytics::get_course_id_by_db_table($table);
			// here we use min and max as in some rare cases there is more than one record per course/user
			$query_joins.= $wpdb->prepare( " LEFT JOIN (
				SELECT user_id,
				IF(activity_started = 0, NULL, FROM_UNIXTIME(activity_started)) AS started_on,
				IF(activity_completed = 0, NULL, FROM_UNIXTIME(activity_completed)) AS completed_on
				FROM $ld_activity_table WHERE activity_type = '$this->course_type' AND post_id = %d
			) AS $table ON $wpdb->users.ID = $table.user_id", $course_id);
		}
		return $query_joins;
	}
	
	public function apply_custom_query_filters($custom_query_data, $filter){
		global $wpdb;
		$ref = 'ldr_'.++$this->count;
		
		if($filter->by == 'ld_has_completed_course' || $filter->by == 'ld_has_not_completed_course'){
			
			$custom_query_data['joins'] .= $wpdb->prepare(" LEFT JOIN
				(SELECT user_id, post_id FROM ".$this->get_ld_table_name()." WHERE post_id = %d 
				AND activity_status = 1 AND activity_type = '$this->course_type'
				GROUP BY user_id) AS $ref ON $wpdb->users.ID = $ref.user_id", $filter->condition);
			
			$operator = $filter->by == 'ld_has_completed_course' ? 'IS NOT NULL' : 'IS NULL';
			$custom_query_data['where'] = " AND $ref.post_id $operator";
			
		}elseif($filter->by == 'ld_has_enrolled_course' || $filter->by == 'ld_has_not_enrolled_course' ){
			
			$custom_query_data['joins'] .= $wpdb->prepare(" LEFT JOIN
				(SELECT user_id, post_id FROM ".$this->get_ld_table_name()." WHERE post_id = %d 
				AND activity_type = '$this->course_type'
				GROUP BY user_id) AS $ref ON $wpdb->users.ID = $ref.user_id", $filter->condition);
			
			$operator = $filter->by == 'ld_has_enrolled_course' ? 'IS NOT NULL' : 'IS NULL';
			$custom_query_data['where'] = " AND $ref.post_id $operator";
			
		}elseif($filter->by == 'ld_has_passed_quiz' || $filter->by == 'ld_has_not_passed_quiz'){
			
			$custom_query_data['joins'] .= $wpdb->prepare(" LEFT JOIN
				(SELECT user_id, post_id FROM ".$this->get_ld_table_name()." WHERE post_id = %d 
				AND activity_status = 1 AND activity_type = '$this->quiz_type'
				GROUP BY user_id) AS $ref ON $wpdb->users.ID = $ref.user_id", $filter->condition);
			
			$operator = $filter->by == 'ld_has_passed_quiz' ? 'IS NOT NULL' : 'IS NULL';
			$custom_query_data['where'] = " AND $ref.post_id $operator";
		}elseif($filter->by == 'ld_group'){
			$custom_query_data['joins'] .= $wpdb->prepare(" LEFT JOIN
				$wpdb->usermeta AS $ref ON $wpdb->users.ID = $ref.user_id AND $ref.meta_key = %s ", 'learndash_group_users_'.$filter->condition);
			
			$operator = $filter->operator == 'include' ? 'IS NOT NULL' : 'IS NULL';
			$custom_query_data['where'] = " AND $ref.meta_value $operator";
		}
	
		return $custom_query_data;
	}
	
	

	protected function get_ld_table_name(){
		global $wpdb;
		
		return $wpdb->prefix.'learndash_user_activity';
	}

	
}