/*
 WPFront Notification Bar Plugin
 Copyright (C) 2013, WPFront.com
 Website: wpfront.com
 Contact: syam@wpfront.com
 
 WPFront Notification Bar Plugin is distributed under the GNU General Public License, Version 3,
 June 2007. Copyright (C) 2007 Free Software Foundation, Inc., 51 Franklin
 St, Fifth Floor, Boston, MA 02110, USA
 
 */

(function() {
    //displays the notification bar
    window.wpfront_notification_bar = function(data, process) {
        var log = function(msg) {
            if(data.log) 
                console.log('[WPFront Notification Bar] ' + msg);
        };

        if(typeof jQuery !== "function" || (data.keep_closed && typeof Cookies !== "function")) {
            log('Waiting for ' + (typeof jQuery !== "function" ? 'jQuery.' : 'Cookies.'));
            setTimeout(function(){
                wpfront_notification_bar(data, process);
            }, 100);
            return;
        }

        if(data.position == 2 && process !== true) {
            jQuery(function(){
                wpfront_notification_bar(data, true);
            });
            return;
        }

        var $ = jQuery;

        var keep_closed_cookie = data.keep_closed_cookie;

        var spacer = $("#wpfront-notification-bar-spacer").removeClass('hidden');
        var bar = $("#wpfront-notification-bar");
        var open_button = $("#wpfront-notification-bar-open-button");

        //set the position
        if (data.position == 1) {
            log('Setting notification bar at top.');
            var top = 0;
            if (data.fixed_position && data.is_admin_bar_showing) {
                top = $("html").css("margin-top");
                if (top == "0px")
                    top = $("html").css("padding-top");
                top = parseInt(top);
            }
            if (data.fixed_position) {
                top += data.position_offset;
            }
            bar.css("top", top + "px");
            open_button.css("top", top + "px");
            spacer.css("top", data.position_offset + "px");
            var $body = $("body").prepend(spacer);
            $(function(){
                if(!$body.children().first().is(spacer)) {
                    $body.prepend(spacer);
                }
            });
        }
        else {
            log('Setting notification bar at bottom.');
            var $body = $("body");
            if(!$body.children().last().is(spacer)) {
                $body.append(spacer);
            }
            $(function(){
                if(!$body.children().last().is(spacer)) {
                    $body.append(spacer);
                }
            });
        }

        var height = bar.height();
        if (data.height > 0) {
            height = data.height;
            bar.find("table, tbody, tr").css("height", "100%");
        }

        bar.height(0).css({"position": (data.fixed_position ? "fixed" : "relative"), "visibility": "visible"});
        open_button.css({"position": (data.fixed_position ? "fixed" : "absolute")});

        //function to set bar height based on options
        var closed = false;
        var user_closed = false;
        function setHeight(height, callback, userclosed) {
            callback = callback || $.noop;

            if (userclosed)
                user_closed = true;

            if (height == 0) {
                if (closed)
                    return;
                closed = true;
            }
            else {
                if (!closed)
                    return;
                closed = false;
            }

            var fn = callback;
            callback = function() {
                fn();
                if (height > 0) {
                    //set height to auto if in case content wraps on resize
                    if (data.height == 0)
                        bar.height("auto");

                    if(data.display_open_button) {
                        log('Setting reopen button state to hidden.');
                        open_button.addClass('hidden');
                    }

                    closed = false;
                }
                if (height == 0 && data.display_open_button) {
                    log('Setting reopen button state to visible.');
                    open_button.removeClass('hidden');
                }
                if (height == 0 && data.keep_closed && userclosed) {
                    if (data.keep_closed_for > 0)
                        Cookies.set(keep_closed_cookie, 1, {path: "/", expires: data.keep_closed_for});
                    else
                        Cookies.set(keep_closed_cookie, 1, {path: "/"});
                }
            };

            //set animation
            if(height > 0)
                log('Setting notification bar state to visible.');
            else
                log('Setting notification bar state to hidden.');

            if (data.animate_delay > 0) {
                bar.stop().animate({"height": height + "px"}, data.animate_delay * 1000, "swing", callback);
                if (data.fixed_position)
                    spacer.stop().animate({"height": height + "px"}, data.animate_delay * 1000);
            }
            else {
                bar.height(height);
                if (data.fixed_position)
                    spacer.height(height);
                callback();
            }

        }

        if (data.close_button) {
            spacer.on('click', '.wpfront-close', function() {
                setHeight(0, null, true);
            });
        }

        //close button action
        if (data.button_action_close_bar) {
            spacer.on('click', '.wpfront-button', function() {
                setHeight(0, null, true);
            });
        }

        if (data.display_open_button) {
            spacer.on('click', '#wpfront-notification-bar-open-button', function() {
                setHeight(height);
            });
        }

        if (data.keep_closed) {
            if (Cookies.get(keep_closed_cookie)) {
                log('Keep closed enabled and keep closed cookie exists. Hiding notification bar.');
                setHeight(0);
                return;
            }
        }

        closed = true;

        if (data.display_scroll) {
            log('Display on scroll enabled. Hiding notification bar.');
            setHeight(0);
            
            $(window).on('scroll', function() {
                if (user_closed)
                    return;

                if ($(this).scrollTop() > data.display_scroll_offset) {
                    setHeight(height);
                }
                else {
                    setHeight(0);
                }
            });
        }
        else {
            //set open after seconds and auto close seconds.
            log('Setting notification bar open event after ' + data.display_after + ' second(s).');
            setTimeout(function() {
                setHeight(height, function() {
                    if (data.auto_close_after > 0) {
                        log('Setting notification bar auto close event after ' + data.auto_close_after + ' second(s).');
                        setTimeout(function() {
                            setHeight(0, null, true);
                        }, data.auto_close_after * 1000);
                    }
                });
            }, data.display_after * 1000);
        }
    };
})();