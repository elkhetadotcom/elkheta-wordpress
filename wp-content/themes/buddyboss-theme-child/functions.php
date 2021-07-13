<?php
/**
 * @package BuddyBoss Child
 * The parent theme functions are located at /buddyboss-theme/inc/theme/functions.php
 * Add your own functions at the bottom of this file.
 */

/**
 * Sets up theme for translation
 *
 * @since BuddyBoss Child 1.0.0
 */

/****************************** THEME SETUP ******************************/


//function buddyboss_theme_child_languages()
// {
/**
 * Makes child theme available for translation.
 * Translations can be added into the /languages/ directory.
 */

// Translate text from the PARENT theme.
// load_theme_textdomain( 'buddyboss-theme', get_stylesheet_directory() . '/languages' );

// Translate text from the CHILD theme only.
// Change 'buddyboss-theme' instances in all child theme files to 'buddyboss-theme-child'.
// load_theme_textdomain( 'buddyboss-theme-child', get_stylesheet_directory() . '/languages' );

// }
//add_action( 'after_setup_theme', 'buddyboss_theme_child_languages' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since Boss Child Theme  1.0.0
 */
function buddyboss_theme_child_scripts_styles()
{
    // wp_enqueue_style( 'buddyboss-child-css', get_stylesheet_directory_uri().'/assets/css/custom.css', '', '1.0.0' );

    wp_enqueue_script('buddyboss-child-js', get_stylesheet_directory_uri() . '/assets/js/custom.js');

}

add_action('wp_enqueue_scripts', 'buddyboss_theme_child_scripts_styles');

add_filter('elementor_pro/utils/get_public_post_types', function ($post_types) {
    $post_types['sfwd-courses'] = LearnDash_Custom_Label::get_label('courses');
    $post_types['sfwd-lessons'] = LearnDash_Custom_Label::get_label('lessons');
    $post_types['sfwd-topic'] = LearnDash_Custom_Label::get_label('topics');
    $post_types['sfwd-quiz'] = LearnDash_Custom_Label::get_label('quizzes');

    return $post_types;
});


/****************************** CUSTOM FUNCTIONS ******************************/

/*  Complete profile & 2 days trial  */
add_action('init', 'for_subscription_trial');
function for_subscription_trial()
{
    if (is_user_logged_in()) {
        //if( current_user_can('editor') || current_user_can('administrator') ) {

        //}else{
        if (get_user_meta(get_current_user_id(), 'completed_profile', true) == 0) {
            if (str_replace(get_site_url() . "/", "", wp_guess_url()) == 'complete-profile') {

            } else {
                wp_redirect(get_permalink(49709));
                exit();
            }
        } else {
            if (str_replace(get_site_url() . "/", "", wp_guess_url()) == 'complete-profile') {
                wp_redirect(get_site_url());
                exit();
            } else {

            }
        }

        if (str_replace(get_site_url() . "/", "", wp_guess_url()) == 'login') {
            wp_redirect(get_site_url());
            exit();
        } else {

        }
        //}
    } else {
        if (str_replace(get_site_url() . "/", "", wp_guess_url()) == 'complete-profile') {
            wp_redirect(get_site_url());
            exit();
        } else {

        }
    }
}

add_action('user_register', 'update_user_redirect');
function update_user_redirect($user_id)
{
    update_user_meta($user_id, 'completed_profile', 0);
    update_user_meta($user_id, 'got_trial', 0);
}

