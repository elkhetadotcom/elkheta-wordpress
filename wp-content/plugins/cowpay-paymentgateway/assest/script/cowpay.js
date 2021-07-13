jQuery(document).ready(function () {
  var $ = jQuery;
  var finalResult;
  function sendPaymentRequest($form) {
    var $form = $(this);
    $form.addClass("processing");

    wc_checkout_form.blockOnSubmit($form);

    // Attach event to block reloading the page when the form has been submitted
    wc_checkout_form.attachUnloadEventsOnSubmit();

    // ajaxSetup is global, but we use it to ensure JSON is valid once returned.
    $.ajaxSetup({
      dataFilter: function (raw_response, dataType) {
        // We only want to work with JSON
        if ("json" !== dataType) {
          return raw_response;
        }

        if (wc_checkout_form.is_valid_json(raw_response)) {
          return raw_response;
        } else {
          // Attempt to fix the malformed JSON
          var maybe_valid_json = raw_response.match(/{"result.*}/);

          if (null === maybe_valid_json) {
            console.log("Unable to fix malformed JSON");
          } else if (wc_checkout_form.is_valid_json(maybe_valid_json[0])) {
            console.log("Fixed malformed JSON. Original:");
            raw_response = maybe_valid_json[0];
          } else {
            console.log("Unable to fix malformed JSON");
          }
        }

        return raw_response;
      },
    });

    $.ajax({
      type: "POST",
      url: wc_checkout_params.checkout_url,
      data: $form.serialize(),
      dataType: "json",
      success: function (result) {
        // Detach the unload handler that prevents a reload / redirect
        wc_checkout_form.detachUnloadEventsOnSubmit();
        finalResult = result;
        try {
          if ("success" === result.result) {
            proceedAfterSuccess(result);
          } else if ("failure" === result.result) {
            throw "Result failure";
          } else {
            throw "Invalid response";
          }
        } catch (err) {
          // Reload page
          if (true === result.reload) {
            window.location.reload();
            return;
          }

          // Trigger update in case we need a fresh nonce
          if (true === result.refresh) {
            $(document.body).trigger("update_checkout");
          }

          // Add new errors
          if (result.messages) {
            wc_checkout_form.submit_error(result.messages);
          } else {
            wc_checkout_form.submit_error(
              '<div class="woocommerce-error">' +
                wc_checkout_params.i18n_checkout_error +
                "</div>"
            ); // eslint-disable-line max-len
          }
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        // Detach the unload handler that prevents a reload / redirect
        wc_checkout_form.detachUnloadEventsOnSubmit();

        wc_checkout_form.submit_error(
          '<div class="woocommerce-error">' + errorThrown + "</div>"
        );
      },
    });

    return false;
  }

  function proceedAfterSuccess(result) {
    if (!result.otp_check || !result.cowpay_reference_id) {
      redirect(result.redirect);
      return;
    }

    COWPAYOTPDIALOG.init();
    COWPAYOTPDIALOG.load(result.cowpay_reference_id);
  }

  function redirect(url) {
    if (-1 === url.indexOf("https://") || -1 === url.indexOf("http://")) {
      window.location = url;
      return;
    }

    window.location = decodeURI(url);
  }

  var wc_checkout_form = {
    $checkout_form: $("form.checkout"),
    blockOnSubmit: function ($form) {
      var form_data = $form.data();

      if (1 !== form_data["blockUI.isBlocked"]) {
        $form.block({
          message: null,
          overlayCSS: {
            background: "#fff",
            opacity: 0.6,
          },
        });
      }
    },
    attachUnloadEventsOnSubmit: function () {
      $(window).on("beforeunload", this.handleUnloadEvent);
    },
    detachUnloadEventsOnSubmit: function () {
      $(window).unbind("beforeunload", this.handleUnloadEvent);
    },
    is_valid_json: function (raw_json) {
      try {
        var json = $.parseJSON(raw_json);

        return json && "object" === typeof json;
      } catch (e) {
        return false;
      }
    },
    submit_error: function (error_message) {
      console.log({ error_message });
      $(
        ".woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message"
      ).remove();
      wc_checkout_form.$checkout_form.prepend(
        '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' +
          error_message +
          "</div>"
      ); // eslint-disable-line max-len
      wc_checkout_form.$checkout_form.removeClass("processing").unblock();
      wc_checkout_form.$checkout_form
        .find(".input-text, select, input:checkbox")
        .trigger("validate")
        .blur();
      wc_checkout_form.scroll_to_notices();
      $(document.body).trigger("checkout_error");
    },
    scroll_to_notices: function () {
      var scrollElement = $(
        ".woocommerce-NoticeGroup-updateOrderReview, .woocommerce-NoticeGroup-checkout"
      );

      if (!scrollElement.length) {
        scrollElement = $(".form.checkout");
      }
      $.scroll_to_notices(scrollElement);
    },
  };

  wc_checkout_form.$checkout_form.on(
    "checkout_place_order_cowpay_credit_card",
    sendPaymentRequest
  );

  $(window).on("message", function (e) {
    var data = e.originalEvent.data;
    //alert(data);
    //alert(data.payment_status);
    if (!data || data.message_source !== "cowpay") {
      return;
    }
    // var paymentStatus = e.data.payment_status;
    // var cowpayReferenceId = e.data.cowpay_reference_id;
    // var gatewayReferenceId = e.data.payment_gateway_reference_id;


    //alert('before');
    // AJAX url
    var ajax_url = plugin_ajax_object.ajax_url;

    var merchant_reference_id =  finalResult.merchant_reference_id.split('_');
    // Fetch filtered records (AJAX with parameter)
    var dataAjax = {
      'payment_status': data.payment_status,
      'cowpay_reference_id': data.cowpay_reference_id,
      'merchant_reference_id' : merchant_reference_id[0]
    };

    //alert(merchant_reference_id[0]);
    jQuery.ajax({
        type: "post",
        dataType: "json",
        url: ajax_url,
        data: dataAjax,
        success: function(msg){
          //alert('in');
          console.log(msg);
          if (msg == 'PAID'){
            redirect(finalResult.redirect);
            return;
          }else if(msg == 'FAILED'){
            wc_checkout_form.submit_error(
                '<div class="woocommerce-error">THE PAYMENT WAS ' + data.payment_status + "</div>"
              );
            return;
          }else{
            console.log(msg);
          }
            
            
        },
        error: function (jqXHR, textStatus, errorThrown) {
        // Detach the unload handler that prevents a reload / redirect
        wc_checkout_form.detachUnloadEventsOnSubmit();

        wc_checkout_form.submit_error(
          '<div class="woocommerce-error">' + errorThrown + "</div>"
        );
      },

    });

    // console.log("message posted"+data.payment_status);
    // if (data.payment_status == 'PAID'){
    //   redirect(finalResult.redirect);
    //   return;
    // }else if(data.payment_status == 'FAILED'){
    //   wc_checkout_form.submit_error(
    //       '<div class="woocommerce-error">THE PAYMENT WAS ' + data.payment_status + "</div>"
    //     );
    //   return;
    // }

    
    
    // use the following fields
  });
});
