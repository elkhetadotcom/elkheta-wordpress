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

/**
 * Template for WPFront Notification Bar Options
 *
 * @author Syam Mohan <syam@wpfront.com>
 * @copyright 2013 WPFront.com
 */
?>

<div class="wrap">
    <h2><?php echo __('WPFront Notification Bar Settings', 'wpfront-notification-bar'); ?></h2>
    <div id="wpfront-notification-bar-options" class="inside">
        <form id="wpfront-notification-bar-options-form" method="post" action="options.php">
            <?php
            settings_fields(WPFront_Notification_Bar::OPTIONS_GROUP_NAME);
            do_settings_sections('wpfront-notification-bar');

            if ((isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') || (isset($_GET['updated']) && $_GET['updated'] == 'true')) {
                ?>
                <div class="updated">
                    <p>
                        <strong><?php echo __('If you have a caching plugin, clear the cache for the new settings to take effect.', 'wpfront-notification-bar'); ?></strong>
                    </p>
                </div>
                <?php
            }
            ?>
            <h3><?php echo __('Display', 'wpfront-notification-bar'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php echo $this->options->enabled_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->enabled_name(); ?>" <?php echo $this->options->enabled() ? 'checked' : ''; ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->preview_mode_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->preview_mode_name(); ?>" <?php echo $this->options->preview_mode() ? 'checked' : ''; ?> />
                        <?php
                        if ($this->options->preview_mode()) {
                            $url = add_query_arg('wpfront-notification-bar-preview-mode', '1', home_url());
                            ?>
                            <span class="description"><a target="_blank" rel="noopener" href="<?php echo $url; ?>"><?php echo $url; ?></a></span>
                            <?php
                        } else {
                            ?>
                            <span class="description"><?php echo __('[You can test the notification bar without enabling it.]', 'wpfront-notification-bar'); ?></span>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->debug_mode_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->debug_mode_name(); ?>" <?php echo $this->options->debug_mode() ? 'checked' : ''; ?> />
                        <span class="description">
                            <?php echo __('[Enable to see logs in browser.]', 'wpfront-notification-bar'); ?>
                            <a target="_blank" rel="noopener" href="https://wpfront.com/wordpress-plugins/notification-bar-plugin/wpfront-notification-bar-troubleshooting/"><?php echo __('[How to?]', 'wpfront-notification-bar'); ?></a>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->position_label(); ?>
                    </th>
                    <td>
                        <select name="<?php echo $this->options->position_name(); ?>">
                            <option value="1" <?php echo $this->options->position() == '1' ? 'selected' : ''; ?>><?php echo __('Top', 'wpfront-notification-bar'); ?></option>
                            <option value="2" <?php echo $this->options->position() == '2' ? 'selected' : ''; ?>><?php echo __('Bottom', 'wpfront-notification-bar'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->fixed_position_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->fixed_position_name(); ?>" <?php echo $this->options->fixed_position() ? 'checked' : ''; ?> />&#160;<span class="description"><?php echo __('[Sticky Bar, bar will stay at position regardless of scrolling.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->display_scroll_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->display_scroll_name(); ?>" <?php echo $this->options->display_scroll() ? 'checked' : ''; ?> />&#160;<span class="description"><?php echo __('[Displays the bar on window scroll.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->display_scroll_offset_label(); ?>
                    </th>
                    <td>
                        <input class="seconds" name="<?php echo $this->options->display_scroll_offset_name(); ?>" value="<?php echo $this->options->display_scroll_offset(); ?>" />&#160;<?php echo __('px', 'wpfront-notification-bar'); ?>&#160;<span class="description">[<?php echo __('Number of pixels to be scrolled before the bar appears.', 'wpfront-notification-bar'); ?>]</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->height_label(); ?>
                    </th>
                    <td>
                        <input class="seconds" name="<?php echo $this->options->height_name(); ?>" value="<?php echo $this->options->height(); ?>" />&#160;<?php echo __('px', 'wpfront-notification-bar'); ?>&#160;<span class="description">[<?php echo __('Set 0px to auto fit contents.', 'wpfront-notification-bar'); ?>]</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->position_offset_label(); ?>
                    </th>
                    <td>
                        <input class="seconds" name="<?php echo $this->options->position_offset_name(); ?>" value="<?php echo $this->options->position_offset(); ?>" />&#160;<?php echo __('px', 'wpfront-notification-bar'); ?>&#160;<span class="description">[<?php echo __('(Top bar only) If you find the bar overlapping, try increasing this value. (eg. WordPress 3.8 Twenty Fourteen theme, set 48px)', 'wpfront-notification-bar'); ?>]</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->display_after_label(); ?>
                    </th>
                    <td>
                        <input class="seconds" name="<?php echo $this->options->display_after_name(); ?>" value="<?php echo $this->options->display_after(); ?>" />&#160;<?php echo __('second(s)', 'wpfront-notification-bar'); ?>&#160;<span class="description">[<?php echo __('Set 0 second(s) to display immediately. Does not work in "Display on Scroll" mode.', 'wpfront-notification-bar'); ?>]</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->animate_delay_label(); ?>
                    </th>
                    <td>
                        <input class="seconds" name="<?php echo $this->options->animate_delay_name(); ?>" value="<?php echo $this->options->animate_delay(); ?>" />&#160;<?php echo __('second(s)', 'wpfront-notification-bar'); ?>&#160;<span class="description">[<?php echo __('Set 0 second(s) for no animation.', 'wpfront-notification-bar'); ?>]</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->close_button_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->close_button_name(); ?>" <?php echo $this->options->close_button() ? 'checked' : ''; ?> />&#160;<span class="description"><?php echo __('[Displays a close button at the top right corner of the bar.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->auto_close_after_label(); ?>
                    </th>
                    <td>
                        <input class="seconds" name="<?php echo $this->options->auto_close_after_name(); ?>" value="<?php echo $this->options->auto_close_after(); ?>" />&#160;<?php echo __('second(s)', 'wpfront-notification-bar'); ?>&#160;<span class="description">[<?php echo __('Set 0 second(s) to disable auto close. Do not work in "Display on Scroll" mode.', 'wpfront-notification-bar'); ?>]</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->display_shadow_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->display_shadow_name(); ?>" <?php echo $this->options->display_shadow() ? 'checked' : ''; ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->display_open_button_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->display_open_button_name(); ?>" <?php echo $this->options->display_open_button() ? 'checked' : ''; ?> />&#160;<span class="description">[<?php echo __('A reopen button will be displayed after the bar is closed.', 'wpfront-notification-bar'); ?>]</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->reopen_button_image_url_label(); ?>
                    </th>
                    <td>
                        <input id="reopen-button-image-url" class="url" name="<?php echo $this->options->reopen_button_image_url_name(); ?>" value="<?php echo $this->options->reopen_button_image_url(); ?>"/>
                        <input type="button" id="media-library-button" class="button" value="<?php echo __('Media Library', 'wpfront-notification-bar'); ?>" />
                        <br />
                        <span class="description"><?php echo esc_html(__('[Set empty value to use default images.]')); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->keep_closed_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->keep_closed_name(); ?>" <?php echo $this->options->keep_closed() ? 'checked' : ''; ?> />&#160;<span class="description">[<?php echo __('Once closed, bar will display closed on other pages.', 'wpfront-notification-bar'); ?>]</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->keep_closed_for_label(); ?>
                    </th>
                    <td>
                        <input class="seconds" name="<?php echo $this->options->keep_closed_for_name(); ?>" value="<?php echo $this->options->keep_closed_for(); ?>" />&#160;<?php echo __('day(s)', 'wpfront-notification-bar'); ?>&#160;<span class="description">[<?php echo __('Bar will be kept closed for the number of days specified from last closed date.', 'wpfront-notification-bar'); ?>]</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->keep_closed_cookie_name_label(); ?>
                    </th>
                    <td>
                        <input class="cookie-name" name="<?php echo $this->options->keep_closed_cookie_name_name(); ?>" value="<?php echo $this->options->keep_closed_cookie_name(); ?>" />
                        <span><?php echo __('Cookie name used to mark keep closed days. Changing this value will allow you to bypass "Keep Closed For" days and show the notification again.', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->hide_small_device_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->hide_small_device_name(); ?>" <?php echo $this->options->hide_small_device() ? "checked" : ""; ?> />
                        <span class="description"><?php echo __('[Notification bar will be hidden on small devices when the width matches.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->small_device_width_label(); ?>
                    </th>
                    <td>
                        <input class="pixels" name="<?php echo $this->options->small_device_width_name(); ?>" value="<?php echo $this->options->small_device_width(); ?>" />px 
                        <span class="description"><?php echo __('[Notification bar will be hidden on devices with lesser or equal width.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->hide_small_window_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->hide_small_window_name(); ?>" <?php echo $this->options->hide_small_window() ? "checked" : ""; ?> />
                        <span class="description"><?php echo __('[Notification bar will be hidden on broswer window when the width matches.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->small_window_width_label(); ?>
                    </th>
                    <td>
                        <input class="pixels" name="<?php echo $this->options->small_window_width_name(); ?>" value="<?php echo $this->options->small_window_width(); ?>" />px 
                        <span class="description"><?php echo __('[Notification bar will be hidden on browser window with lesser or equal width.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->attach_on_shutdown_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->attach_on_shutdown_name(); ?>" <?php echo $this->options->attach_on_shutdown() ? 'checked' : ''; ?> />
                        <span class="description"><?php echo __('[Enable as a last resort if the notification bar is not working. This could create compatibility issues.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
            </table>

            <h3><?php echo __('Content', 'wpfront-notification-bar'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php echo $this->options->message_label(); ?>
                    </th>
                    <td>
                        <textarea rows="5" cols="75" name="<?php echo $this->options->message_name(); ?>"><?php echo $this->options->message(); ?></textarea>
                        <br />
                        <span class="description"><?php echo esc_html(__('[HTML tags are allowed. e.g. Add <br /> for break.]')); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->message_process_shortcode_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->message_process_shortcode_name(); ?>" <?php echo $this->options->message_process_shortcode() ? 'checked' : ''; ?> />&#160;<span class="description"><?php echo __('[Processes shortcodes in message text.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->display_button_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->display_button_name(); ?>" <?php echo $this->options->display_button() ? 'checked' : ''; ?> />&#160;<span class="description"><?php echo __('[Displays a button next to the message.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->button_text_label(); ?>
                    </th>
                    <td>
                        <input name="<?php echo $this->options->button_text_name(); ?>" value="<?php echo $this->options->button_text(); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->button_action_label(); ?>
                    </th>
                    <td>
                        <label>
                            <input type="radio" name="<?php echo $this->options->button_action_name(); ?>" value="1" <?php echo $this->options->button_action() == 1 ? 'checked' : ''; ?> />
                            <span><?php echo $this->options->button_action_url_label(); ?></span>
                        </label>
                        <input class="URL" name="<?php echo $this->options->button_action_url_name(); ?>" value="<?php echo $this->options->button_action_url(); ?>" />
                        <br />
                        <label>
                            <input type="checkbox" name="<?php echo $this->options->button_action_new_tab_name(); ?>" <?php echo $this->options->button_action_new_tab() ? 'checked' : ''; ?> />
                            <span><?php echo $this->options->button_action_new_tab_label() . '.'; ?></span>
                        </label>
                        <br />
                        <label>
                            <input type="checkbox" name="<?php echo $this->options->button_action_url_nofollow_name(); ?>" <?php echo $this->options->button_action_url_nofollow() ? 'checked' : ''; ?> />
                            <span><?php echo $this->options->button_action_url_nofollow_label() . '.'; ?></span>
                        </label>
                        <span class="description"><?php echo __('[rel="nofollow"]', 'wpfront-notification-bar'); ?></span>
                        <br />
                        <label>
                            <input type="checkbox" name="<?php echo $this->options->button_action_url_noreferrer_name(); ?>" <?php echo $this->options->button_action_url_noreferrer() ? 'checked' : ''; ?> />
                            <span><?php echo $this->options->button_action_url_noreferrer_label() . '.'; ?></span>
                        </label>
                        <span class="description"><?php echo __('[rel="noreferrer"]', 'wpfront-notification-bar'); ?></span>
                        <br />
                        <label>
                            <input id="chk_button_action_url_noopener" type="checkbox" <?php echo $this->options->button_action_url_noopener() ? 'checked' : ''; ?> />
                            <input type="hidden" id="txt_button_action_url_noopener" name="<?php echo $this->options->button_action_url_noopener_name(); ?>" value="<?php echo $this->options->button_action_url_noopener() ? '1' : '0'; ?>" />
                            <span><?php echo $this->options->button_action_url_noopener_label() . '.'; ?></span>
                        </label>
                        <span class="description"><?php echo __('[rel="noopener", used when URL opens in new tab/window. Recommended value is "on", unless it affects your functionality.]', 'wpfront-notification-bar'); ?></span>
                        <br />
                        <label>
                            <input type="radio" name="<?php echo $this->options->button_action_name(); ?>" value="2" <?php echo $this->options->button_action() == 2 ? 'checked' : ''; ?> />
                            <span><?php echo $this->options->button_action_javascript_label(); ?></span>
                        </label>
                        <br />
                        <textarea rows="5" cols="75" name="<?php echo $this->options->button_action_javascript_name(); ?>"><?php echo $this->options->button_action_javascript(); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->button_action_close_bar_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->button_action_close_bar_name(); ?>" <?php echo $this->options->button_action_close_bar() ? 'checked' : ''; ?> />
                    </td>
                </tr>
            </table>

            <h3><?php echo __('Filter', 'wpfront-notification-bar'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php echo $this->options->start_date_label(); ?>
                    </th>
                    <td>
                        <input class="date" name="<?php echo $this->options->start_date_name(); ?>" value="<?php echo $this->options->start_date() == NULL ? '' : date('Y-m-d', $this->options->start_date()); ?>" />
                        <input class="time" name="<?php echo $this->options->start_time_name(); ?>" value="<?php echo $this->options->start_time() == NULL ? '' : date('h:i a', $this->options->start_time()); ?>" />
                        &#160;
                        <span class="description"><?php echo __('[YYYY-MM-DD] [hh:mm ap]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->end_date_label(); ?>
                    </th>
                    <td>
                        <input class="date" name="<?php echo $this->options->end_date_name(); ?>" value="<?php echo $this->options->end_date() == NULL ? '' : date('Y-m-d', $this->options->end_date()); ?>" />
                        <input class="time" name="<?php echo $this->options->end_time_name(); ?>" value="<?php echo $this->options->end_time() == NULL ? '' : date('h:i a', $this->options->end_time()); ?>" />
                        &#160;
                        <span class="description"><?php echo __('[YYYY-MM-DD] [hh:mm ap]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->display_pages_label(); ?>
                    </th>
                    <td>
                        <label>
                            <input type="radio" name="<?php echo $this->options->display_pages_name(); ?>" value="1" <?php echo $this->options->display_pages() == 1 ? 'checked' : ''; ?> />
                            <span><?php echo __('All pages.', 'wpfront-notification-bar'); ?></span>
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="<?php echo $this->options->display_pages_name(); ?>" value="2" <?php echo $this->options->display_pages() == 2 ? 'checked' : ''; ?> />
                            <span><?php echo __('Only in landing page.', 'wpfront-notification-bar'); ?></span>&#160;<span class="description"><?php echo __('[The first page they visit on your website.]', 'wpfront-notification-bar'); ?></span>
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="<?php echo $this->options->display_pages_name(); ?>" value="3" <?php echo $this->options->display_pages() == 3 ? 'checked' : ''; ?> />
                            <span><?php echo __('Include in following pages [Use the textbox below to specify the post IDs as a comma separated list.]', 'wpfront-notification-bar'); ?></span>
                        </label>
                        <input class="post-id-list" name="<?php echo $this->options->include_pages_name(); ?>" value="<?php echo $this->options->include_pages(); ?>" />
                        <div class="pages-selection">
                            <?php
                            $objects = $this->get_filter_objects();
                            foreach ($objects as $key => $value) {
                                ?>
                                <div class="page-div">
                                    <label>
                                        <input type="checkbox" value="<?php echo $key; ?>" <?php echo $this->filter_pages_contains($this->options->include_pages(), $key) === FALSE ? '' : 'checked'; ?> />
                                        <?php echo $value; ?>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <label>
                            <input type="radio" name="<?php echo $this->options->display_pages_name(); ?>" value="4" <?php echo $this->options->display_pages() == 4 ? 'checked' : ''; ?> />
                            <span><?php echo __('Exclude in following pages [Use the textbox below to specify the post IDs as a comma separated list.]', 'wpfront-notification-bar'); ?></span>
                        </label>
                        <input class="post-id-list" name="<?php echo $this->options->exclude_pages_name(); ?>" value="<?php echo $this->options->exclude_pages(); ?>" />
                        <div class="pages-selection">
                            <?php
                            $objects = $this->get_filter_objects();
                            foreach ($objects as $key => $value) {
                                ?>
                                <div class="page-div">
                                    <label>
                                        <input type="checkbox" value="<?php echo $key; ?>" <?php echo $this->filter_pages_contains($this->options->exclude_pages(), $key) === FALSE ? '' : 'checked'; ?> />
                                        <?php echo $value; ?>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <span><?php echo __('Will only display 50 posts and 50 pages to reduce load. Use the PostIDs textbox to apply this setting on other Posts/Pages/CPTs.', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->landingpage_cookie_name_label(); ?>
                    </th>
                    <td>
                        <input class="cookie-name" name="<?php echo $this->options->landingpage_cookie_name_name(); ?>" value="<?php echo $this->options->landingpage_cookie_name(); ?>" />
                        <span><?php echo __('Cookie name used to mark landing page. Useful when you have multiple WordPress installs under same domain.', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->display_roles_label(); ?>
                    </th>
                    <td>
                        <label>
                            <input type="radio" name="<?php echo $this->options->display_roles_name(); ?>" value="1" <?php echo $this->options->display_roles() == 1 ? 'checked' : ''; ?> />
                            <span><?php echo __('All users.', 'wpfront-notification-bar'); ?></span>
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="<?php echo $this->options->display_roles_name(); ?>" value="2" <?php echo $this->options->display_roles() == 2 ? 'checked' : ''; ?> />
                            <span><?php echo __('All logged in users.', 'wpfront-notification-bar'); ?></span>
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="<?php echo $this->options->display_roles_name(); ?>" value="3" <?php echo $this->options->display_roles() == 3 ? 'checked' : ''; ?> />
                            <span><?php echo __('Guest users. [Non-logged in users]', 'wpfront-notification-bar'); ?></span>
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="<?php echo $this->options->display_roles_name(); ?>" value="4" <?php echo $this->options->display_roles() == 4 ? 'checked' : ''; ?> />
                            <span><?php echo __('For following user roles', 'wpfront-notification-bar'); ?></span>&nbsp;<span>[<a target="_blank" rel="noopener" href="https://wpfront.com/nbtoure"><?php echo __('Manage Roles', 'wpfront-notification-bar'); ?>]</a></span>
                        </label>
                        <br />
                        <div class="roles-selection">
                            <input type="hidden" name="<?php echo $this->options->include_roles_name(); ?>" value="<?php echo htmlentities(json_encode($this->options->include_roles())); ?>" />
                            <?php
                            foreach ($this->get_role_objects() as $key => $value) {
                                ?>
                                <div class="role-div">
                                    <label>
                                        <input type="checkbox" value="<?php echo $key; ?>" <?php echo in_array($key, $this->options->include_roles()) === FALSE ? '' : 'checked'; ?> />
                                        <?php echo $value; ?>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="role-div">
                                <label>
                                    <input type="checkbox" value="<?php echo WPFront_Notification_Bar::ROLE_NOROLE; ?>" <?php echo in_array(WPFront_Notification_Bar::ROLE_NOROLE, $this->options->include_roles()) === FALSE ? '' : 'checked'; ?> />
                                    <?php echo __('[No Role]', 'wpfront-notification-bar'); ?>
                                </label>
                            </div>
                            <div class="role-div">
                                <label>
                                    <input type="checkbox" value="<?php echo WPFront_Notification_Bar::ROLE_GUEST; ?>" <?php echo in_array(WPFront_Notification_Bar::ROLE_GUEST, $this->options->include_roles()) === FALSE ? '' : 'checked'; ?> />
                                    <?php echo __('[Guest]', 'wpfront-notification-bar'); ?>
                                </label>
                            </div>
                        </div>
                        <label>
                            <input type="checkbox" name="<?php echo $this->options->wp_emember_integration_name(); ?>" <?php echo $this->options->wp_emember_integration() ? 'checked' : ''; ?> />
                            <span><?php echo __('Enable WP eMember integration.', 'wpfront-notification-bar'); ?></span>
                        </label>
                    </td>
                </tr>
            </table>

            <h3><?php echo __('Color', 'wpfront-notification-bar'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php echo __('Bar Color', 'wpfront-notification-bar'); ?>
                    </th>
                    <td>
                        <div class="color-selector-div">
                            <div class="color-selector" color="<?php echo $this->options->bar_from_color(); ?>"></div>
                            <input type="text" class="color-value" name="<?php echo $this->options->bar_from_color_name(); ?>" value="<?php echo $this->options->bar_from_color(); ?>" />
                        </div>
                        <div class="color-selector-div">
                            <div class="color-selector" color="<?php echo $this->options->bar_to_color(); ?>"></div>
                            <input type="text" class="color-value" name="<?php echo $this->options->bar_to_color_name(); ?>" value="<?php echo $this->options->bar_to_color(); ?>" />
                        </div>
                        <span class="description"><?php echo __('[Select two different colors to create a gradient.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->message_color_label(); ?>
                    </th>
                    <td>
                        <div class="color-selector" color="<?php echo $this->options->message_color(); ?>"></div>
                        <input type="text" class="color-value" name="<?php echo $this->options->message_color_name(); ?>" value="<?php echo $this->options->message_color(); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo __('Button Color', 'wpfront-notification-bar'); ?>
                    </th>
                    <td>
                        <div class="color-selector-div">
                            <div class="color-selector" color="<?php echo $this->options->button_from_color(); ?>"></div>
                            <input type="text" class="color-value" name="<?php echo $this->options->button_from_color_name(); ?>" value="<?php echo $this->options->button_from_color(); ?>" />
                        </div>
                        <div class="color-selector-div">
                            <div class="color-selector" color="<?php echo $this->options->button_to_color(); ?>"></div>
                            <input type="text" class="color-value" name="<?php echo $this->options->button_to_color_name(); ?>" value="<?php echo $this->options->button_to_color(); ?>" />
                        </div>
                        <span class="description"><?php echo __('[Select two different colors to create a gradient.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->button_text_color_label(); ?>
                    </th>
                    <td>
                        <div class="color-selector" color="<?php echo $this->options->button_text_color(); ?>"></div>
                        <input type="text" class="color-value" name="<?php echo $this->options->button_text_color_name(); ?>" value="<?php echo $this->options->button_text_color(); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->open_button_color_label(); ?>
                    </th>
                    <td>
                        <div class="color-selector" color="<?php echo $this->options->open_button_color(); ?>"></div>
                        <input type="text" class="color-value" name="<?php echo $this->options->open_button_color_name(); ?>" value="<?php echo $this->options->open_button_color(); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->close_button_color_label(); ?>
                    </th>
                    <td>
                        <div class="color-selector-div">
                            <div class="color-selector" color="<?php echo $this->options->close_button_color(); ?>"></div>
                            <input type="text" class="color-value" name="<?php echo $this->options->close_button_color_name(); ?>" value="<?php echo $this->options->close_button_color(); ?>" />
                        </div>
                        <div class="color-selector-div">
                            <div class="color-selector" color="<?php echo $this->options->close_button_color_hover(); ?>"></div>
                            <input type="text" class="color-value" name="<?php echo $this->options->close_button_color_hover_name(); ?>" value="<?php echo $this->options->close_button_color_hover(); ?>" />
                        </div>
                        <div class="color-selector-div">
                            <div class="color-selector" color="<?php echo $this->options->close_button_color_x(); ?>"></div>
                            <input type="text" class="color-value" name="<?php echo $this->options->close_button_color_x_name(); ?>" value="<?php echo $this->options->close_button_color_x(); ?>" />
                        </div>
                        <span class="description"><?php echo __('[Normal, Hover, X]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
            </table>

            <h3><?php echo __('CSS', 'wpfront-notification-bar'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php echo $this->options->dynamic_css_use_url_label(); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="<?php echo $this->options->dynamic_css_use_url_name(); ?>" <?php echo $this->options->dynamic_css_use_url() ? 'checked' : ''; ?> />
                        <span class="description"><?php echo __('[Custom and dynamic CSS will be added through a URL instead of writing to the document. Enabling this setting is recommened if there are no conflicts, so that caching can be leveraged.]', 'wpfront-notification-bar'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo $this->options->custom_css_label(); ?>
                    </th>
                    <td>
                        <textarea name="<?php echo $this->options->custom_css_name(); ?>" rows="10" cols="75"><?php echo $this->options->custom_css(); ?></textarea>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="<?php echo $this->options->last_saved_name(); ?>" value="<?php echo time(); ?>" />
            <?php submit_button(); ?>
        </form>
    </div>
</div>
<script type="text/javascript">
    (function () {
        init_wpfront_notifiction_bar_options({
            choose_image: '<?php echo __('Choose Image', 'wpfront-notification-bar'); ?>',
            select_image: '<?php echo __('Select Image', 'wpfront-notification-bar'); ?>'
        });
    })();
</script>