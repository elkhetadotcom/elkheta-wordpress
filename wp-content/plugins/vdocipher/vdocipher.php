<?php
/**
 * Plugin Name: VdoCipher
 * Plugin URI: https://www.vdocipher.com
 * Description: Secured video hosting for wordpress
 * Version: 1.26
 * Author: VdoCipher
 * Author URI: https://www.vdocipher.com
 * License: GPL2
 */

if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\r\n<html><head>\r\n<title>404 Not Found</title>\r\n".
        "</head><body>\r\n<h1>Not Found</h1>\r\n<p>The requested URL " . $_SERVER['SCRIPT_NAME'] . " was not found on ".
        "this server.</p>\r\n</body></html>");
}

if (!defined('VDOCIPHER_PLUGIN_VERSION')) {
    define('VDOCIPHER_PLUGIN_VERSION', '1.25');
}

if (!defined('VDOCIPHER_PLAYER_VERSION')) {
    define('VDOCIPHER_PLAYER_VERSION', '1.6.10');
}

function vdo_plugin_check_version()
{
// This applies only for installs 1.24 and below
    if (!get_option('vdo_plugin_version')) {
        if (preg_match('/^1\.[0123456]\.[0-9]{1,2}$/', get_option('vdo_embed_version'))) {
            update_option('vdo_embed_version', VDOCIPHER_PLAYER_VERSION);
        }
        if (preg_match('/^1\.[01234]\.[0-9]{1,2}$/', get_option('vdo_embed_version'))) {
            update_option('vdo_default_height', 'auto');
        }
        update_option('vdo_plugin_version', VDOCIPHER_PLUGIN_VERSION);
        return ;
    }
 // This applies for all new installs after 1.25
    if (VDOCIPHER_PLUGIN_VERSION !== get_option('vdo_plugin_version')) {
        if (preg_match('/^1\.[0-9]{1,2}\.[0-9]{1,2}$/', get_option('vdo_embed_version'))) {
            update_option('vdo_embed_version', VDOCIPHER_PLAYER_VERSION);
        }
        update_option('vdo_plugin_version', VDOCIPHER_PLUGIN_VERSION);
        return ;
    }
    return ;
}

add_action('plugins_loaded', 'vdo_plugin_check_version');

// Function called to retrieve id for when title given, starts
function vdo_retrieve_id($title)
{
    $client_key = get_option('vdo_client_key');
    if ($client_key == false || $client_key == "") {
        return "Plugin not configured. Please set the API key to embed videos.";
    }
    $url = "https://dev.vdocipher.com/api/videos?q=$title";
    $headers = array(
        'Authorization'=>'Apisecret '.$client_key,
        'Content-Type'=>'application/json',
        'Accept'=>'application/json'
    );
    $response = wp_remote_post($url, array(
        'method'    =>  'GET',
        'headers'   =>  $headers
    ));
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        echo "VdoCipher: Something went wrong: $error_message";
        return "";
    }
    $video_json_response = $response['body'];
    $video_list_object = json_decode($video_json_response);
    $video_list = $video_list_object->rows;
    $video_object = $video_list[0];
    $video_id = $video_object->id;
    return $video_id;
}
// Function called to retrieve id for when title given, ends

// Function called to get OTP, starts
function vdo_otp($video, $otp_post_array = array())
{
    $client_key = get_option('vdo_client_key');
    if ($client_key == false || $client_key == "") {
        return "Plugin not configured. Please set the API key to embed videos.";
    }
    $url = "https://dev.vdocipher.com/api/videos/$video/otp";
    $headers = array(
        'Authorization'=>'Apisecret '.$client_key,
        'Content-Type'=>'application/json',
        'Accept'=>'application/json'
    );
    $otp_post_json = json_encode($otp_post_array);
    $response = wp_remote_post(
        $url,
        array(
            'method'    =>  'POST',
            'headers'   =>  $headers,
            'body'      =>  $otp_post_json
        )
    );
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        echo "VdoCipher: Something went wrong: $error_message";
        return "";
    }
    $OTP_Response =  $response['body'];
    return json_decode($OTP_Response);
}
// Function called to get OTP, ends

