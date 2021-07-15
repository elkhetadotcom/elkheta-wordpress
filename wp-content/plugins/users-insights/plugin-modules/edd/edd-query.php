<?php 

/**
 * Includes the DB query functionality for the EDD module.
 */
class USIN_EDD_Query{
	
	protected $has_ordered_join_applied = false;
	protected $orders_join_set = false;
	protected $customers_join_set = false;
	
	/**
	 * Inits the main functionality - registers filter hooks.
	 */
	public function init(){
		add_filter('usin_db_map', array($this, 'filter_db_map'));
		add_filter('usin_query_join_table', array($this, 'filter_query_joins'), 10, 2);
		add_filter('usin_custom_query_filter', array($this, 'apply_filters'), 10, 2);
		add_filter('usin_custom_select', array($this, 'filter_query_select'), 10, 2);
	}
	
	/**
	 * Filters the default DB map fields and adds the custom EDD fields to the map.
	 * @param  array $db_map the default DB map array
	 * @return array         the default DB map array including the EDD fields
	 */
	public function filter_db_map($db_map){
		$db_map['edd_order_num'] = array('db_ref'=>'purchase_count', 'db_table'=>'edd_customers', 'null_to_zero'=>true);
		$db_map['edd_total_spent'] = array('db_ref'=>'purchase_value', 'db_table'=>'edd_customers', 'null_to_zero'=>true, 'cast'=>'DECIMAL', 'custom_select'=>true);
		$db_map['edd_has_ordered'] = array('db_ref'=>'', 'db_table'=>'', 'no_select'=>true);
		$db_map['edd_has_order_status'] = array('db_ref'=>'', 'db_table'=>'payments', 'no_select'=>true);
		$db_map['edd_last_order'] = array('db_ref'=>'edd_last_order', 'db_table'=>'payments_dates', 'nulls_last'=>true, 'cast'=>'DATETIME');
		return $db_map;
	}
	
	/**
	 * Adds the custom SELECT clauses for the EDD fields.
	 * @param  string $query_select the main SELECT clause to which to append the
	 * EDD selects
	 * @return string               the modified SELECT clause
	 */
	public function filter_query_select($query_select, $field){
		if($field == 'edd_total_spent'){
			$query_select='CAST(IFNULL(edd_customers.purchase_value, 0) AS DECIMAL(10,2))';
		}
		return $query_select;
	}

	/**
	 * Adds the custom query JOINS for the EDD fields.
	 * @param  string $query_joins the main JOINS string to which to append the 
	 * custom EDD joins 
	 * @return string              the modified JOINS query
	 */
	public function filter_query_joins($query_joins, $table){
		global $wpdb;

		if($table == 'edd_customers'){
			$query_joins .= $this->get_customers_join();
		}elseif($table == 'payments_dates'){
			$orders_table = $wpdb->prefix.'edd_orders';
			$max_date_local = self::get_gmt_offset_date_select('MAX(date_created)');
			$subquery = "SELECT user_id, $max_date_local AS edd_last_order FROM $orders_table WHERE type='sale' GROUP BY user_id";
			$query_joins .= " LEFT JOIN ($subquery) AS payments_dates ON payments_dates.user_id = $wpdb->users.ID";
		}
		
		return $query_joins;
	}
	
	protected function get_customers_join(){
		if(!$this->customers_join_set){
			global $wpdb;
			$this->customers_join_set = true;
			return " LEFT JOIN ".$wpdb->prefix."edd_customers AS edd_customers ON $wpdb->users.ID = edd_customers.user_id";
		}
		return '';
	}
	
	/**
	 * Generates a LEFT JOIN with the posts table to join the orders (edd payments)
	 * posts only. This JOIN is generated only once.
	 * @return string the JOIN clause if it hasn't been loaded yet or an empty 
	 * string otherwise.
	 */
	protected function get_orders_join(){
		if(!$this->orders_join_set){
			global $wpdb;
			
			$this->orders_join_set = true;
			$orders_table = $wpdb->prefix.'edd_orders';
			return " LEFT JOIN $orders_table AS edd_orders ON edd_orders.user_id = $wpdb->users.ID AND edd_orders.type = 'sale'";
			
		}
		return '';
	}
	
	/**
	 * Applies the custom filters for "Products ordered include/exclude" and 
	 * "Orders status include/exclude"
	 * @param  array $custom_query_data includes the default joins, where and having 
	 * clauses, so that this function can generate them and return this array
	 * @param  object $filter            filter object, contains the filter data 
	 * such as condition and operator
	 * @return array                    the modified $custom_query_data array, that 
	 * includes the generated JOIN, WHERE and HAVING clauses
	 */
	public function apply_filters($custom_query_data, $filter){

		if(in_array($filter->operator, array('include', 'exclude'))){
			global $wpdb;
			$operator = $filter->operator == 'include' ? '>' : '=';
			
			if($filter->by == 'edd_has_ordered'){
				//filter by the products ordered (can be include or exclude)
				
				if(!$this->has_ordered_join_applied){
					//this join depends on the edd_customers join above, so we are going to append it
					//to the main joins query, instead of this one
					$custom_query_data['joins'] =  $this->get_orders_join().
						" INNER JOIN ".$wpdb->prefix."edd_order_items AS edd_order_items ON edd_orders.id = edd_order_items.order_id";

					$this->has_ordered_join_applied = true;
				}
				
				$custom_query_data['having'] = $wpdb->prepare(" AND SUM(edd_order_items.product_id IN (%d)) $operator 0", $filter->condition);

			}elseif($filter->by == 'edd_has_order_status'){
				//filter by the status of the orders (can be include or exclude)
			
				$custom_query_data['joins'] = $this->get_orders_join();
				$custom_query_data['having'] = $wpdb->prepare(" AND SUM(edd_orders.status IN (%s)) $operator 0", $filter->condition);
			
			}
		}

		return $custom_query_data;
	}

	public static function get_gmt_offset_date_select($date_column){
		global $wpdb;
		$offset = get_option('gmt_offset');
		return $wpdb->prepare("DATE_ADD($date_column, INTERVAL %d HOUR)", $offset);
	}

	
	/**
	 * Resets the query options - this should be called when more than one
	 * query is performed per http request
	 */
	public function reset(){
		$this->has_ordered_join_applied = false;
		$this->customers_join_set = false;
		$this->orders_join_set = false;
	}
}