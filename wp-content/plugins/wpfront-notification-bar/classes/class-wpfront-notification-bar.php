<?php
/*
  WPFront Notification Bar Plugin
  Copyright (C) 2013, WPFront.com
  Website: wpfront.com
  Contact: syam@wpfront.com

  WPFront Notification Bar Plugin is distributed under the GNU General Public License, Version 3,
  June 2007. Copyright (C) 2007 Free Software Foundation, Inc., 51 Franklin
  St, Fifth Floor, Boston, MA 02110, USA

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
  ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
  ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

require_once("class-wpfront-notification-bar-options.php");
require_once(dirname(__DIR__) . "/templates/custom-css-template.php");

if (!class_exists('WPFront_Notification_Bar')) {

    /**
     * Main class of WPFront Notification Bar plugin
     *
     * @author Syam Mohan <syam@wpfront.com>
     * @copyright 2013 WPFront.com
     */
    class WPFront_Notification_Bar {

        //Constants
        const VERSION = '1.9.1.04012';
        const OPTIONS_GROUP_NAME = 'wpfront-notification-bar-options-group';
        const OPTION_NAME = 'wpfront-notification-bar-options';
        const PLUGIN_SLUG = 'wpfront-notification-bar';
        const PLUGIN_FILE = 'wpfront-notification-bar/wpfront-notification-bar.php';
        const PREVIEW_MODE_NAME = 'wpfront-notification-bar-preview-mode';
        //role consts
        const ROLE_NOROLE = 'wpfront-notification-bar-role-_norole_';
        const ROLE_GUEST = 'wpfront-notification-bar-role-_guest_';

        //Variables
        private $plugin_file;
        private $options;
        private $markupLoaded;
        private $scriptLoaded;
        private $enabled = null;
        private $logs = array();
        private static $instance = null;

        protected function __construct() {
            $this->markupLoaded = false;
            $this->min_file_suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
        }

        public static function Instance() {
            if (empty(self::$instance)) {
                self::$instance = new WPFront_Notification_Bar();
            }

            return self::$instance;
        }

        public function init($plugin_file) {
            $this->plugin_file = $plugin_file;

            add_action('plugins_loaded', array($this, 'plugins_loaded'));
            add_action('init', array($this, 'custom_css'));

            if (is_admin()) {
                add_action('admin_init', array($this, 'admin_init'));
                add_action('admin_menu', array($this, 'admin_menu'));
                add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);

                add_action('update_option_wpfront-notification-bar-options', array($this, 'settings_updated'), 10, 2);

                $this->add_activation_redirect();
            } else {
                add_action('template_redirect', array($this, 'set_landingpage_cookie'));

                add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
                add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            }
        }

        protected function add_activation_redirect() {
            add_action('activated_plugin', array($this, 'activated_plugin_callback'));
            add_action('admin_init', array($this, 'admin_init_callback'), 999999);
        }

        public function activated_plugin_callback($plugin) {
            if ($plugin !== self::PLUGIN_FILE) {
                return;
            }

            if (is_network_admin() || isset($_GET['activate-multi'])) {
                return;
            }

            $key = self::PLUGIN_SLUG . '-activation-redirect';
            add_option($key, true);
        }

        public function admin_init_callback() {
            $key = self::PLUGIN_SLUG . '-activation-redirect';

            if (get_option($key, false)) {
                delete_option($key);

                if (is_network_admin() || isset($_GET['activate-multi'])) {
                    return;
                }

                wp_safe_redirect(menu_page_url(self::PLUGIN_SLUG, FALSE));
            }
        }

        public function set_landingpage_cookie() {
            if (headers_sent()) {
                return;
            }

            if ($this->doing_ajax()) {
                return;
            }

            if (defined('WP_CLI') && WP_CLI) {
                return;
            }

            //for landing page tracking
            $cookie_name = $this->options->landingpage_cookie_name();
            if (!isset($_COOKIE[$cookie_name]) && !is_admin() && $this->options->display_pages() == 2 && $this->enabled()) {
                setcookie($cookie_name, 1, 0, '/', '', false, true);
            }
        }

        public function plugins_loaded() {
            load_plugin_textdomain(self::PLUGIN_SLUG, false, self::PLUGIN_SLUG . '/languages/');

            $this->options = new WPFront_Notification_Bar_Options(self::OPTION_NAME, self::PLUGIN_SLUG);

            if ($this->options->preview_mode() && isset($_GET[self::PREVIEW_MODE_NAME])) {
                $this->set_preview_mode();
                wp_redirect(home_url());
                exit;
            }
        }

        public function admin_init() {
            register_setting(self::OPTIONS_GROUP_NAME, self::OPTION_NAME);
        }

        public function admin_menu() {
            $page_hook_suffix = add_options_page(__('WPFront Notification Bar', 'wpfront-notification-bar'), __('Notification Bar', 'wpfront-notification-bar'), 'manage_options', self::PLUGIN_SLUG, array($this, 'options_page'));

            add_action('admin_print_scripts-' . $page_hook_suffix, array($this, 'enqueue_options_scripts'));
            add_action('admin_print_styles-' . $page_hook_suffix, array($this, 'enqueue_options_styles'));
        }

        //options page scripts
        public function enqueue_options_scripts() {
            wp_enqueue_media();

            $this->enqueue_scripts();

            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-datepicker');

            wp_enqueue_script('jquery-ui-timepicker', 'https://cdn.jsdelivr.net/npm/timepicker@1.13.14/jquery.timepicker.min.js', array('jquery', 'jquery-ui-core'), '1.13.14');

            $js = 'jquery-plugins/colorpicker/js/colorpicker.min.js';
            wp_enqueue_script('jquery.eyecon.colorpicker', plugins_url($js, $this->plugin_file), array('jquery', 'jquery-ui-core'), self::VERSION);

            wp_enqueue_script('wpfront-notification-bar-options', plugins_url("js/options{$this->min_file_suffix}.js", $this->plugin_file), array(), self::VERSION);
        }

        //options page styles
        public function enqueue_options_styles() {
            $this->enqueue_styles();

            $style = 'jquery-plugins/jquery-ui/smoothness/jquery-ui-1.10.4.custom.min.css';
            wp_enqueue_style('jquery.ui.smoothness.datepicker', plugins_url($style, $this->plugin_file), array(), self::VERSION);

            wp_enqueue_style('jquery.ui.timepicker', 'https://cdn.jsdelivr.net/npm/timepicker@1.13.14/jquery.timepicker.min.css', array(), '1.13.14');

            $style = 'jquery-plugins/colorpicker/css/colorpicker.min.css';
            wp_enqueue_style('jquery.eyecon.colorpicker.colorpicker', plugins_url($style, $this->plugin_file), array(), self::VERSION);

            $style = "css/options{$this->min_file_suffix}.css";
            wp_enqueue_style('wpfront-notification-bar-options', plugins_url($style, $this->plugin_file), array(), self::VERSION);
        }

        public function plugin_action_links($links, $file) {
            if ($file == self::PLUGIN_FILE) {
                $settings_link = '<a id="wpfront-notification-bar-settings-link" href="' . menu_page_url(self::PLUGIN_SLUG, false) . '">' . __('Settings', 'wpfront-notification-bar') . '</a>';
                array_unshift($links, $settings_link);
            }
            return $links;
        }

        //add scripts
        public function enqueue_scripts() {
            if ($this->options->debug_mode()) {
                add_action('wp_footer', array($this, 'write_debug_logs'), 99999);
                if ($this->options->attach_on_shutdown()) {
                    add_action('shutdown', array($this, 'write_debug_logs'), 99999);
                }
            }
            if ($this->enabled() == false) {
                return;
            }

            wp_enqueue_script('jquery');

            if ($this->options->keep_closed()) {
                wp_enqueue_script('js-cookie', plugins_url('jquery-plugins/js-cookie.min.js', $this->plugin_file), array(), '2.2.1');
            }

            wp_enqueue_script('wpfront-notification-bar', plugins_url("js/wpfront-notification-bar{$this->min_file_suffix}.js", $this->plugin_file), array('jquery'), self::VERSION);

            if ($this->options->position() == 1) {
                add_action('wp_body_open', array($this, 'write_markup'));
                add_action('wp_footer', array($this, 'write_markup'));//Callback hook 'wp_body_open' only works from WordPress 5.2
                add_action('admin_head', array($this, 'write_markup'));
            } else {
                add_action('wp_footer', array($this, 'write_markup'));
                add_action('admin_footer', array($this, 'write_markup'));
            }
            if ($this->options->attach_on_shutdown()) {
                add_action('shutdown', array($this, 'write_markup'));
            }

            $this->scriptLoaded = true;
        }

        //add styles
        public function enqueue_styles() {
            if ($this->enabled() == false) {
                return;
            }

            wp_enqueue_style('wpfront-notification-bar', plugins_url("css/wpfront-notification-bar{$this->min_file_suffix}.css", $this->plugin_file), array(), self::VERSION);

            if ($this->options->dynamic_css_use_url()) {
                wp_enqueue_style('wpfront-notification-bar-custom', $this->custom_css_url(), array('wpfront-notification-bar'), self::VERSION . '.' . $this->options->last_saved());
            }
        }

        private function custom_css_url() {
            return plugins_url("css/wpfront-notification-bar-custom-css/", $this->plugin_file);
        }

        public function custom_css() {
            if (strpos($_SERVER['REQUEST_URI'], '/css/wpfront-notification-bar-custom-css/') === false) {
                return;
            }

            header('Content-Type: text/css; charset=UTF-8');
            header('Expires: ' . gmdate('D, d M Y H:i:s ', strtotime('+1 year')) . 'GMT');

            $template = new WPFront_Notification_Bar_Custom_CSS_Template();
            $template->write($this);

            exit();
        }

        //creates options page
        public function options_page() {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.', 'wpfront-notification-bar'));
                return;
            }

            include(dirname($this->plugin_file) . '/templates/options-template.php');

            add_filter('admin_footer_text', array($this, 'admin_footer_text'));
        }

        //writes the html and script for the bar
        public function write_markup() {
            if ($this->markupLoaded) {
                return;
            }

            if (!$this->scriptLoaded) {
                return;
            }

            if ($this->doing_ajax()) {
                return;
            }

            if ($this->enabled()) {
                $this->log('Writing HTML template.');

                include(plugin_dir_path($this->plugin_file) . 'templates/notification-bar-template.php');

                $json = json_encode(array(
                    'position' => $this->options->position(),
                    'height' => $this->options->height(),
                    'fixed_position' => $this->options->fixed_position(),
                    'animate_delay' => $this->options->animate_delay(),
                    'close_button' => $this->options->close_button(),
                    'button_action_close_bar' => $this->options->button_action_close_bar(),
                    'auto_close_after' => $this->options->auto_close_after(),
                    'display_after' => $this->options->display_after(),
                    'is_admin_bar_showing' => is_admin_bar_showing(),
                    'display_open_button' => $this->options->display_open_button(),
                    'keep_closed' => $this->options->keep_closed(),
                    'keep_closed_for' => $this->options->keep_closed_for(),
                    'position_offset' => $this->options->position_offset(),
                    'display_scroll' => $this->options->display_scroll(),
                    'display_scroll_offset' => $this->options->display_scroll_offset(),
                    'keep_closed_cookie' => $this->options->keep_closed_cookie_name(),
                    'log' => $this->options->debug_mode()
                ));

                $this->write_load_script($json);
            }

            $this->markupLoaded = true;
        }

        private function write_load_script($json) {
            $this->log('Writing JS load script.');

            $this->write_debug_logs();

            if ($this->options->debug_mode()) {
                ?>
                <script type="text/javascript">
                    console.log('[WPFront Notification Bar] Starting JS scripts execution.');
                </script>
            <?php }
            ?>

            <script type="text/javascript">
                function __load_wpfront_notification_bar() {
                    if (typeof wpfront_notification_bar === "function") {
                        wpfront_notification_bar(<?php echo $json; ?>);
                    } else {
            <?php
            if ($this->options->debug_mode()) {
                echo 'console.log("[WPFront Notification Bar] Waiting for JS function \"wpfront_notification_bar\".");';
            }
            ?>
                        setTimeout(__load_wpfront_notification_bar, 100);
                    }
                }
                __load_wpfront_notification_bar();
            </script>
            <?php
        }

        public function write_debug_logs() {
            if (empty($this->logs)) {
                return;
            }

            if (!$this->options->debug_mode()) {
                return;
            }

            if ($this->doing_ajax()) {
                return;
            }

            $now = current_time('mysql');
            $now = strtotime($now);
            $now_str = date('Y-m-d h:i:s a', $now);

            echo "<!-- [WPFront Notification Bar] Page generated at $now_str. -->";
            echo '<script type="text/javascript">';
            echo "console.log('[WPFront Notification Bar] Page generated at $now_str.');";
            foreach ($this->logs as $message => $args) {
                if(empty($args)) {
                    printf("console.log('$message');");
                } else {
                    vprintf("console.log('$message');", $args);
                }
            }
            echo '</script>';

            $this->logs = array();
        }

        protected function get_message_text() {
            $message = $this->options->message();

            $message = apply_filters('wpfront_notification_bar_message', $message);

            if ($this->options->message_process_shortcode()) {
                $message = do_shortcode($message);
            }

            return $message;
        }

        protected function get_button_text() {
            $text = $this->options->button_text();

            $text = apply_filters('wpfront_notification_bar_button_text', $text);

            if ($this->options->message_process_shortcode()) {
                $text = do_shortcode($text);
            }

            return $text;
        }

        protected function get_filter_objects() {
            $objects = array();

            $objects['home'] = __('[Home Page]', 'wpfront-notification-bar');

            $pages = get_pages(array('number' => 50));
            foreach ($pages as $page) {
                $objects[$page->ID] = __('[Page]', 'wpfront-notification-bar') . ' ' . $page->post_title;
            }

            $posts = get_posts(array('number' => 50));
            foreach ($posts as $post) {
                $objects[$post->ID] = __('[Post]', 'wpfront-notification-bar') . ' ' . $post->post_title;
            }

//            $categories = get_categories();
//            foreach ($categories as $category) {
//                $objects['3.' . $category->cat_ID] = __('[Category]', 'wpfront-notification-bar') . ' ' . $category->cat_name;
//            }

            return $objects;
        }

        protected function get_role_objects() {
            $objects = array();
            global $wp_roles;

            $roles = $wp_roles->role_names;
            foreach ($roles as $role_name => $role_display_name) {
                $objects[$role_name] = $role_display_name;
            }

            return $objects;
        }

        protected function filter() {
            if (is_admin()) {
                $this->log('Running in wp-admin, ignoring filters.');
                return true;
            }

            $now = current_time('mysql');
            $now = strtotime($now);
            $now_str = date('Y-m-d h:i a', $now);
            $now = strtotime($now_str);

            $start_date = $this->options->start_date();
            if ($start_date != NULL) {
                $start_date = date('Y-m-d', $start_date);
                $start_time = $this->options->start_time();
                if ($start_time == NULL) {
                    $start_time = '12:00 am';
                } else {
                    $start_time = date('h:i a', $start_time);
                }
                $start_date_str = $start_date . ' ' . $start_time;
                $start_date = strtotime($start_date_str);

                if ($start_date > $now) {
                    $this->log('Filter: Start time is in future, disabling notification. Start time: %s[%s], Current time: %s[%s]', [$start_date, $start_date_str, $now, $now_str]);
                    return false;
                }
            }

            $end_date = $this->options->end_date();
            if ($end_date != NULL) {
                $end_date = date('Y-m-d', $end_date);
                $end_time = $this->options->end_time();
                if ($end_time == NULL) {
                    $end_time = '11:59 pm';
                } else {
                    $end_time = date('h:i a', $end_time);
                }

                $end_date_str = $end_date . ' ' . $end_time;
                $end_date = strtotime($end_date_str);

                if ($end_date < $now) {
                    $this->log('Filter: End time is in past, disabling notification. End time: %s[%s], Current time: %s[%s]', [$end_date, $end_date_str, $now, $now_str]);
                    return false;
                }
            }

            switch ($this->options->display_roles()) {
                case 1:
                    break;
                case 2:
                    if (!$this->is_user_logged_in()) {
                        $this->log('Filter: Display only for logged-in users. User is not logged-in, disabling notification.');
                        return false;
                    }
                    break;
                case 3:
                    if ($this->is_user_logged_in()) {
                        $this->log('Filter: Display only for guest users. User is logged-in, disabling notification.');
                        return false;
                    }
                    break;
                case 4:
                    global $current_user;
                    if (empty($current_user->roles)) {
                        $role = self::ROLE_GUEST;
                        if ($this->is_user_logged_in())
                            $role = self::ROLE_NOROLE;
                        if (!in_array($role, $this->options->include_roles())) {
                            $this->log('Filter: Display set for user roles. Current user role is not allowed, disabling notification.');
                            return false;
                        }
                    } else {
                        $display = false;
                        foreach ($current_user->roles as $role) {
                            if (in_array($role, $this->options->include_roles())) {
                                $display = true;
                                break;
                            }
                        }
                        if (!$display) {
                            $this->log('Filter: Display set for user roles. Current user role is not allowed, disabling notification.');
                            return false;
                        }
                    }
                    break;
            }

            switch ($this->options->display_pages()) {
                case 1:
                    return true;
                case 2:
                    if (isset($_COOKIE[$this->options->landingpage_cookie_name()])) {
                        $this->log('Filter: Display only on landing page. This is not the landing page, disabling notification.');
                        return false;
                    }

                    return true;
                case 3:
                case 4:
                    global $post;
                    if (empty($post)) {
                        $this->log('Filter: Global post object is empty.');
                    }
                    $ID = false;
                    if (is_home()) {
                        $ID = 'home';
                    } elseif (is_singular()) {
                        $ID = $post->ID;
                    }
                    if ($this->options->display_pages() == 3) {
                        if ($ID !== false) {
                            if ($this->filter_pages_contains($this->options->include_pages(), $ID) === false) {
                                $this->log('Filter: Display is set to include in pages. Current page ID is "%s", which is not included, disabling notification.', array($ID));
                                return false;
                            } else {
                                return true;
                            }
                        }
                        return false;
                    }
                    if ($this->options->display_pages() == 4) {
                        if ($ID !== false) {
                            if ($this->filter_pages_contains($this->options->exclude_pages(), $ID) === false) {
                                return true;
                            } else {
                                $this->log('Filter: Display is set to exclude in pages. Current page ID is "%s", which is excluded, disabling notification.', array($ID));
                                return false;
                            }
                        }
                        return true;
                    }
            }

            return true;
        }

        protected function is_user_logged_in() {
            $logged_in = is_user_logged_in();

            if ($this->options->wp_emember_integration() && function_exists('wp_emember_is_member_logged_in')) {
                $logged_in = $logged_in || wp_emember_is_member_logged_in();
            }

            return $logged_in;
        }

        public function filter_pages_contains($list, $key) {
            return strpos(',' . $list . ',', ',' . $key . ',');
        }

        protected function enabled() {
            if ($this->enabled !== null) {
                return $this->enabled;
            }

            if ($this->options->enabled()) {
                $this->log('Notification bar is enabled.');
                $this->enabled = $this->filter();
                return $this->enabled;
            }

            if ($this->is_preview_mode()) {
                $this->log('Notification bar is running in preview mode.');
                $this->enabled = $this->filter();
                return $this->enabled;
            }

            $this->log('Notification bar is not enabled.');
            $this->enabled = false;
            return false;
        }

        public function admin_footer_text($text) {
            $troubleshootingLink = sprintf('<a href="%s" target="_blank">%s</a>', 'https://wpfront.com/wordpress-plugins/notification-bar-plugin/wpfront-notification-bar-troubleshooting/', __('Troubleshooting', 'wpfront-notification-bar'));
            $settingsLink = sprintf('<a href="%s" target="_blank">%s</a>', 'https://wpfront.com/notification-bar-plugin-settings/', __('Settings Description', 'wpfront-notification-bar'));
            $reviewLink = sprintf('<a href="%s" target="_blank">%s</a>', 'https://wordpress.org/support/plugin/' . self::PLUGIN_SLUG . '/reviews/', __('Write a Review', 'wpfront-notification-bar'));
            $donateLink = sprintf('<a href="%s" target="_blank">%s</a>', 'https://wpfront.com/donate/', __('Buy me a Beer or Coffee', 'wpfront-notification-bar'));

            return sprintf('%s | %s | %s | %s | %s', $troubleshootingLink, $settingsLink, $reviewLink, $donateLink, $text);
        }

        protected function doing_ajax() {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                return TRUE;
            }

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                return TRUE;
            }

            if (!empty($_SERVER['REQUEST_URI']) && strtolower($_SERVER['REQUEST_URI']) == '/wp-admin/async-upload.php') {
                return TRUE;
            }

            if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
                return TRUE;
            }

            if (function_exists('wp_is_json_request') && wp_is_json_request()) {
                return TRUE;
            }

            if (function_exists('wp_is_jsonp_request') && wp_is_jsonp_request()) {
                return TRUE;
            }

            if (function_exists('wp_is_xml_request') && wp_is_xml_request()) {
                return TRUE;
            }

            if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
                return TRUE;
            }

            if (defined('WP_CLI') && WP_CLI) {
                return TRUE;
            }

            return FALSE;
        }

        public function settings_updated($old_value, $value) {
            if (empty($value['preview_mode'])) {
                $this->remove_preview_mode();
            } else {
                $this->set_preview_mode();
            }

            if (function_exists('w3tc_flush_posts')) {
                w3tc_flush_posts();
            }
        }

        private function set_preview_mode() {
            setcookie(self::PREVIEW_MODE_NAME, 1, 0, '/');
        }

        private function remove_preview_mode() {
            setcookie(self::PREVIEW_MODE_NAME, '', time() - 3600, '/');
        }

        private function is_preview_mode() {
            if ($this->options->preview_mode()) {
                $this->log('Preview mode is enabled.');

                if (empty($_COOKIE[self::PREVIEW_MODE_NAME])) {
                    $this->log('Preview mode flag is not set. Disabling preview mode.');
                    return false;
                }

                return true;
            }

            $this->log('Preview mode is not enabled.');
            return false;
        }

        private function log($message, $args = null) {
            $this->logs["[WPFront Notification Bar] $message"] = $args;
        }

        public function get_plugin_file() {
            return $this->plugin_file;
        }

        public function get_options() {
            return $this->options;
        }

        public function display_on_page_load() {
            if (!$this->options->display_scroll() && $this->options->display_after() == 0 && $this->options->animate_delay() == 0) {
                return true;
            }
            
            return false;
        }

    }

}