// VdoCipher Shortcode starts
function vdo_shortcode($atts)
{
    $vdo_args = shortcode_atts(
        array(
            'title' => 'TITLE_OF_VIDEO',
            'width' => get_option('vdo_default_width'),
            'height' => get_option('vdo_default_height'),
            'id'    => 'id',
            'no_annotate'=> false,
            'version'=> 0,
            'vdo_theme'=> false,
            'vdo_version'=> false,
            'player_tech'=> ''
        ),
        $atts
    );
    $title = $vdo_args['title'];
    $width = $vdo_args['width'];
    $height = $vdo_args['height'];
    $id = $vdo_args['id'];
    $no_annotate = $vdo_args['no_annotate'];
    $version = $vdo_args['version'];
    $vdo_theme = $vdo_args['vdo_theme'];
    $vdo_version = $vdo_args['vdo_version'];
    $player_tech = $vdo_args['player_tech'];

    if (!preg_match('/.*px$/', $width)) {
        $width = $width."px";
    }
    if (!preg_match('/.*px$/', $height)) {
        if ($height != 'auto') {
            $height = $height."px";
        }
    }
    if (!$atts['id']) {
        if (!$atts['title']) {
            return "Required argument id for embedded video not found.";
        } else {
            $video = vdo_retrieve_id($title);
            if ($video == null) {
                return "404. Video not found.";
            }
        }
    } else {
        $video = $id;
    }

    // Initialize $otp_post_array, to be sent as part of OTP request, as for time-to-live 300
    $otp_post_array = array("ttl" => 300);
    if (!function_exists("eval_date")) {
        function eval_date($matches)
        {
            return current_time($matches[1]);
        }
    }
    if (get_option('vdo_annotate_code') != "") {
        $current_user = wp_get_current_user();
        $vdo_annotate_code = get_option('vdo_annotate_code');
        $vdo_annotate_code = apply_filters('vdocipher_annotate_preprocess', $vdo_annotate_code);
        if (is_user_logged_in()) {
            $vdo_annotate_code = str_replace('{name}', $current_user->display_name, $vdo_annotate_code);
            $vdo_annotate_code = str_replace('{email}', $current_user->user_email, $vdo_annotate_code);
            $vdo_annotate_code = str_replace('{username}', $current_user->user_login, $vdo_annotate_code);
            $vdo_annotate_code = str_replace('{id}', $current_user->ID, $vdo_annotate_code);
        }
        $vdo_annotate_code = str_replace('{ip}', $_SERVER['REMOTE_ADDR'], $vdo_annotate_code);
        $vdo_annotate_code = preg_replace_callback('/\{date\.([^\}]+)\}/', "eval_date", $vdo_annotate_code);
        $vdo_annotate_code = apply_filters('vdocipher_annotate_postprocess', $vdo_annotate_code);
        // Add annotate code to $otp_post_array, which will be
        // converted to Json and then sent as POST body to API endpoint
        if (!$no_annotate) {
            $otp_post_array["annotate"] = $vdo_annotate_code;
        }
    }
    // OTP is requested via vdo_otp function
    $OTP_Response = vdo_otp($video, $otp_post_array);
    $OTP = $OTP_Response->otp;
    $playbackInfo = $OTP_Response->playbackInfo;

    if (is_null($OTP)) {
        $output = "<span id='vdo$OTP' style='background:#555555;color:#FFFFFF'><h4>Video not found</h4></span>";
        return $output;
    }

    // Version, legacy, for flash only
    $version = 0;
    if (isset($atts['version'])) {
        $version = $atts['version'];
    }

    // Video Embed version is retrieved from options table or from shortcode attribute
    if ((get_option('vdo_embed_version')) == false) {
        update_option('vdo_embed_version', VDOCIPHER_PLAYER_VERSION);
    }
    if (!$vdo_version) {
        $vdo_embed_version_str = get_option('vdo_embed_version');
    } else {
        $vdo_embed_version_str = $vdo_version;
    }

    // Video Player theme, update and as shortcode attribute
    if ((get_option('vdo_player_theme')) == false) {
        update_option('vdo_player_theme', '9ae8bbe8dd964ddc9bdb932cca1cb59a');
    }
    if (!$vdo_theme) {
        $vdo_player_theme = get_option('vdo_player_theme');
    } else {
        $vdo_player_theme = $vdo_theme;
    }

    // tech override custom names
    switch ($player_tech) {
        case "flash":
            $player_tech = "*,-dash";
            break;
        case "nohtml5":
            $player_tech = "*,-dash";
            break;
        case "noflash":
            $player_tech = "*,-hss";
            break;
        case "nozen":
            $player_tech = "*,-zen";
            break;
        case "noios":
            $player_tech = "*,-hlse";
            break;
        default:
            break;
    }

    // Old Embed Code
    if ($vdo_embed_version_str === '0.5') {
        $output = "<div id='vdo$OTP' style='height:$height;width:$width;max-width:100%' ></div>";
        $output .= "<script> (function(v,i,d,e,o){v[o]=v[o]||{}; v[o].add = v[o].add || function V(a){".
            " (v[o].d=v[o].d||[]).push(a);};";
        $output .= "if(!v[o].l) { v[o].l=1*new Date();a=i.createElement(d),m=i.getElementsByTagName(d)[0];a.async=1;".
            "a.src=e; m.parentNode.insertBefore(a,m);}";
        $output .= " })(window,document,'script','//de122v0opjemw.cloudfront.net/vdo.js','vdo'); vdo.add({ ";
        $output .= "o: '$OTP', ";
        if ($version == 32) {
            $output .= "version: '$version' ";
        }
        $output .= "}); </script>";
    } else {
        //New embed code
        if ($player_tech === '') {
            if (get_option('vdo_watermark_flash_html') === 'flash') {
                $player_tech = "*,-dash";
            }
        }
        $output = "<div id='vdo$OTP' style='height:$height;width:$width;max-width:100%' ></div>";
        $output .= "<script>(function(v,i,d,e,o){v[o]=v[o]||{}; v[o].add = v[o].add || function V(a){".
            "(v[o].d=v[o].d||[]).push(a);};";
        $output .= "if(!v[o].l) { v[o].l=1*new Date(); a=i.createElement(d), m=i.getElementsByTagName(d)[0];";
        $output .= "a.async=1; a.src=e; m.parentNode.insertBefore(a,m);}";
        $output .= "})(window,document,'script','https://d1z78r8i505acl.cloudfront.net/playerAssets/";
        $output .= "$vdo_embed_version_str";
        $output .= "/vdo.js','vdo');";
        $output .= "vdo.add({";
        $output .= "otp: '$OTP',";
        $output .= "playbackInfo: '$playbackInfo',";
        $output .= "theme: '$vdo_player_theme',";
        $output .= "plugins: [{";
        $output .= "name: 'keyboard',";
        $output .= "options: {";
        $output .= "preset: 'default',";
        $output .= "bindings: {";
        $output .= "'Left' : (player) => player.seek(player.currentTime - 15),";
        $output .= "'Right' : (player) => player.seek(player.currentTime + 15),";
        $output .= "},";
        $output .= "}";
        $output .= "}],";
        if ($player_tech !== '') {
            $output .= "techoverride: [" ;
            $techarray = explode(',', $player_tech);
            for ($i = 0; $i < sizeof($techarray); $i++) {
                $techStr = $techarray[$i];
                $output .= "'$techStr'";
                if ($i !== sizeof($techarray)-1) {
                    $output .= ", ";
                }
            }
            $output .= "],";
        }
        $output .= "container: document.querySelector('#vdo$OTP'),});";
        $output .= "</script>";
    }
    return $output;
}
add_shortcode('vdo', 'vdo_shortcode');
// VdoCipher Shortcode ends

