<?php
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\r\n<html><head>\r\n<title>404 Not Found</title>\r\n".
        "</head><body>\r\n<h1>Not Found</h1>\r\n<p>The requested URL " . $_SERVER['SCRIPT_NAME'] . " was not found on ".
        "this server.</p>\r\n</body></html>");
}
?>
<div class="wrap">
<form method="post" action="options.php">
<?php
settings_fields('vdo_option-group');
do_settings_sections('vdo_option-group');
?>
    <h2>Please enter your VdoCipher API Secret Key</h2>

    <table class="form-table">
        <tbody><tr>
            <td style="width: 160px;">API Secret key :</td>
            <td>
                <div style="display: inline-flex;">
                <input id="vdo_client_key" name="vdo_client_key" style="width: 400px;"
                  value="<?php echo esc_attr(get_option('vdo_client_key')); ?>"
                  type="password">
                  <p style="margin-left:20px; position: relative">
                      <span id="toggle_span" style="display:none">
                        <button id="toggle_API_visibility" href="#" data-protected="On"></button>
                      </span>
                  </p>
                </div>
            </td>
            <tr style="display:none;">
              <td>Plugin version no.: </td>
              <td><input
                id="vdo_plugin_version" name="vdo_plugin_version"
                value="<?php echo esc_attr(get_option('vdo_plugin_version')); ?>" readonly>
              </td>
            </tr>
        </tr>

    </tbody></table>
<?php wp_enqueue_script('vdo_hide_api_key', plugin_dir_url(__FILE__).'js/showkey.js');
?>
<?php submit_button(); ?>
</form>
</div>