/* update user after complete profile submission */
add_action('frm_after_create_entry', 'update_user_data', 30, 2);
add_action('frm_after_update_entry', 'update_user_data', 10, 2);
function update_user_data($entry_id, $form_id)
{
    global $wpdb;
    $user_id = get_current_user_id();
    if ($form_id == 8) {

        /*        $wpdb->get_results("SELECT * FROM wp_saffi_stats WHERE user_id = ".$user_id);
                if($wpdb->num_rows != 0){
                    $update = "UPDATE wp_saffi_stats SET remaining_points=remaining_points+50 WHERE user_id = ".$user_id;
                    $wpdb->query($update);
                }else{
                    $wpdb->insert('wp_saffi_stats', array(
                        'user_id' => $user_id,
                        'earned_points' => 0,
                        'pending_points' => 0,
                        'remaining_points' => 50,
                        'coversion_pending_points' => 0,
                        'rejected_points' => 0,
                        'total_signups' => 0,
                        'total_accepted' => 0,
                        'total_clicks' => 0,
                        'total_row_updates' => 0,
                    ));
                }*/
        $student_name = $_POST['item_meta'][101];
        $student_number = $_POST['item_meta'][102];
        $student_class = $_POST['item_meta'][103];
        $city = $_POST['item_meta'][104];
        $edu_admin = $_POST['item_meta'][106];
        $new_password = $_POST['item_meta'][105];
        $school_name = $_POST['item_meta'][107];
        update_user_meta($user_id, 'student_name', $student_name);
        update_user_meta($user_id, 'student_number', $student_number);
        update_user_meta($user_id, 'student_class', $student_class);
        update_user_meta($user_id, 'city', $city);
        update_user_meta($user_id, 'edu_admin', $edu_admin);
        update_user_meta($user_id, 'school_name', $school_name);
        wp_set_password($new_password, $user_id);

        $current_user = wp_get_current_user();
        $users_login = $current_user->user_email;

        $user_data = array(
            'user_login' => $users_login,
            'user_password' => $new_password,
            'remember' => true
        );
        $result = wp_signon($user_data);

        /*        $product_id = 0;
                if($student_class == 'الثالث الإعدادى عام'){
                    $product_id = 56;
                }else if($student_class == 'الثالث الاعدادى لغات'){
                    $product_id = 57;
                }else if($student_class == 'الاول الثانوى عام'){
                    $product_id = 58;
                }else if($student_class == 'الأول الثانوى لغات'){
                    $product_id = 59;
                }else if($student_class == 'الثانى الثانوى ادبى عام'){
                    $product_id = 60;
                }else if($student_class == 'الثانى الثانوى ادبى لغات'){
                    $product_id = 61;
                }else if($student_class == 'الثانى الثانوى علمى عام'){
                    $product_id = 62;
                }else if($student_class == 'الثانى الثانوى علمى لغات'){
                    $product_id = 63;
                }else if($student_class == 'الثالث الثانوى أدبى'){
                    $product_id = 64;
                }else if($student_class == 'الثالث الثانوى علمى رياضة عام'){
                    $product_id = 65;
                }else if($student_class == 'الثالث الثانوى علمى رياضة لغات'){
                    $product_id = 66;
                }else if($student_class == 'الثالث الثانوى علمى علوم عام'){
                    $product_id = 67;
                }else if($student_class == 'الثالث الثانوى علمى علوم لغات'){
                    $product_id = 68;
                }*/

        /* add free membership to new user */

        /*        pmpro_changeMembershipLevel($product_id, $user_id);
                $now = date('Y-m-d');
                $count_days = pmpro_getLevel($product_id)->expiration_number;
                $expiration_date = date('Y-m-d', strtotime($now. ' + '.$count_days.' days'));
                $sqlQuery = "UPDATE wp_pmpro_memberships_users SET enddate = '" . $expiration_date . "' WHERE status = 'active' AND membership_id = '" . intval(pmpro_getMembershipLevelForUser($user_id)->id) . "' AND user_id = '" .$user_id. "' LIMIT 1";
                $wpdb->query($sqlQuery);

        */


        update_user_meta($user_id, 'completed_profile', 1);
        update_user_meta($user_id, 'got_trial', 1);
        wp_redirect(get_site_url() . "/student-page");
        exit();
    }

    if ($form_id == 10) {
        $student_class = $_POST['item_meta'][112];
        update_user_meta($user_id, 'student_class', $student_class);
    }
}

/* filter for student cources based on his selection */
function filter_student_courses($query)
{
    $user_id = get_current_user_id();
    $current_cat = get_user_meta($user_id, 'student_class', true);
    $cats = array();

    if ($current_cat == 'الثالث الإعدادى عام') {
        $the_cat = 9237;
    } else if ($current_cat == 'الثالث الاعدادى لغات') {
        $the_cat = 9238;
    } else if ($current_cat == 'الاول الثانوى عام') {
        $the_cat = 9240;
    } else if ($current_cat == 'الأول الثانوى لغات') {
        $the_cat = 9241;
    } else if ($current_cat == 'الثانى الثانوى ادبى عام') {
        $the_cat = 9291;
    } else if ($current_cat == 'الثانى الثانوى ادبى لغات') {
        $the_cat = 9292;
    } else if ($current_cat == 'الثانى الثانوى علمى عام') {
        $the_cat = 9243;
    } else if ($current_cat == 'الثانى الثانوى علمى لغات') {
        $the_cat = 9244;
    } else if ($current_cat == 'الثالث الثانوى أدبى') {
        $the_cat = 9246;
    } else if ($current_cat == 'الثالث الثانوى علمى رياضة عام') {
        $the_cat = 9249;
    } else if ($current_cat == 'الثالث الثانوى علمى رياضة لغات') {
        $the_cat = 9250;
    } else if ($current_cat == 'الثالث الثانوى علمى علوم عام') {
        $the_cat = 9248;
    } else if ($current_cat == 'الثالث الثانوى علمى علوم لغات') {
        $the_cat = 9247;
    }
    $query->set('post_type', 'sfwd-courses');
    array_push($cats, $the_cat);
    $taxquery = array(
        array(
            'taxonomy' => 'ld_course_category',
            'field' => 'id',
            'terms' => $cats,
        )
    );
    $query->set('tax_query', $taxquery);
}

