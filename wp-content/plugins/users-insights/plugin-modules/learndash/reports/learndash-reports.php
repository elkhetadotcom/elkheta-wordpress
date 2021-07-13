<?php

class USIN_Learndash_Reports extends USIN_Module_Reports{

	protected $group = 'learndash';

	public function __construct($ld){
		parent::__construct();
		$this->ld = $ld;
	}

	public function get_group(){
		
		return array(
			'id' => $this->group,
			'name' => 'LearnDash'
		);
	}

	public function get_reports(){


		$reports = array(
			new USIN_Period_Report('learndash_active_students', __('Active students', 'usin'), 
				array(
					'group'=>'learndash'
				)
			),
			new USIN_Period_Report('learndash_course_enrolments', sprintf(__('%s started', 'usin'), USIN_LearnDash::get_label('courses')), 
				array(
					'group'=>'learndash'
				)
			),
			new USIN_Period_Report('learndash_course_completions', sprintf(__('%s completed', 'usin'), USIN_LearnDash::get_label('courses')), 
				array(
					'group'=>'learndash'
				)
			),
			new USIN_Standard_Report('learndash_course_students', sprintf(__('Top %s by student number', 'usin'), USIN_LearnDash::get_label('courses')), 
				array(
					'group'=>'learndash', 
					'type'=>USIN_Report::BAR, 
					'filters' => array(
						'options' => array(
							'all' => __('All statuses', 'usin'),
							'completed' => __('Completed', 'usin'),
							'in_progress' => __('In Progress', 'usin')
						),
						'default' => 'all'
					)
				)
			),
		);

		$groups = USIN_LearnDash::get_items(USIN_LearnDash::GROUP_POST_TYPE);
		
		if(sizeof($groups) > 0){
			$reports[]= new USIN_Standard_Report('learndash_groups', __('Top groups by student number', 'usin'), 
					array(
						'group'=>'learndash',
						'type' => USIN_Report::BAR
					)
				);
		}

		$quizzes = USIN_LearnDash::get_items(USIN_LearnDash::QUIZ_POST_TYPE, true);

		if(sizeof($quizzes) > 0){
			$quizzes['all'] = sprintf(__('All %s', 'usin'), USIN_LearnDash::get_label('quizzes'));

			$reports[]= new USIN_Standard_Report('learndash_quiz_attempts', sprintf(__('%s attempts', 'usin'), USIN_LearnDash::get_label('quiz')), 
					array(
						'group'=>'learndash',
						'filters' => array(
							'default' => 'all',
							'options' => $quizzes
						)
					)
				);


			$reports[]= new USIN_Standard_Report('learndash_quiz_score', sprintf(__('%s score', 'usin'), USIN_LearnDash::get_label('quiz')), 
				array(
					'group'=>'learndash',
					'type' => USIN_Report::BAR,
					'filters' => array(
						'default' => 'all',
						'options' => $quizzes
					)
				)
			);
		}

		$analytics_courses = USIN_LearnDash_Course_Analytics::get_enabled_courses();
		foreach ($analytics_courses as $course_id => $course_name) {
			$started_report_id = USIN_LearnDash_Course_Analytics::get_started_field_id($course_id);
			$reports[]=new USIN_Period_Report($started_report_id, "Students started $course_name", 
				array(
					'group'=>'learndash',
					'visible'=>false,
					'loader_class' => 'USIN_Learndash_Course_Enrolments_Loader'
				)
			);

			$completed_report_id = USIN_LearnDash_Course_Analytics::get_completed_field_id($course_id);
			$reports[]=new USIN_Period_Report($completed_report_id, "Students completed $course_name", 
				array(
					'group'=>'learndash',
					'visible'=>false,
					'loader_class' => 'USIN_Learndash_Course_Completions_Loader'
				)
			);
		}
		

		return $reports;

	}
}