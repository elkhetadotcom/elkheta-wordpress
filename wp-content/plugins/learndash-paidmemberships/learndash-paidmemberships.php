<?php
/**
 * Plugin Name: LearnDash LMS - Paid Memberships Pro
 * Plugin URI: http://www.learndash.com
 * Description: LearnDash integration with the Paid Memberships Pro plugin that allows to control the course's access by a user level.
 * Version: 1.3.1
 * Author: LearnDash
 * Author URI: http://www.learndash.com
 * Text Domain: learndash-paidmemberships
 * Doman Path: /languages/
 */

if ( ! class_exists( 'Learndash_Paidmemberships' ) ) {

class Learndash_Paidmemberships {
	/**
	 * Define constants used in the plugin
	 * 
	 * @return void
	 */
	public static function define_constants() 
	{
		// Plugin version
		if ( ! defined( 'LEARNDASH_PMP_VERSION' ) ) {
			define( 'LEARNDASH_PMP_VERSION', '1.3.1' ); 
		}

		// Plugin file
		if ( ! defined( 'LEARNDASH_PMP_FILE' ) ) {
			define( 'LEARNDASH_PMP_FILE', __FILE__ );
		}		

		// Plugin folder path
		if ( ! defined( 'LEARNDASH_PMP_PLUGIN_PATH' ) ) {
			define( 'LEARNDASH_PMP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL
		if ( ! defined( 'LEARNDASH_PMP_PLUGIN_URL' ) ) {
			define( 'LEARNDASH_PMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Include necessary files
	 * 
	 * @return void
	 */
	public static function includes() 
	{
		include LEARNDASH_PMP_PLUGIN_PATH . 'includes/class-tools.php';
	}

	/**
	 * Load language files
	 * 
	 * @return void
	 */
	public static function i18nize() 
	{
		load_plugin_textdomain( 'learndash-paidmemberships', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 	

		// include translations class
		include LEARNDASH_PMP_PLUGIN_PATH . 'includes/class-translations-ld-paidmemberships.php';
	}

	/**
	 * Check plugin dependency
	 * 
	 * @return void
	 */
	public static function check_dependency() 
	{
		include LEARNDASH_PMP_PLUGIN_PATH . 'includes/class-dependency-check.php';
		
		LearnDash_Dependency_Check_LD_Paidmemberships::get_instance()->set_dependencies(
			array(
				'sfwd-lms/sfwd_lms.php' => array(
					'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
					'class'       => 'SFWD_LMS',
					'min_version' => '3.0.0',
				),
				'paid-memberships-pro/paid-memberships-pro.php' => array(
					'label'       => '<a href="https://paidmembershipspro.com">Paid Memberships Pro</a>',
					'class'       => '',
					'min_version' => '2.0.0',		
				)
			)
		);

		LearnDash_Dependency_Check_LD_Paidmemberships::get_instance()->set_message(
			esc_html__( 'LearnDash LMS Paid Memberships Pro Add-on requires the following plugin(s) to be active:', 'learndash-paidmemberships' )
		);
	}

	/**
	 * Register action and filter hooks used in the plugin
	 * 
	 * @return void
	 */
	public static function hooks()
	{
		if ( ! LearnDash_Dependency_Check_LD_Paidmemberships::get_instance()->check_dependency_results() ) {
			return;
		}

		Learndash_Paidmemberships::includes();

		add_action( 'plugins_loaded', array( 'Learndash_Paidmemberships', 'i18nize' ) );
		add_action( 'admin_init', array( 'Learndash_Paidmemberships', 'register_meta_box' ) );
		add_action( 'save_post', array( 'Learndash_Paidmemberships', 'save_object_settings' ), 10, 3 );
		add_action( 'init', array( 'Learndash_Paidmemberships', 'update_plugin_data' ) );

		add_action( 'admin_head', [ 'Learndash_Paidmemberships', 'admin_header_scripts' ] );
		add_action( 'admin_footer', [ 'Learndash_Paidmemberships', 'admin_footer_scripts' ] );

		// Integration hooks
		add_action( 'pmpro_membership_level_after_other_settings', array( 'Learndash_Paidmemberships', 'output_level_settings' ) );
		add_action( 'pmpro_save_membership_level', array( 'Learndash_Paidmemberships', 'save_level_settings' ) );

		// Update course access when user change membership level
		add_action( 'pmpro_after_change_membership_level', array( 'Learndash_Paidmemberships', 'user_change_level' ), 10, 3 );
		// Email confirmation addon hook
		add_action( 'pmproec_after_validate_user', array( 'Learndash_Paidmemberships', 'update_access_on_email_confirmation' ), 10, 2 );
		add_filter( 'the_content', array( 'Learndash_Paidmemberships', 'object_email_confirmation_message' ), 10, 1 );
		// Update course access on member approval update
		add_action( 'update_user_meta', array( 'Learndash_Paidmemberships', 'update_access_on_approval' ), 10, 4 );
		// Update course access when an order is updated
		add_action( 'pmpro_updated_order', array( 'Learndash_Paidmemberships', 'update_object_access_on_order_update' ), 10, 1 );
		// Update course access when an order is deleted
		add_action( 'pmpro_delete_order', array( 'Learndash_Paidmemberships', 'remove_object_access_on_order_deletion' ), 10, 2 );
		// Update course access when an subscription is cancelled, failed, or payment refunded
		add_action( 'pmpro_subscription_expired', array( 'Learndash_Paidmemberships', 'remove_object_access_by_order' ), 10, 1 );
		add_action( 'pmpro_subscription_cancelled', array( 'Learndash_Paidmemberships', 'remove_object_access_by_order' ), 10, 1 );
		add_action( 'pmpro_subscription_recuring_stopped', array( 'Learndash_Paidmemberships', 'remove_object_access_by_order' ), 10, 1 );

		// Regain access to course when subscription recurring is restarted
		add_action( 'pmpro_subscription_recuring_restarted', array( 'Learndash_Paidmemberships', 'give_object_access_by_order' ), 10, 1 );

		// Remove membership access message if user already has access to a particular course
		add_filter( 'pmpro_has_membership_access_filter', array( 'Learndash_Paidmemberships', 'has_object_access' ), 99, 4 ); // priority 99 to make sure the value is returned
	}

	/**
	 * Register meta box
	 * 
	 * @return void
	 */
	public static function register_meta_box() 
	{
		add_meta_box( 'credits_meta', 'Require Membership', array( 'Learndash_Paidmemberships', 'output_object_settings' ), [ 'sfwd-courses', 'groups' ], 'side', 'low' );
	}

	/**
	 * Output course meta box
	 * 
	 * @return void
	 */
	public static function output_object_settings() 
	{
		global $post, $wpdb;

		if ( ! isset( $wpdb->pmpro_membership_levels ) ) {
			_e( 'Please enable Paid Memberships Pro plugin and create some levels', 'learndash-paidmemberships' );
			return;
		}

		$membership_levels = $wpdb->get_results( "SELECT * FROM {$wpdb->pmpro_membership_levels}", OBJECT );
		
		$object_id = $post->ID;
		$level_course_option = get_option( '_level_course_option' );
		$array_levels = explode( ',', $level_course_option[ $object_id ] );

		wp_nonce_field( 'ld_pmpro_save_object_metabox', 'ld_pmpro_nonce' );
		
		?>
		<div class="learndash">
			<select name="level-curso[]" id="learndash-pmp-level" class="select2" multiple="">
			<?php
			for ( $num_cursos = 0; $num_cursos < sizeof( $membership_levels ); $num_cursos++ )
			{
				$selected = '';
				for ( $tmp_array_levels = 0; $tmp_array_levels < sizeof( $array_levels ); $tmp_array_levels++ ) {
					if ( $array_levels[ $tmp_array_levels ] == $membership_levels[ $num_cursos ]->id ) {	
						$selected = 'selected';
					}
				}
				?>
				<!-- <p><input type="checkbox" name="level-curso[<?php echo $num_cursos ?>]" value="<?php echo $membership_levels[ $num_cursos ]->id; ?>" <?php echo $checked; ?>> <?php echo $membership_levels[ $num_cursos ]->name; ?></p> -->

				<option value="<?php echo esc_attr( $membership_levels[ $num_cursos ]->id ) ?>" <?php echo $selected; ?> ><?php echo $membership_levels[ $num_cursos ]->name; ?></option>
				<?php
			}
			?>
			</select>
		</div>
		<?php
	}

	/**
	 * Add scripts and styles to admin head
	 *
	 * @since 1.3.0
	 * @return void
	 */
	public static function admin_header_scripts() 
	{
		$screen = get_current_screen();

		if ( 
			( ! empty( $_GET['page'] ) && 'pmpro-membershiplevels' === $_GET['page'] && isset( $_GET['edit'] ) )
			|| $screen->post_type === 'groups'
			|| $screen->post_type === 'sfwd-courses'
		) {
			?>
			<style>
				.learndash .select2-container {
				    width: 100% !important;
				    border: 1px solid #ddd;
				    border-radius: 5px;
				}

				.learndash .select2-container ul {
				    width: 100%;
				}

				.learndash .select2-container li {
				    width: auto;
				    float: left;
				    border: 1px solid #ddd;
				    padding: 3px;
				    border-radius: 10px;
				    margin-right: 5px;
				}

				.learndash .select2-container li.select2-search {
				    clear: both;
				    border: none;
				    width: 99%;
				}

				.learndash .select2-container li.select2-search input {
				    width: 99% !important;
				    padding: 0 3px;
				    border: 1px solid #ddd;
				}

				.learndash .select2-container .select2-selection:focus {
				    outline: none;
				}

				/* Select2 Dropdown */
				.select2-container.select2-container--open .select2-dropdown {
				    border-color: #ddd;
				    border-top: 1px solid #ddd;
				}

				.select2-dropdown .select2-results__options {
				    max-height: 300px;
				    overflow: auto;
				}

				.select2-dropdown .select2-results__options .select2-results__option {
				    margin: 0;
				}

				.select2-dropdown .select2-results__options .select2-results__option[aria-selected="true"] {
				    background-color: #ddd;
				}
			</style>
			<?php
		}
	}

	/**
	 * Add scripts and styles to admin footer
	 *
	 * @since 1.3.0
	 * @return void
	 */
	public static function admin_footer_scripts()
	{
		$screen = get_current_screen();

		if (
			( ! empty( $_GET['page'] ) && 'pmpro-membershiplevels' === $_GET['page']  && isset( $_GET['edit'] ) )
			|| $screen->post_type === 'groups'
			|| $screen->post_type === 'sfwd-courses'
		) {
			?>
			<script type="text/javascript">
				jQuery( document ).ready( function( $ ) {
					$( '.select2' ).select2({
						closeOnSelect: false,
					});
				} );
			</script>
			<?php
		}
	}

	/**
	 * Output settings for membership level add/edit page
	 * 
	 * @return void
	 */
	public static function output_level_settings() 
	{
		$courses = self::get_courses();
		$groups  = self::get_groups();
		$object_levels = get_option( '_level_course_option' );
		$current_level = $_REQUEST['edit'];

		wp_nonce_field( 'ld_pmpro_save_level_settings', 'ld_pmpro_nonce' );
		?>		
		<h3 class="topborder"><?php _e( 'LearnDash', 'learndash-paidmemberships' );?></h3>
		<table class="form-table learndash">
			<tbody>
				<tr>
					<th scope="row" valign="top"><label><?php _e( 'Courses', 'learndash-paidmemberships' ) ?>:</label></th>
					<td>
						<select name="cursos[]" id="cursos" class="select2" multiple>
							<?php foreach ( $courses as $course ) : ?>		
								<option value="<?php echo esc_attr( $course->ID ) ?>" <?php if ( in_array( $current_level, explode( ',', @$object_levels[ $course->ID ] ) ) ) echo 'selected ' ?>><?php echo $course->post_title ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label><?php _e( 'Groups', 'learndash-paidmemberships' ) ?></label>
					</th>
					<td>
						<select name="learndash_groups[]" id="learndash-groups" class="select2" multiple>
							<?php foreach ( $groups as $group ) : ?>		
								<option value="<?php echo esc_attr( $group->ID ) ?>" <?php if ( in_array( $current_level, explode( ',', @$object_levels[ $group->ID ] ) ) ) echo 'selected ' ?>><?php echo $group->post_title ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Reassign list of users who have access to membership levels and re-enroll them to an object
	 * 
	 * @param  int    $object_id ID of a LearnDash course or group
	 * @param  array  $levels    Membership levels
	 * @return void
	 */
	public static function generate_access_list( $object_id, $levels ) 
	{
		global $wpdb;
		$levels = ! empty( $levels ) && is_array( $levels ) ? $levels : [];
		$levels = array_filter( $levels, function( $value ) { return is_numeric( $value ) && $value > 0; } );
		
		$levels_sql = implode( ',', $levels );
		$users = $wpdb->get_results( "SELECT * FROM {$wpdb->pmpro_memberships_users} WHERE membership_id IN ($levels_sql) AND status='active'" );
		$user_ids = array();
		foreach ( $users as $user ) {
			$user_ids[] = $user->user_id;			
		}

		Learndash_Paidmemberships::reassign_access_list( $object_id, $user_ids );
	}

	/**
	 * Re-enroll list of users to certain LearnDash course or group
	 * 
	 * @param  int    $course_id   ID of a LearnDash course
	 * @param  array  $access_list List of user ID
	 * @return void
	 */
	public static function reassign_access_list( $course_id, $access_list ) 
	{
		$meta = get_post_meta( $course_id, '_sfwd-courses', true );
		$old_access_list = explode( ',', $meta['sfwd-courses_course_access_list'] );
		foreach ( $access_list as $user_id ) {
			// Add user who was not in old list
			if ( ! in_array( $user_id, $old_access_list ) ) {
				ld_update_course_access( $user_id, $course_id );
			}
		}

		foreach ( $old_access_list as $user_id ) {
			// Remove user who was in old list but not in new list
			if ( ! in_array( $user_id, $access_list ) ) {
				ld_update_course_access( $user_id, $course_id, true );
			}
		}

		$meta = get_post_meta( $course_id, '_sfwd-courses', true );
		$level_course_option = get_option( '_level_course_option' );

		if ( ! empty( $level_course_option[ $course_id ] ) ) {
			$meta['sfwd-courses_course_price_type'] = 'closed';
		}

		update_post_meta( $course_id, '_sfwd-courses', $meta );
	}

	/**
	 * Save level edit page LearnDash settings
	 * 
	 * @param  int    $level_id Membership level ID
	 * @return void
	 */
	public static function save_level_settings( $level_id ) 
	{
		if ( isset( $_POST['ld_pmpro_nonce'] ) && ! wp_verify_nonce( $_POST['ld_pmpro_nonce'], 'ld_pmpro_save_level_settings' ) ) {
			return;
		}

		$new_courses = isset( $_POST['cursos'] ) ? array_map( 'intval', $_POST['cursos'] ) : array();
		$new_groups  = isset( $_POST['learndash_groups'] ) ? array_map( 'intval', $_POST['learndash_groups'] ) : array();
		$new_objects = array_merge( $new_courses, $new_groups );

		$courses = self::get_courses();
		$groups  = self::get_groups();
		$objects = array_merge( $courses, $groups );

		$object_levels = get_option( '_level_course_option' );
		$object_levels = ! empty( $object_levels ) && is_array( $object_levels ) ? $object_levels : [];

		foreach ( $objects as $object ) {
			$refresh = false;
			$levels = @$object_levels[ $object->ID ] ? explode( ',', @$object_levels[ $object->ID ] ) : array();

			// If the course is in the level and it wasn't add it
			if ( array_search( $object->ID, $new_objects ) !== FALSE && array_search( $level_id, $levels ) === FALSE ) {
				$refresh = true;
				$levels[] = $level_id;
				$object_levels[ $object->ID ] = implode( ',', $levels );

				self::insert_object( $level_id, $object->ID );
			}

			// When the object is not in the level but it was
			else if ( array_search( $object->ID, $new_objects ) === FALSE && array_search( $level_id, $levels ) !== FALSE ){				
				$refresh = true;
				$level_index = array_search( $level_id, $levels );
				unset( $levels[ $level_index ] );
				$object_levels[ $object->ID ] = implode( ',', $levels );

				self::delete_object_by_membership_id_object_id( $level_id, $object->ID );
			}

			if ( $refresh ) {
				self::generate_access_list( $object->ID, $levels );
			}
		}

		update_option( '_level_course_option' , $object_levels );
	}

	/**
	 * Save LearnDash course/group edit page LearnDash settings
	 * 
	 * @param  int    $post_id ID of a WP_Post
	 * @param  object $post    WP_Post object
	 * @param  bool   $update  Whether this action hook is an update
	 * @return void
	 */
	public static function save_object_settings( $post_id, $post, $update ) 
	{
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		if ( isset( $_POST['ld_pmpro_nonce'] ) && ! wp_verify_nonce( $_POST['ld_pmpro_nonce'], 'ld_pmpro_save_object_metabox' ) ) {
			return;
		}

		global $wpdb;

		if ( isset( $post->post_type ) && ( $post->post_type == 'sfwd-courses' || $post->post_type == 'groups' ) ) {
			$object_id = $post_id;
			
			$current_access_list = [];
			switch ( $post->post_type ) {
				case 'sfwd-courses':
					$current_access_list = learndash_get_users_for_course( $object_id );
					$current_access_list = is_a( $current_access_list, 'WP_User_Query' ) ? wp_list_pluck( $current_access_list->get_results(), 'ID' ) : [];
					break;

				case 'groups':
					$current_access_list = learndash_get_groups_user_ids( $object_id );
					break;
			}

			$level_course_option = get_option( '_level_course_option' );

			if ( isset( $_POST['level-curso'] ) && is_array( $_POST['level-curso'] ) ) {
				$_POST['level-curso'] = array_map( 'intval', $_POST['level-curso'] );

				$access_list = array();
				$levels_list = array();

				// Delete old course page ID from pmpro_membership_pages table
				self::delete_object_by_object_id( $object_id );

				foreach ( $_POST['level-curso'] as $membership_id ) {
					$users_pro_list = $wpdb->get_results( "SELECT * FROM {$wpdb->pmpro_memberships_users} WHERE membership_id = {$membership_id} AND status = 'active'", ARRAY_N );

					// Add new course page IDs to pmpro_membership_pages table
					self::insert_object( $membership_id, $object_id );

					foreach ( $users_pro_list as $user_pro ) {
						$access_list[] = $user_pro[1];			
					}

					$levels_list[] = $membership_id;			
				}

				$levels_list_tmp = implode( ',', $levels_list );
				$level_course_option[ $object_id ] = $levels_list_tmp;

				$access_list = array_merge( $current_access_list, $access_list );
				Learndash_Paidmemberships::reassign_access_list( $object_id, $access_list );			
			} else {
				// Delete old course page ID from pmpro_membership_pages table
				self::delete_object_by_object_id( $object_id );

				$level_course_option[ $object_id ] = '';
			}

			update_option( '_level_course_option', $level_course_option );
		}
	}

	/**
	 * Update user LearnDash course/group access based on his/her active levels
	 * 
	 * @param  int    $user_id WP_User ID
	 * @return void
	 */
	public static function update_user_level_object_access( $user_id )
	{
		$all_levels    = pmpro_getAllLevels();
		$active_levels = pmpro_getMembershipLevelsForUser( $user_id );

		$active_levels_ids = array();
		if ( is_array( $active_levels ) ) {
			foreach ( $active_levels as $active_level ) {
				$active_levels_ids[] = $active_level->id;
			}
		}

		if ( is_array( $all_levels ) ) {
			foreach ( $all_levels as $all_level ) {
				if ( in_array( $all_level->id, $active_levels_ids ) ) {
					continue;
				}

				Learndash_Paidmemberships::update_object_access( $all_level->id, $user_id, $remove = true );	
			}
		}

		foreach ( $active_levels_ids as $active_level_id ) {
			// enroll users
			Learndash_Paidmemberships::update_object_access( $active_level_id, $user_id );	
		}
	}

	/**
	 * Update user course access on user memberhip level change
	 * 
	 * @param  int $level_id        ID of new membership level
	 * @param  int $user_id      ID of a WP_User
	 * @param  int $cancel_level ID of old membership level
	 * @return void
	 */
	public static function user_change_level( $level_id, $user_id, $cancel_level ) 
	{
		// Add approval check if PMPro approval addon is active
		if ( class_exists( 'PMPro_Approvals' ) ) {
			if ( PMPro_Approvals::requiresApproval( $level_id ) && ! PMPro_Approvals::isApproved( $user_id, $level_id ) ) {
				return;
			}
		}

		if ( function_exists( 'pmproec_isEmailConfirmationLevel' ) && ! self::is_user_valid( $user_id ) ) {
			return;
		}

		self::update_user_level_object_access( $user_id );
	}

	/**
	 * Update user course access after email confirmation (requires email confirmation addon)
	 * 
	 * @param  int    $user_id  WP_User ID
	 * @param  string $validate User validation key or 'validated' if already validated
	 * @return void
	 */
	public static function update_access_on_email_confirmation( $user_id, $validate )
	{
		self::update_user_level_object_access( $user_id );
	}

	/**
	 * Show email confirmation message on course page
	 * 
	 * @param  string $content Post content
	 * @return string          Returned post content
	 */
	public static function object_email_confirmation_message( $content )
	{
		global $post;

		if ( 'sfwd-courses' == $post->post_type || 'groups' == $post->post_type ) {
			$membership_level   = pmpro_getMembershipLevelForUser();
			$membership_courses = get_option( '_level_course_option', [] );

			if ( isset( $membership_courses[ $post->ID ] ) && in_array( $membership_level->ID, explode( ',', $membership_courses[ $post->ID ] ) ) ) {
				$user = wp_get_current_user();

				if ( isset( $user->pmpro_email_confirmation_key ) && $user->pmpro_email_confirmation_key != 'validated' ) {
					$message = '<p>' . sprintf( _x( 'The courses will be activated as soon as you confirm your email address. <strong>Important! You must click on the confirmation URL sent to %s before you gain full access to your courses.</strong>', 'User email address', 'learndash-paidmemberships' ), $user->user_email ) . '</p>';

					return $message . $content;
				}
			}

		}

		return $content;
	}

	/**
	 * Update user course access on approval (requires approval add-on)
	 * 
	 * @param  int    $meta_id    ID of meta key
	 * @param  int    $object_id  ID of a WP_User
	 * @param  string $meta_key   Meta key
	 * @param  string $meta_value Meta value
	 * @return void
	 */
	public static function update_access_on_approval( $meta_id, $object_id, $meta_key, $meta_value ) 
	{
		preg_match( '/pmpro_approval_(\d+)/', $meta_key, $matches );

		if ( isset( $matches[0] ) && false !== strpos( $matches[0], 'pmpro_approval' ) ) {
			$level = $matches[1];
			if ( 'approved' == $meta_value['status'] ) {
				Learndash_Paidmemberships::update_object_access( $level, $object_id );
			} else {
				Learndash_Paidmemberships::update_object_access( $level, $object_id, $remove = true );
			}
		}
	}

	/**
	 * Get a membership level's associated courses
	 * 
	 * @param  int    $level ID of a membership level
	 * @return array         LearnDash courses and group IDs that belong to a level
	 */
	public static function get_level_objects( $level ) 
	{
		$objects_levels = get_option( '_level_course_option', array() );

		$objects = array();
		foreach ( $objects_levels as $object_id => $levels ) {
			$levels = explode( ',', $levels );
			if ( in_array( $level, $levels ) ) {
				$objects[] = $object_id;
			}
		}

		return $objects;
	}

	/**
	 * Check if user email has been validated. (Requires email confirmation addon)
	 * 
	 * @param  int    $user_id WP_User ID
	 * @return bool            True if validated|false otherwise
	 */
	public static function is_user_valid( $user_id )
	{
		$user = get_user_by( 'ID', $user_id );

		if ( isset( $user->pmpro_email_confirmation_key ) && $user->pmpro_email_confirmation_key !== 'validated' ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Update LearnDash object (course and group) access
	 * 
	 * @param  int  $level   ID of a membership level
	 * @param  int  $user_id ID of WP_User
	 * @param  boolean $remove  True to remove course access|false otherwise
	 * @return void
	 */
	public static function update_object_access( $level_id, $user_id, $remove = false ) 
	{
		$objects = Learndash_Paidmemberships::get_level_objects( $level_id );

		foreach ( $objects as $object_id ) {
			$post_type = get_post_type( $object_id );
			switch ( $post_type ) {
				case 'sfwd-courses':
					ld_update_course_access( $user_id, $object_id, $remove );
					break;
				
				case 'groups':
					ld_update_group_access( $user_id, $object_id, $remove );
					break;
			}
		}
	}

	/**
	 * Update course access when order is updated
	 * 
	 * @param  object $order Object of an order
	 * @return void
	 */
	public static function update_object_access_on_order_update( $order ) 
	{		
		switch ( $order->status ) {
			case 'success':
				self::give_object_access_by_order( $order );
				break;
			
			case 'cancelled':
			case 'error':
			case 'pending':
			case 'refunded':
			case 'review':
				self::remove_object_access_by_order( $order );
				break;
		}
	}

	/**
	 * Get LearnDash courses
	 *
	 * @since 1.3.0
	 * @return array Array of WP_Post objects
	 */
	public static function get_courses() 
	{
		return get_posts( [
			'post_type' => 'sfwd-courses',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		] );
	}

	/**
	 * Get LearnDash groups
	 *
	 * @since 1.3.0
	 * @return array Array of WP_Post objects
	 */
	public static function get_groups() 
	{
		return get_posts( [
			'post_type' => 'groups',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		] );
	}

	/**
	 * Remove user course access when an order is deleted
	 * 
	 * @param  int    $order_id ID of an order
	 * @param  object $order    Order object
	 * @return void
	 */
	public static function remove_object_access_on_order_deletion( $order_id, $order ) 
	{
		$level    = $order->getMembershipLevel();
		$user     = $order->getUser();

		self::update_object_access( $level->id, $user->ID, true );
	}

	/**
	 * Remove course access by given order
	 * 
	 * @param  object $order Order object
	 * @return void
	 */
	public static function remove_object_access_by_order( $order ) 
	{
		$level    = $order->getMembershipLevel();
		$user     = $order->getUser();

		self::update_object_access( $level->id, $user->ID, true );
	}

	/**
	 * Give LearnDash course and group access by given order
	 *
	 * @param object $order Order object
	 * @return void
	 */
	public static function give_object_access_by_order( $order ) 
	{
		$level = $order->getMembershipLevel();
		$user  = $order->getUser();

		self::update_object_access( $level->id, $user->ID );
	}

	/**
	 * Give user course access if he already has access to a particular course even though he's not a member of the course's membership
	 *
	 * @param bool  $hasaccess Whether user has access or not
	 * @param int   $mypost Course WP_Post
	 * @param int   $myuser WP_User
	 * @param array $mypost List of membership levels that protect this course
	 * @return boolean Returned $hasaccess
	 */
	public static function has_object_access( $hasaccess, $mypost, $myuser, $post_membership_levels ) 
	{
		if ( 'sfwd-courses' == $mypost->post_type || 'groups' == $mypost->post_type ) {
			$hasaccess = true;
		}

		return $hasaccess;
	}

	/**
	 * Get courses that belong to a certain level ID
	 * 
	 * @param  int    $level_id ID of a level
	 * @return array            Array of courses
	 */
	public static function get_objects_by_level_id( $level_id ) 
	{
		$objects_levels = get_option( '_level_course_option' );

		$objects = array();
		foreach ( $objects_levels as $object_id => $levels ) {
			$levels = explode( ',', $levels );
			if ( in_array( $level_id, $levels ) ) {
				$objects[] = $object_id;
			}
		}

		return $objects;
	}

	/**
	 * Add new course page IDs to pmpro_membership_pages table
	 * 
	 * @since  1.0.7
	 * @param  int    $membership_id 	ID of PMP membership level
	 * @param  int    $object_id        ID of a Learndash course or group
	 * @return void
	 */
	public static function insert_object( $membership_id, $object_id )
	{
		global $wpdb;

		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->pmpro_memberships_pages} WHERE membership_id = %d AND page_id = %d", $membership_id, $object_id ) );

		if ( ! $count ) {
			$wpdb->insert(
				"{$wpdb->pmpro_memberships_pages}",
				array( 
					'membership_id' => $membership_id,
					'page_id' => $object_id,
				),
				array( '%d', '%d' )
			);
		}
	}

	/**
	 * Delete course/group page ID from pmpro_membership_pages table
	 * 
	 * @since 1.0.7
	 * @param  int  $object_id ID of a LearnDash course or group
	 * @return void
	 */
	public static function delete_object_by_object_id( $object_id )
	{
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->pmpro_memberships_pages}",
			array( 'page_id' => $object_id ),
			array( '%d' )
		);
	}

	/**
	 * Delete course/group page ID from pmpro_membership_pages table
	 * 
	 * @since 1.0.7
	 * @param  int  $membership_id ID of a PMPro membership
	 * @param  int  $object_id     ID of a LearnDash course or group
	 * @return void
	 */
	public static function delete_object_by_membership_id_object_id( $membership_id, $object_id )
	{
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->pmpro_memberships_pages}",
			array( 'membership_id' => $membership_id, 'page_id' => $object_id ),
			array( '%d', '%d' )
		);
	}

	/**
	 * Update plugin data when the plugin is updated
	 *
	 * @return void
	 */
	public static function update_plugin_data()
	{
		$plugin_version = '1.1.0';
		$saved_version  = get_option( 'ld_pmpro_version' );

		if ( false === $saved_version || version_compare( $saved_version, $plugin_version, '<' ) ) {

			$lvl_courses = get_option( '_level_course_option' );

			if ( is_array( $lvl_courses ) ) {
				foreach ( $lvl_courses as $course_id => $level_string ) {
					self::delete_object_by_object_id( $course_id );

					if ( empty( trim( $level_string ) ) ) {
						continue;
					}

					$levels = explode( ',', $level_string );

					foreach ( $levels as $lvl ) {
						self::insert_object( $lvl, $course_id );
					}
				}
			}

			update_option( 'ld_pmpro_version', $plugin_version );
		}
	}
} // end class

Learndash_Paidmemberships::define_constants();
Learndash_Paidmemberships::check_dependency();
add_action( 'plugins_loaded', array( 'Learndash_Paidmemberships', 'hooks' ) );

} // end if class_exists