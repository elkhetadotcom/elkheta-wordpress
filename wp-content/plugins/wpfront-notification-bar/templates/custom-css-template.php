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

class WPFront_Notification_Bar_Custom_CSS_Template {

    protected $controller;
    protected $options;

    public function write($controller) {
        $this->controller = $controller;
        $this->options = $controller->get_options();

        $this->wpfront_notification_bar_css();
        $this->div_wpfront_message_css();
        $this->a_wpfront_button_css();
        $this->open_button_css();
        $this->div_wpfront_close_css();
        $this->div_wpfront_close_hover_css();
        $this->hide_small_device();
        $this->hide_small_window();
        $this->custom_css();
    }

    protected function wpfront_notification_bar_css() {
        ?>

        #wpfront-notification-bar
        {
        background: <?php echo $this->options->bar_from_color(); ?>;
        background: -moz-linear-gradient(top, <?php echo $this->options->bar_from_color(); ?> 0%, <?php echo $this->options->bar_to_color(); ?> 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,<?php echo $this->options->bar_from_color(); ?>), color-stop(100%,<?php echo $this->options->bar_to_color(); ?>));
        background: -webkit-linear-gradient(top, <?php echo $this->options->bar_from_color(); ?> 0%,<?php echo $this->options->bar_to_color(); ?> 100%);
        background: -o-linear-gradient(top, <?php echo $this->options->bar_from_color(); ?> 0%,<?php echo $this->options->bar_to_color(); ?> 100%);
        background: -ms-linear-gradient(top, <?php echo $this->options->bar_from_color(); ?> 0%,<?php echo $this->options->bar_to_color(); ?> 100%);
        background: linear-gradient(to bottom, <?php echo $this->options->bar_from_color(); ?> 0%, <?php echo $this->options->bar_to_color(); ?> 100%);
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $this->options->bar_from_color(); ?>', endColorstr='<?php echo $this->options->bar_to_color(); ?>',GradientType=0 );
        }
        <?php
    }

    protected function div_wpfront_message_css() {
        ?>
        #wpfront-notification-bar div.wpfront-message
        {
        color: <?php echo $this->options->message_color(); ?>;
        }

        <?php
    }

    protected function a_wpfront_button_css() {
        ?>
        #wpfront-notification-bar a.wpfront-button
        {
        background: <?php echo $this->options->button_from_color(); ?>;
        background: -moz-linear-gradient(top, <?php echo $this->options->button_from_color(); ?> 0%, <?php echo $this->options->button_to_color(); ?> 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,<?php echo $this->options->button_from_color(); ?>), color-stop(100%,<?php echo $this->options->button_to_color(); ?>));
        background: -webkit-linear-gradient(top, <?php echo $this->options->button_from_color(); ?> 0%,<?php echo $this->options->button_to_color(); ?> 100%);
        background: -o-linear-gradient(top, <?php echo $this->options->button_from_color(); ?> 0%,<?php echo $this->options->button_to_color(); ?> 100%);
        background: -ms-linear-gradient(top, <?php echo $this->options->button_from_color(); ?> 0%,<?php echo $this->options->button_to_color(); ?> 100%);
        background: linear-gradient(to bottom, <?php echo $this->options->button_from_color(); ?> 0%, <?php echo $this->options->button_to_color(); ?> 100%);
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $this->options->button_from_color(); ?>', endColorstr='<?php echo $this->options->button_to_color(); ?>',GradientType=0 );

        color: <?php echo $this->options->button_text_color(); ?>;
        }
        <?php
    }

    protected function open_button_css() {
        ?>
        #wpfront-notification-bar-open-button
        {
        background-color: <?php echo $this->options->open_button_color(); ?>;
        <?php
        if (!empty($this->options->reopen_button_image_url())) {
            echo "background-image: url({$this->options->reopen_button_image_url()});";
        }
        ?>
        }
        <?php
        if (empty($this->options->reopen_button_image_url())) {
            $url_top = plugins_url('images/arrow_down.png', $this->controller->get_plugin_file());
            $url_bottom = plugins_url('images/arrow_up.png', $this->controller->get_plugin_file());
            ?>
            #wpfront-notification-bar-open-button.top 
            {
            background-image: url(<?php echo $url_top; ?>);
            }

            #wpfront-notification-bar-open-button.bottom 
            {
            background-image: url(<?php echo $url_bottom; ?>);
            }
            <?php
        }
    }

    protected function div_wpfront_close_css() {
        ?>
        #wpfront-notification-bar  div.wpfront-close
        {
        border: 1px solid <?php echo $this->options->close_button_color(); ?>;
        background-color: <?php echo $this->options->close_button_color(); ?>;
        color: <?php echo $this->options->close_button_color_x(); ?>;
        }
        <?php
    }

    protected function div_wpfront_close_hover_css() {
        ?>
        #wpfront-notification-bar  div.wpfront-close:hover
        {
        border: 1px solid <?php echo $this->options->close_button_color_hover(); ?>;
        background-color: <?php echo $this->options->close_button_color_hover(); ?>;
        }
        <?php
    }

    protected function hide_small_device() {
        if ($this->options->hide_small_device()) {
            echo "@media screen and (max-device-width: {$this->options->small_device_width()}px) { #wpfront-notification-bar-spacer  { display:none; } }";
        }
    }

    protected function hide_small_window() {
        if ($this->options->hide_small_window()) {
            echo "@media screen and (max-width: {$this->options->small_window_width()}px) { #wpfront-notification-bar-spacer  { display:none; } }";
        }
    }

    protected function custom_css() {
        echo $this->options->custom_css();
    }

}