// adding the Settings link, starts
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'vdo_settings_link');

function vdo_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=vdocipher">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
// adding the Settings link, ends

// add the menu item and register settings (3 functions), starts
if (is_admin()) { // admin actions
    add_action('admin_init', 'register_vdo_settings');
    add_action('admin_menu', 'vdo_menu');
} else {
      // non-admin enqueues, actions, and filters
}
function vdo_menu()
{
    add_menu_page(
        'VdoCipher Options',
        'VdoCipher',
        'manage_options',
        'vdocipher',
        'vdo_options',
        plugin_dir_url(__FILE__).'images/logo.png'
    );
    add_submenu_page(
        null,
        'Video Player Themes',
        'CustomPlayer',
        'manage_options',
        'themesvdo',
        'themesvdo'
    );
}

function themesvdo()
{
    include('include/player_themes.php');
}

function vdo_options()
{
    if (!get_option('vdo_default_height')) {
        update_option('vdo_default_height', 'auto');
    }
    if (!get_option('vdo_default_width')) {
        update_option('vdo_default_width', '1280');
    }
    $vdo_client_key = get_option('vdo_client_key');
    if (!$vdo_client_key && strlen($vdo_client_key) != 64) {
        vdo_show_form_client_key();
        return "";
    }
    if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    include('include/options.php');
}
function register_vdo_settings()
{
 // whitelist options
    register_setting('vdo_option-group', 'vdo_client_key');
    register_setting('vdo_option-group', 'vdo_default_height');
    register_setting('vdo_option-group', 'vdo_default_width');
    register_setting('vdo_option-group', 'vdo_annotate_code');
    register_setting('vdo_option-group', 'vdo_embed_version');
    register_setting('vdo_option-group', 'vdo_player_theme');
    register_setting('vdo_option-group', 'vdo_watermark_flash_html');
    register_setting('vdo_option-group', 'vdo_plugin_version');
    register_setting('vdo_custom_theme', 'vdo_player_theme_options');
}
// add the menu item and register settings (3 functions), ends

