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

require_once("base/class-wpfront-options-base.php");

if (!class_exists('WPFront_Notification_Bar_Options')) {

    /**
     * Options class for WPFront Notification Bar plugin
     *
     * @author Syam Mohan <syam@wpfront.com>
     * @copyright 2013 WPFront.com
     */
    class WPFront_Notification_Bar_Options extends WPFront_Options_Base {

        function __construct($optionName, $pluginSlug) {
            parent::__construct($optionName, $pluginSlug);

            //add the options required for this plugin
            $this->addOption('enabled', 'bit', false)->label(__('Enabled', 'wpfront-notification-bar'));
            $this->addOption('preview_mode', 'bit', false)->label(__('Preview Mode', 'wpfront-notification-bar'));
            $this->addOption('debug_mode', 'bit', false)->label(__('Debug Mode', 'wpfront-notification-bar'));
            $this->addOption('position', 'int', 1, array($this, 'validate_1or2'))->label(__('Position', 'wpfront-notification-bar'));
            $this->addOption('height', 'int', 0, array($this, 'validate_zero_positive'))->label(__('Bar Height', 'wpfront-notification-bar'));
            $this->addOption('message', 'string', '')->label(__('Message Text', 'wpfront-notification-bar'));
            $this->addOption('message_process_shortcode', 'bit', false)->label(__('Process Shortcode', 'wpfront-notification-bar'));
            $this->addOption('display_after', 'int', 1, array($this, 'validate_zero_positive'))->label(__('Display After', 'wpfront-notification-bar'));
            $this->addOption('animate_delay', 'float', 0.5, array($this, 'validate_zero_positive'))->label(__('Animation Duration', 'wpfront-notification-bar'));
            $this->addOption('close_button', 'bool', false)->label(__('Display Close Button', 'wpfront-notification-bar'));
            $this->addOption('auto_close_after', 'int', 0, array($this, 'validate_zero_positive'))->label(__('Auto Close After', 'wpfront-notification-bar'));
            $this->addOption('display_button', 'bool', false)->label(__('Display Button', 'wpfront-notification-bar'));
            $this->addOption('button_text', 'string', '')->label(__('Button Text', 'wpfront-notification-bar'));
            $this->addOption('button_action', 'int', 1, array($this, 'validate_1or2'))->label(__('Button Action', 'wpfront-notification-bar'));
            $this->addOption('button_action_url', 'string', '')->label(__('Open URL:', 'wpfront-notification-bar'));
            $this->addOption('button_action_new_tab', 'bool', false)->label(__('Open URL in new tab/window', 'wpfront-notification-bar'));
            $this->addOption('button_action_url_nofollow', 'bool', false)->label(__('No follow link', 'wpfront-notification-bar'));
            $this->addOption('button_action_url_noreferrer', 'bool', false)->label(__('No referrer link', 'wpfront-notification-bar'));
            $this->addOption('button_action_url_noopener', 'bool', true)->label(__('No opener link', 'wpfront-notification-bar'));
            $this->addOption('button_action_javascript', 'string', '')->label(__('Execute JavaScript', 'wpfront-notification-bar'));
            $this->addOption('button_action_close_bar', 'bit', false)->label(__('Close Bar on Button Click', 'wpfront-notification-bar'));
            $this->addOption('display_shadow', 'bit', false)->label(__('Display Shadow', 'wpfront-notification-bar'));
            $this->addOption('fixed_position', 'bit', false)->label(__('Fixed at Position', 'wpfront-notification-bar'));
            $this->addOption('message_color', 'string', '#ffffff', array($this, 'validate_color'))->label(__('Message Text Color', 'wpfront-notification-bar'));
            $this->addOption('bar_from_color', 'string', '#888888', array($this, 'validate_color'))->label(__('From Color', 'wpfront-notification-bar'));
            $this->addOption('bar_to_color', 'string', '#000000', array($this, 'validate_color'))->label(__('To Color', 'wpfront-notification-bar'));
            $this->addOption('button_from_color', 'string', '#00b7ea', array($this, 'validate_color'))->label(__('From Color', 'wpfront-notification-bar'));
            $this->addOption('button_to_color', 'string', '#009ec3', array($this, 'validate_color'))->label(__('To Color', 'wpfront-notification-bar'));
            $this->addOption('button_text_color', 'string', '#ffffff', array($this, 'validate_color'))->label(__('Button Text Color', 'wpfront-notification-bar'));
            $this->addOption('display_pages', 'int', '1', array($this, 'validate_display_pages'))->label(__('Display on Pages', 'wpfront-notification-bar'));
            $this->addOption('include_pages', 'string', '', array($this, 'validate_include_exclude_pages'));
            $this->addOption('exclude_pages', 'string', '', array($this, 'validate_include_exclude_pages'));
            $this->addOption('display_open_button', 'bit', false)->label(__('Display Reopen Button', 'wpfront-notification-bar'));
            $this->addOption('reopen_button_image_url', 'string', '', array($this, 'validate_reopen_button_image_url'))->label(__('Reopen Button Image URL', 'wpfront-notification-bar'));
            $this->addOption('open_button_color', 'string', '#00b7ea')->label(__('Reopen Button Color', 'wpfront-notification-bar'));
            $this->addOption('keep_closed', 'bit', false)->label(__('Keep Closed', 'wpfront-notification-bar'));
            $this->addOption('keep_closed_for', 'int', 0, array($this, 'validate_zero_positive'))->label(__('Keep Closed For', 'wpfront-notification-bar'));
            $this->addOption('position_offset', 'int', 0)->label(__('Position Offset', 'wpfront-notification-bar'));
            $this->addOption('dynamic_css_use_url', 'bit', false)->label(__('Use Dynamic CSS URL', 'wpfront-notification-bar'));
            $this->addOption('custom_css', 'string', '')->label(__('Custom CSS', 'wpfront-notification-bar'));
            $this->addOption('close_button_color', 'string', '#555555', array($this, 'validate_color'))->label(__('Close Button Color', 'wpfront-notification-bar'));
            $this->addOption('close_button_color_hover', 'string', '#aaaaaa', array($this, 'validate_color'));
            $this->addOption('close_button_color_x', 'string', '#000000', array($this, 'validate_color'));
            $this->addOption('display_roles', 'int', '1', array($this, 'validate_display_roles'))->label(__('Display for User Roles', 'wpfront-notification-bar'));
            $this->addOption('include_roles', 'string', array(), array($this, 'validate_include_roles'));
            $this->addOption('display_scroll', 'bit', false)->label(__('Display on Scroll', 'wpfront-notification-bar'));
            $this->addOption('display_scroll_offset', 'int', '100', array($this, 'validate_zero_positive'))->label(__('Scroll Offset', 'wpfront-notification-bar'));
            $this->addOption('start_date', 'string', '', array($this, 'validate_date_range'))->label(__('Start Date & Time', 'wpfront-notification-bar'));
            $this->addOption('end_date', 'string', '', array($this, 'validate_date_range'))->label(__('End Date & Time', 'wpfront-notification-bar'));
            $this->addOption('start_time', 'string', '', array($this, 'validate_date_range'))->label(__('Start Time', 'wpfront-notification-bar'));
            $this->addOption('end_time', 'string', '', array($this, 'validate_date_range'))->label(__('End Time', 'wpfront-notification-bar'));
            $this->addOption('wp_emember_integration', 'bit', false);
            $this->addOption('landingpage_cookie_name', 'string', 'wpfront-notification-bar-landingpage', array($this, 'validate_landingpage_cookie_name'))->label(__('Landing Page Cookie Name', 'wpfront-notification-bar'));
            $this->addOption('keep_closed_cookie_name', 'string', 'wpfront-notification-bar-keep-closed', array($this, 'validate_keep_closed_cookie_name'))->label(__('Keep Closed Cookie Name', 'wpfront-notification-bar'));
            $this->addOption('hide_small_device', 'bit', false)->label(__('Hide on Small Devices', 'wpfront-notification-bar'));
            $this->addOption('small_device_width', 'int', 640, array($this, 'validate_zero_positive'))->label(__('Small Device Max Width', 'wpfront-notification-bar'));
            $this->addOption('hide_small_window', 'bit', false)->label(__('Hide on Small Window', 'wpfront-notification-bar'));
            $this->addOption('small_window_width', 'int', 640, array($this, 'validate_zero_positive'))->label(__('Small Window Max Width', 'wpfront-notification-bar'));
            $this->addOption('attach_on_shutdown', 'bit', false)->label(__('Attach on Shutdown', 'wpfront-notification-bar'));
            $this->addOption('last_saved', 'string', '0');
        }

        //validation function
        protected function validate_1or2($arg) {
            if ($arg < 1) {
                return 1;
            }

            if ($arg > 2) {
                return 2;
            }

            return $arg;
        }

        //validation function
        protected function validate_color($arg) {
            if (strlen($arg) != 7)
                return '#ffffff';

            if (strpos($arg, '#') != 0)
                return '#ffffff';

            return $arg;
        }

        protected function validate_display_pages($arg) {
            if ($arg < 1) {
                return 1;
            }

            if ($arg > 4) {
                return 4;
            }

            return $arg;
        }

        protected function validate_display_roles($arg) {
            if ($arg < 1) {
                return 1;
            }

            if ($arg > 4) {
                return 4;
            }

            return $arg;
        }

        protected function validate_include_roles($arg) {
            $obj = json_decode($arg);
            if (!is_array($obj))
                return array();
            return $obj;
        }

        protected function validate_date_range($arg) {
            if (trim($arg) == '')
                return NULL;

            if (($timestamp = strtotime($arg)) === false) {
                return NULL;
            }

            return $timestamp;
        }

        protected function validate_landingpage_cookie_name($arg) {
            if (trim($arg) == '') {
                return 'wpfront-notification-bar-landingpage';
            }

            return $arg;
        }

        protected function validate_keep_closed_cookie_name($arg) {
            if (trim($arg) == '') {
                return 'wpfront-notification-bar-keep-closed';
            }

            return $arg;
        }

        protected function validate_include_exclude_pages($pages) {
            if (strpos($pages, '.') === false) {
                return $pages;
            }

            $pages = explode(',', $pages);

            for ($i = 0; $i < count($pages); $i++) {
                $e = explode('.', $pages[$i]);
                if (count($e) > 1) {
                    $pages[$i] = $e[1];
                } else {
                    $pages[$i] = $e[0];
                }
            }

            return implode(',', $pages);
        }

        protected function validate_reopen_button_image_url($arg) {
            return trim($arg);
        }

    }

}