add_action('elementor/query/filter_student_courses', 'filter_student_courses', 10, 2);

// change home page for logged in users
function fn_set_context_based_page_on_front($value)
{

    if (!is_user_logged_in()) {
        return 278;
    }

    return 49736;
}

add_filter('pre_option_page_on_front', 'fn_set_context_based_page_on_front');

//allow SVG file uploads
function add_file_types_to_uploads($file_types)
{

    $new_filetypes = array();
    $new_filetypes['svg'] = 'image/svg';
    $file_types = array_merge($file_types, $new_filetypes);

    return $file_types;
}

add_action('upload_mimes', 'add_file_types_to_uploads');

/* google analytics */
add_action('wp_head', 'wpb_add_googleanalytics');
function wpb_add_googleanalytics()
{
    ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-138499970-1"></script>
    <script>window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', 'UA-138499970-1');</script>
    <?php
}

/* enable wocommerce session for all even admins */
add_action('woocommerce_init', 'enable_wc_session_cookie');
function enable_wc_session_cookie()
{
    if (isset(WC()->session) && !WC()->session->has_session())
        WC()->session->set_customer_session_cookie(true);
}

/* custom discount for saffi plugin */
/*add_action( 'woocommerce_cart_calculate_fees', 'custom_discount', 10, 1 );
function custom_discount( $cart ){
    $discount = do_shortcode("[saffi_user_stats show='remaining_points']");
    if($discount >= get_option('saffi_min_convertible_points', false)){
	    if( $discount > 0 ){
	        $cart->add_fee( sprintf( __("Wallet credit")), -$discount, true, '');
	    }
    }else{

    }
}*/
/* deduct the used credit from saffi plugin */
/*add_action( 'woocommerce_checkout_create_order', 'meta_from_session', 10, 2 );
function meta_from_session( $order, $data ) {
    global $wpdb;
    $order->update_meta_data( '_fee_total', WC()->session->cart_totals['fee_total'] );
    $order->update_meta_data( '_cart_contents_total', WC()->session->cart_totals['cart_contents_total'] );

    $sql = "UPDATE wp_saffi_stats SET remaining_points= remaining_points".WC()->session->cart_totals['fee_total']." WHERE user_id = ".get_current_user_id();
    $wpdb->query($sql);
}*/

/* meta data (_fee_total , _cart_contents_total) */


if (get_current_user_id() < 47933) {
    update_user_meta(get_current_user_id(), 'completed_profile', 1);
    //update_user_meta( $user_id, 'used_packages', '' );
}
if (get_current_user_id() == 88096) {

}

add_action('wp_ajax_get_reward', 'get_reward');

