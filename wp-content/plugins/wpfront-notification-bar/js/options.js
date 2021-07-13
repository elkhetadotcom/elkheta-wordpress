jQuery(document).ready(function () {
    var $ = jQuery;
    var $divOptions = $('#wpfront-notification-bar-options');

    $divOptions.on('change', '.pages-selection input[type="checkbox"]', function() {
        var $this = $(this);
        var $input = $this.parent().parent().parent().prev();
        var $text = $input.val();

        if ($this.prop('checked')) {
            $text += ',' + $this.val();
        } else {
            $text = (',' + $text + ',').replace(',' + $this.val() + ',', ',');
        }

        $text = $text.replace(/(^[,\s]+)|([,\s]+$)/g, '');
        $input.val($text);
    });

    $divOptions.on('change', '.roles-selection input[type="checkbox"]', function() {
        var values = [];
        var div = $(this).parent().parent().parent();
        div.find('input:checked').each(function (i, e) {
            values.push($(e).val());
        });
        div.children(":first").val(JSON.stringify(values));
    });

    $divOptions.find('input.date').datepicker({
        'dateFormat': 'yy-mm-dd'
    });

    $divOptions.find('input.time').timepicker({
        'timeFormat': 'h:i a'
    });

    $divOptions.on('change', '#chk_button_action_url_noopener', function() {
        $('#txt_button_action_url_noopener').val($(this).prop('checked') ? 1 : 0);
    });

    function setColorPicker(div) {
        if (div.ColorPicker) {
            div.ColorPicker({
                color: div.attr('color'),
                onShow: function (colpkr) {
                    $(colpkr).fadeIn(500);
                    return false;
                }, onHide: function (colpkr) {
                    $(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    div.css('backgroundColor', '#' + hex);
                    div.next().val('#' + hex);
                }
            }).css('backgroundColor', div.attr('color'));
        }
    }

    $divOptions.find(".color-selector").each(function (i, e) {
        setColorPicker($(e));
    });

});

(function () {
    window.init_wpfront_notifiction_bar_options = function (settings) {
        var $ = jQuery;
        var mediaLibrary = null;

        $('#wpfront-notification-bar-options').on('click', '#media-library-button', function () {
            if (mediaLibrary === null) {
                mediaLibrary = wp.media.frames.file_frame = wp.media({
                    title: settings.choose_image,
                    multiple: false,
                    button: {
                        text: settings.select_image
                    }
                }).on('select', function () {
                    var obj = mediaLibrary.state().get('selection').first().toJSON();

                    $('#reopen-button-image-url').val(obj.url);
                });
            }

            mediaLibrary.open();
            return false;
        });
    };
})();