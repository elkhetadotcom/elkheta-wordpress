<?php
?>
<style>
@media screen and (min-width: 1200px){
  .theme-select {
    width: 32.7%;
  }
}
@media screen and (min-width: 700px) and (max-width: 1200px){
  .theme-select {
    width: 48.5%;
  }
}
@media screen and (max-width: 700px){
  .theme-select {
    width: 98%;
  }
}
.display-flex {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}
.img-flex {
  display: flex;
  cursor: pointer;
  align-items: baseline;
  height: auto;
}
.img-flex img {
  margin: auto;
}
.text-flex {
  margin:20px 10px;
  display: flex;
  justify-content: space-between;
}
.text-flex button {
  display: inline-block;
}
</style>
<div style="padding-top: 10px;">
  <h1>Select a player theme </h1>
  <h4><a href="<?php menu_page_url('vdocipher', 1); ?> ">Return to Settings Page </a></h4>
</div>
<div>
<form name="playerThemeForm" method="post" action="options.php">
<?php
  settings_fields('vdo_custom_theme');
  do_settings_sections('vdo_custom_theme');
?>
  <table class="form-table">
    <?php

    if ((get_option('vdo_player_theme_options'))) {
        $vdo_custom_theme1 = get_option('vdo_player_theme_options');
        update_option('vdo_player_theme', $vdo_custom_theme1);
    } elseif ((get_option('vdo_player_theme')) == false) {
        update_option('vdo_player_theme', '9ae8bbe8dd964ddc9bdb932cca1cb59a');
        update_option('vdo_player_theme_options', '9ae8bbe8dd964ddc9bdb932cca1cb59a');
    } else {
        $vdo_custom_theme1 = get_option('vdo_player_theme');
        update_option('vdo_player_theme_options', $vdo_custom_theme1);
    }
    $vdo_custom_theme = get_option('vdo_player_theme_options');
    ?>
    <tr valign="top">
      <td style="width:100px; font-weight: 600;">Player Theme</th>
      <td style="width:320px">
        <input id="vdo_player_theme_options" type="text" name="vdo_player_theme_options"
        value="<?php echo esc_attr(get_option('vdo_player_theme_options')); ?>" max-length = "32" style="width: 320px"
        />
      </td>
      <td><?php submit_button(); ?></td>
    </tr>
  </table>
    <?php
    wp_enqueue_script('vdo_theme_options_select', plugin_dir_url(__FILE__).'js/themeselect.js');
    wp_localize_script('vdo_theme_options_select', 'vdoThemeOption', array(
    'selectedTheme'=>$vdo_custom_theme
    ));
    ?>

</form>
</div>
<div id="available_themes" class="display-flex">
  <div id="default1" class="theme-select">
    <div class="img-flex">
      <img
        id="default1-image"
        src="<?php echo plugin_dir_url(__FILE__).'img/default1.png' ?>"
        alt="VdoCipher Default player"
        width="100%" >
    </div>
    <div class="text-flex">
      <div>Theme ID: <span id="default1-id">9ae8bbe8dd964ddc9bdb932cca1cb59a</span> </div>
      <button id="default1-btn">Select</button>
    </div>
  </div>
  <div id="color1" class="theme-select">
    <div class="img-flex">
      <img
        id="color1-image"
        src="<?php echo plugin_dir_url(__FILE__).'img/color1.png' ?>"
        alt="VdoCipher Default player"
        width="100%" >
    </div>
    <div class="text-flex">
      <div>Theme ID: <span id="color1-id">e2dbde7971d145cd9a3bc0271b75b0a5</span> </div>
      <button id="color1-btn">Select</button>
    </div>
  </div>
  <div id="square1" class="theme-select">
    <div class="img-flex">
      <img
        id="square1-image"
        src="<?php echo plugin_dir_url(__FILE__).'img/square1.png' ?>"
        alt="VdoCipher Default player"
        width="100%" >
    </div>
    <div class="text-flex">
      <div>Theme ID: <span id="square1-id">9111e0056d664282b368c5558a36f32a</span> </div>
      <button id="square1-btn">Select</button>
    </div>
  </div>
  <div id="default2" class="theme-select">
    <div class="img-flex">
      <img
        id="default2-image"
        src="<?php echo plugin_dir_url(__FILE__).'img/default2.png' ?>"
        alt="VdoCipher Default player"
        width="100%" >
    </div>
    <div class="text-flex">
      <div>Theme ID: <span id="default2-id">b5e5a9020189409db6b7ecb2f762de45</span> </div>
      <button id="default2-btn">Select</button>
    </div>
  </div>
  <div id="color2" class="theme-select">
    <div class="img-flex">
      <img
        id="color2-image"
        src="<?php echo plugin_dir_url(__FILE__).'img/color2.png' ?>"
        alt="VdoCipher Default player"
        width="100%" >
    </div>
    <div class="text-flex">
      <div>Theme ID: <span id="color2-id">7ccc1ba5a4814477b342927037003f12</span> </div>
      <button id="color2-btn">Select</button>
    </div>
  </div>
  <div id="square2" class="theme-select">
    <div class="img-flex">
      <img
        id="square2-image"
        src="<?php echo plugin_dir_url(__FILE__).'img/square2.png' ?>"
        alt="VdoCipher Default player"
        width="100%" >
    </div>
    <div class="text-flex">
      <div>Theme ID: <span id="square2-id">8c4e41316cb240b6ae820611c097a743</span> </div>
      <button id="square2-btn">Select</button>
    </div>
  </div>
  <div id="default3" class="theme-select">
    <div class="img-flex">
      <img
        id="default3-image"
        src="<?php echo plugin_dir_url(__FILE__).'img/default3.png' ?>"
        alt="VdoCipher Default player"
        width="100%" >
    </div>
    <div class="text-flex">
      <div>Theme ID: <span id="default3-id">b9671d45d2d84c37b2d602940d340a00</span> </div>
      <button id="default3-btn">Select</button>
    </div>
  </div>
  <div id="color3" class="theme-select">
    <div class="img-flex">
      <img
        id="color3-image"
        src="<?php echo plugin_dir_url(__FILE__).'img/color3.png' ?>"
        alt="VdoCipher Default player"
        width="100%" >
    </div>
    <div class="text-flex">
      <div>Theme ID: <span id="color3-id">3d115cb024bf422586bf4c17dbd831d5</span> </div>
      <button id="color3-btn">Select</button>
    </div>
  </div>
  <div id="square3" class="theme-select">
    <div class="img-flex">
      <img
        id="square3-image"
        src="<?php echo plugin_dir_url(__FILE__).'img/square3.png' ?>"
        alt="VdoCipher Default player"
        width="100%" >
    </div>
    <div class="text-flex">
      <div>Theme ID: <span id="square3-id">013dbcec32f0460dbd9667febfa46158</span> </div>
      <button id="square3-btn">Select</button>
    </div>
  </div>
</div>
<div>
  <h4><a href="<?php menu_page_url('vdocipher', 1); ?> ">Return to Settings Page </a></h4>
  <p>
    For more details please visit this
    <a href="https://www.vdocipher.com/blog/2018/10/video-player-themes/" target="_blank">blogpost on Custom Themes</a>,
    or email us at <a href="mailto:support@vdocipher.com?Subject=Customize%20Video%20Player" target="_top">
    support@vdocipher.com</a>
  </p>
</div>
