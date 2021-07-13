<?php
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\r\n<html><head>\r\n<title>404 Not Found</title>\r\n".
        "</head><body>\r\n<h1>Not Found</h1>\r\n<p>The requested URL " . $_SERVER['SCRIPT_NAME'] . " was not found on ".
        "this server.</p>\r\n</body></html>");
}
?>

<div class="wrap">
<h2>VdoCipher Options</h2>

<form name="vdoOptionForm" method="post" action="options.php">
<?php
settings_fields('vdo_option-group');
do_settings_sections('vdo_option-group');
?>
    <div class="updated">
        <p><strong>Status : </strong> Ready to use. You can now embed your videos inside posts and pages.</p>
    </div>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">API Secret Key</th>
        <td>
            <div style="display: inline-flex;">
                <input type="password" name="vdo_client_key"
          value="<?php echo esc_attr(get_option('vdo_client_key')); ?>"
          max-length="64" style="width: 640px" id="vdo_client_key" />
                <p style="margin-left:20px; position: relative">
                    <span id="toggle_span">
                        <button id="toggle_API_visibility" href="#" data-protected="On">Show API Secret Key</button>
                    </span>
                </p>
            </div>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Default Width</th>
        <td><input type="text" id="vdo_default_width" name="vdo_default_width"
          value="<?php echo esc_attr(get_option('vdo_default_width')); ?>"
        /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Default Height</th>
        <td><input type="text" id="vdo_default_height" name="vdo_default_height"
          value="<?php echo esc_attr(get_option('vdo_default_height')); ?>"
        /></td>
        </tr>

        <!-- Version Number -->
        <?php
        if ((get_option('vdo_embed_version')) == false) {
            update_option('vdo_embed_version', VDOCIPHER_PLAYER_VERSION);
        }
        ?>
        <tr valign="top">
        <th scope="row">Player Version</th>
        <td>
            <div style="display: inline-flex">
            <select id="vdo_embed_version">
                <?php
                $vdo_embed_version_vars = array(
                    '0.5', '1.0.0', '1.1.0', '1.1.3', '1.2.7', '1.3.3', '1.4.5','1.5.0','1.6.4','1.6.10'
                );
                $vdo_embed_version_string = get_option('vdo_embed_version');
                foreach ($vdo_embed_version_vars as $vdo_embed_version_var) {
                    if ($vdo_embed_version_string == $vdo_embed_version_var) {
                        $selected = 'selected';
                    } else {
                        $selected = '';
                    }
                    switch ($vdo_embed_version_var) {
                        case '0.5':
                            $version_old_new = ' (old player)';
                            break;
                        case VDOCIPHER_PLAYER_VERSION:
                            $version_old_new = ' (newest player)' ;
                            break;
                        default:
                            $version_old_new = ' (new player)';
                            break;
                    }
                    ?>
                    <option value="<?php echo $vdo_embed_version_var; ?>" <?php echo $selected; ?> >
                        <?php echo $vdo_embed_version_var . $version_old_new ; ?>
                    </option>
                <?php } ?>
                <option value="Custom Version">
                    <?php echo 'Custom Version' ?>
                </option>
                </select>
                <input type="text" name="vdo_embed_version"
                    id="vdo_custom_version"
                    style="margin-left:20px; position: relative; width:120px;" />
            </div>
            <p class="description">It is recommended that you use the latest player version for best video playback.</p>
        </td>
        </tr>

        <!-- Player Theme Options -->
        <?php
        if (!(get_option('vdo_player_theme'))) {
            update_option('vdo_player_theme', '9ae8bbe8dd964ddc9bdb932cca1cb59a');
        }
        ?>
        <tr valign="top">
        <th scope="row">Player Theme</th>
        <td>
        <div style="display:inline-flex; margin-bottom:10px;">
        <input
            type="text"
            name="vdo_player_theme"
            value="<?php echo esc_attr(get_option('vdo_player_theme')); ?>"
            max-length = "32"
            style="width: 320px"
            readonly
        />
        <p style="margin-left:20px; position: relative">
        <span><a href="<?php menu_page_url('themesvdo', 1); ?> " >Select Custom Player Theme</a><br/>
        </p>
        </div>
        <p class="description">
            Player theme is not available for old player version(0.5). The default theme will be applied for old player.
        </p>

        </td>
        </tr>
        <!-- Player Theme Options end-->

        <!-- Player Watermark option - Flash/ HTML5 starts -->
        <tr id="vdo_watermark_html_flash" valign="top">
          <th scope="row"> Choice of Watermark </th>
          <td>
            <?php
            if ((get_option('vdo_watermark_flash_html') == 'html5')) {
                $vdocheckedhtml5 = 'checked';
                $vdocheckedflash = '';
            } elseif ((get_option('vdo_watermark_flash_html') == 'flash')) {
                $vdocheckedhtml5 = '';
                $vdocheckedflash = 'checked';
            }
            ?>
            <input type="radio" class="vdo-htmlflash"
                value="html5" name="vdo_watermark_flash_html"
                id="vdo_html5" <?php echo $vdocheckedhtml5; ?> >
            <label for="vdo_html5">HTML5 (Overlay)</label>
            &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" class="vdo-htmlflash"
                value="flash" name="vdo_watermark_flash_html"
                id="vdo_flash" <?php echo $vdocheckedflash; ?> >
            <label for="vdo_flash">Flash (Hard-Coded)</label>
          </td>
        </tr>
        <!-- Player Watermark option - Flash/ HTML5 ends -->
        <tr valign="top">
        <th scope="row">Annotation Statement</th>
        <td>
          <div style="display: inline-flex;">
          <textarea name="vdo_annotate_code" id="vdo_watermarkjson"
            type="textarea" rows="6" cols="55" style="display: float-left"
          ><?php
            if (get_option('vdo_annotate_code') != "") {
                echo get_option('vdo_annotate_code');
                $vdo_annotation_code = get_option('vdo_annotate_code');
            }
            ?></textarea>
          <p class="description" style="margin-left:20px; position: relative">
          <span style="color:purple"><b>Sample Code for Dynamic Watermark</b></span><br/>
          [{'type':'rtext', 'text':' {name}', 'alpha':'0.60', 'color':'0xFF0000','size':'15','interval':'5000'}] <br/>
          <span style="color:purple"><b>Sample Code for Static Watermark</b></span><br/>
          [{'type':'text', 'text':'{ip}', 'alpha':'0.5' , 'x':'10', 'y':'100', 'color':'0xFF0000', 'size':'12'}] <br/>
          </p>
          </div>
          <p class="description" id="vdojsonvalidator"></p>
          <p class="description">
                Leave this text blank in case you do not need watermark over all
                videos. For details on writing the annotation code
                <a href="https://www.vdocipher.com/blog/2014/12/add-text-to-videos-with-watermark/" target="_blank">
                    check this out
                </a>
          </p>
        </td>
        </tr>
        <tr style="display:none;">
          <td>Plugin version no.: </td>
          <td><input
            id="vdo_plugin_version" name="vdo_plugin_version"
            value="<?php echo esc_attr(get_option('vdo_plugin_version')); ?>" readonly>
          </td>
        </tr>
    </table>
    <?php
        wp_enqueue_script('vdo_validate_watermark', plugin_dir_url(__FILE__).'js/validatewatermark.js');
        wp_localize_script('vdo_validate_watermark', 'vdoValidateWatermark', array(
          'vdoWatermark' => $vdo_annotation_code
        ));
        wp_enqueue_script('vdo_html_flash_choice', plugin_dir_url(__FILE__).'js/flashhtml5choice.js');
        wp_enqueue_script('vdo_hide_key', plugin_dir_url(__FILE__).'js/showkey.js');
        wp_localize_script('vdo_html_flash_choice', 'vdoVD', array(
          'vdoAV' => $vdo_embed_version_vars,
          'vdoSV' => $vdo_embed_version_string
        ));
        ?>
<?php submit_button(); ?>
</form>
</div>
