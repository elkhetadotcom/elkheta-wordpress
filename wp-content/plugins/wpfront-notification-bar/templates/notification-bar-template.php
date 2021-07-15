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
 * Template for WPFront Notification Bar
 *
 * @author Syam Mohan <syam@wpfront.com>
 * @copyright 2013 WPFront.com
 */
?>

<?php
if (!$this->options->dynamic_css_use_url()) {
    ?>
    <style type="text/css">
    <?php
    $template = new WPFront_Notification_Bar_Custom_CSS_Template();
    $template->write($this);
    ?>
    </style>
    <?php
}
?>


<?php if ($this->options->display_button() && $this->options->button_action() == 2) { ?>
    <script type="text/javascript">
        function wpfront_notification_bar_button_action_script() {
            try {
    <?php echo $this->options->button_action_javascript(); ?>
            } catch (err) {
            }
        }
    </script>
<?php } ?>

<div id="wpfront-notification-bar-spacer" class="<?php echo $this->display_on_page_load() ? ' ' : 'hidden'; ?>">
    <div id="wpfront-notification-bar-open-button" aria-label="reopen" class="hidden <?php echo $this->options->position() == 1 ? 'top wpfront-bottom-shadow' : 'bottom wpfront-top-shadow'; ?>"></div>
    <div id="wpfront-notification-bar" class="wpfront-fixed <?php echo $this->display_on_page_load() ? ' load' : ''; ?> <?php echo $this->options->position() == 1 ? ' top' : ' bottom'; ?> <?php if ($this->options->display_shadow()) echo $this->options->position() == 1 ? ' wpfront-bottom-shadow' : ' wpfront-top-shadow'; ?>">
        
           <?php   if ($this->options->close_button()) { ?>
            <div aria-label="close" class="wpfront-close">X</div>
        <?php } ?>
        <table border="0" cellspacing="0" cellpadding="0" role="presentation">
            <tr>
                <td>
                    <div class="wpfront-message">
                        <?php echo $this->get_message_text(); ?>
                    </div>
                    <div>
                        <?php
                        if ($this->options->display_button()) {
                            $button_text = $this->get_button_text();
                            ?>
                            <?php
                            if ($this->options->button_action() == 1) {
                                $rel = array();

                                if ($this->options->button_action_url_nofollow()) {
                                    $rel[] = 'nofollow';
                                }

                                if ($this->options->button_action_url_noreferrer()) {
                                    $rel[] = 'noreferrer';
                                }

                                if ($this->options->button_action_new_tab() && $this->options->button_action_url_noopener()) {
                                    $rel[] = 'noopener';
                                }

                                $rel = implode(' ', $rel);
                                ?>
                                <a class="wpfront-button" href="<?php echo $this->options->button_action_url(); ?>"  target="<?php echo $this->options->button_action_new_tab() ? '_blank' : '_self'; ?>" <?php echo empty($rel) ? '' : "rel=\"$rel\""; ?>><?php echo $button_text; ?></a>
                                <?php
                            }
                            ?>
                            <?php if ($this->options->button_action() == 2) { ?>
                                <a class="wpfront-button" onclick="javascript:wpfront_notification_bar_button_action_script();"><?php echo $button_text; ?></a>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
</div>