// section for client key for new users
function vdo_show_form_client_key()
{
    include('include/setting_form.php');
}

// Activation Hook starts
function vdo_activate()
{
    if ((get_option('vdo_default_height')) == false) {
        update_option('vdo_default_height', 'auto');
    }
    if ((get_option('vdo_default_width')) == false) {
        update_option('vdo_default_width', '1280');
    }
    //https://stackoverflow.com/a/2173318/5022684
    if ((get_option('vdo_embed_version')) == false) {
        update_option('vdo_embed_version', VDOCIPHER_PLAYER_VERSION);
    }
    if ((get_option('vdo_player_theme')) == false) {
        update_option('vdo_player_theme', '9ae8bbe8dd964ddc9bdb932cca1cb59a');
    }
    if ((get_option('vdo_watermark_flash_html')) == false) {
        update_option('vdo_watermark_flash_html', 'html5');
    }
    if ((get_option('vdo_plugin_version')) == false) {
        update_option('vdo_plugin_version', VDOCIPHER_PLUGIN_VERSION);
    }
}
register_activation_hook(__FILE__, 'vdo_activate');

// Registering and specifying Gutenberg block
function vdo_register_block()
{
    if (!function_exists('register_block_type')) {
        return ;
    }
    wp_register_script(
        'vdo-block-script',
        plugins_url('/include/block/dist/blocks.build.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n')
    );
    //wp_register_style(
    //    'vdo-block-base-style',
    //    plugins_url('/include/block/dist/blocks.style.build.css', __FILE__),
    //    array('wp-blocks')
    //);
    wp_register_style(
        'vdo-block-editor-style',
        plugins_url('/include/block/dist/blocks.editor.build.css', __FILE__),
        array('wp-edit-blocks')
    );
    register_block_type(
        'vdo/block',
        array(
        'editor_script'=>'vdo-block-script',
        'editor_style'=>'vdo-block-editor-style',
        'style'=>'vdo-block-base-style',
        'attributes'=>array(
        'id'=>array(
            'type'=>'string',
        ),
        'width'=>array(
            'type'=>'string',
            'default'=>get_option('vdo_default_width')
        ),
        'height'=>array(
            'type'=>'string',
            'default'=>get_option('vdo_default_height')
        ),
        'vdo_theme'=>array(
            'type'=>'string',
            'default'=>get_option('vdo_player_theme')
        ),
        'vdo_version'=>array(
            'type'=>'string',
            'default'=>get_option('vdo_embed_version')
        ),
        ),
        'render_callback'=>'vdo_shortcode'
        )
    );
}

add_action('init', 'vdo_register_block');

// Deactivation Hook starts
function vdo_deactivate()
{
    delete_option('vdo_client_key');
    delete_option('vdo_default_width');
    delete_option('vdo_default_height');
    delete_option('vdo_annotate_code');
    delete_option('vdo_embed_version');
    delete_option('vdo_player_theme');
    delete_option('vdo_watermark_flash_html');
    delete_option('vdo_player_theme_options');
    delete_option('vdo_plugin_version');
}
register_deactivation_hook(__FILE__, 'vdo_deactivate');

// Admin notice to configure plugin for new installs, starts
function vdo_admin_notice()
{
    if ((!get_option('vdo_client_key') || strlen(get_option('vdo_client_key')) != 64)
        && basename($_SERVER['PHP_SELF']) == "plugins.php"
    ) {
        ?>
        <div class="error">
            <p>
            The VdoCipher plugin is not ready.
            <a href="options-general.php?page=vdocipher">Click here to configure</a>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'vdo_admin_notice');