function get_reward()
{
    $rewards = get_option('saffi_min_convertible_points', false);
    if ($rewards == 0) {
        echo 'نفذت الهدايا';
    } else {
        $user_id = get_current_user_id();
        $got_reward = get_user_meta($user_id, 'got_reward', false);
        if ($got_reward) {
            echo 'لقد حصلتك علي هديتك بالفعل';
        } else {
            $total_points = do_shortcode("[saffi_user_stats show='total_signups']");
            if ($total_points >= 2) {
                global $wpdb;
                $student_class = get_user_meta($user_id, 'student_class', true);
                $product_id = 0;
                if ($student_class == 'الثالث الإعدادى عام') {
                    $product_id = 56;
                } else if ($student_class == 'الثالث الاعدادى لغات') {
                    $product_id = 57;
                } else if ($student_class == 'الاول الثانوى عام') {
                    $product_id = 58;
                } else if ($student_class == 'الأول الثانوى لغات') {
                    $product_id = 59;
                } else if ($student_class == 'الثانى الثانوى ادبى عام') {
                    $product_id = 60;
                } else if ($student_class == 'الثانى الثانوى ادبى لغات') {
                    $product_id = 61;
                } else if ($student_class == 'الثانى الثانوى علمى عام') {
                    $product_id = 62;
                } else if ($student_class == 'الثانى الثانوى علمى لغات') {
                    $product_id = 63;
                } else if ($student_class == 'الثالث الثانوى أدبى') {
                    $product_id = 64;
                } else if ($student_class == 'الثالث الثانوى علمى رياضة عام') {
                    $product_id = 65;
                } else if ($student_class == 'الثالث الثانوى علمى رياضة لغات') {
                    $product_id = 66;
                } else if ($student_class == 'الثالث الثانوى علمى علوم عام') {
                    $product_id = 67;
                } else if ($student_class == 'الثالث الثانوى علمى علوم لغات') {
                    $product_id = 68;
                }

                /* add free membership to user user */

                pmpro_changeMembershipLevel($product_id, $user_id);
                $now = date('Y-m-d');
                $count_days = 30;
                $expiration_date = date('Y-m-d', strtotime($now . ' + ' . $count_days . ' days'));
                $sqlQuery = "UPDATE wp_pmpro_memberships_users SET enddate = '" . $expiration_date . "' WHERE status = 'active' AND membership_id = '" . intval($product_id) . "' AND user_id = '" . $user_id . "' LIMIT 1";
                $wpdb->query($sqlQuery);
                $new_val = $rewards - 1;
                update_option('saffi_min_convertible_points', $new_val);
                update_user_meta($user_id, 'got_reward', true);
            } else {
                echo 'لم تقم بتسجيل 20 صديق';
            }
        }
    }
}


/* gifts script */
add_action('wp_head', 'wpb_add_scrip');
function wpb_add_scrip()
{
    ?>
    <script>
        jQuery(document).ready(function () {
            jQuery('#get_reward').on('click', function (e) {
                e.preventDefault();
                jQuery('#get_reward').attr('disabled', 'disabled');
                var data = {
                    'action': 'get_reward'
                };
                jQuery.post(ajaxurl, data, function (response) {
                    jQuery('#get_reward').removeAttr('disabled');
                    alert(response);
                    location.reload();
                });
            });
        });
    </script>
    <?php
}


add_shortcode('total_gifts', 'total_gifts_shortcode');
function total_gifts_shortcode($atts)
{
    return get_option('saffi_min_convertible_points', false);
}


/* cron job */
add_filter('autoptimize_filter_cachecheck_frequency', 'weekly');
function user_last_seen()
{
    if (is_user_logged_in()) {
        update_user_meta(get_current_user_id(), '_kh_last_seen', time());
    } else {
        return;
    }
}

add_action('wp_footer', 'user_last_seen', 10);


add_action('buddyboss_theme_before_header', 'fawry_track_campaign');
function fawry_track_campaign()
{
    if (!is_user_logged_in()) {
        return;
    }
    $is_paying = get_user_meta(get_current_user_id(), 'paying_customer', true);

    if (empty($is_paying) || 1 != $is_paying) {
        return;
    }

    $last_order = get_user_meta(get_current_user_id(), '_last_order', true);
    $order_type = get_post_meta($last_order, '_payment_method', true);

    if ($order_type != 'cowpay_payat_fawry') {
        return;
    }

    $payment_date = get_post_meta($last_order, '_date_paid', true);
    $last_visit = get_user_meta(get_current_user_id(), '_kh_last_seen', true);
    if (empty($last_visit)) {
        return;
    }

    if ($last_visit > $payment_date) {
        return;
    }
    ?>
    <link itemprop="url" rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

    <div id="fawry_tracking_campaign" class="modal fade fawry_tracking_campaign" data-backdrop="static"
         data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content"  style="border-radius: 10px;">
<!--                <div class="modal-header">-->
<!--                    <button type="button" class="close" data-dismiss="modal">&times;</button>-->
<!--                </div>-->
                <div class="modal-body">
                    <p style="text-align: center;direction: rtl;">شكرا لاشتراكك في موقع الخطة!</p>
                    <button style="margin: auto;display: block;border-radius: 9px;padding: 5px 20px;"  type="button" class="fawry_tracking_campaign_btn" id="fawry_tracking_campaign_btn" data-dismiss="modal">حسنا
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        // jQuery("#fawry_tracking_campaign").modal({
        //     backdrop: 'static',
        //     keyboard: false
        // });
        jQuery(document).ready(function () {
            jQuery("#fawry_tracking_campaign").modal('show');
        });
    </script>
    <?php
}