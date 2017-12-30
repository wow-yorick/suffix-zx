/**
 * @file
 * Payment behaviors.
 */

(function ($, Drupal, window) {
  /**
   * Poll payment state every 3 seconds for QR code payment.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the QR code payment.
   */
  Drupal.behaviors.checkPaymentState = {
    attach: function (context, settings) {
      const {payment_gateway, out_trade_no, interval, timeout} = settings.polling;

      function checkState() {
        $.ajax({
          url: Drupal.url(`payment/state/${payment_gateway}/${out_trade_no}`),
          type: 'POST',
          dataType: 'json',
          success(data) {
            if (data.success) {
              clearInterval(polling);
              isTimeout = true;
              window.location.href = data.redirect;
            }
          },
        })
      }

      let polling = setInterval(checkState, +interval);
      let isTimeout = false;
      setTimeout(function () {
        clearInterval(polling);
        isTimeout = true;
      }, +timeout)

      let hidden, visibilityChange;
      if (typeof document.hidden !== 'undefined') { // Opera 12.10 and Firefox 18 and later support
        hidden = 'hidden';
        visibilityChange = 'visibilitychange';
      }
      else if (typeof document.msHidden !== 'undefined') {
        hidden = 'msHidden';
        visibilityChange = 'msvisibilitychange';
      }
      else if (typeof document.webkitHidden !== 'undefined') {
        hidden = 'webkitHidden';
        visibilityChange = 'webkitvisibilitychange';
      }

      function handleVisibilityChange() {
        if (isTimeout) {
          return;
        }
        if (document[hidden]) {
          clearInterval(polling);
        }
        else {
          clearInterval(polling);
          polling = setInterval(checkState, +interval);
        }
      }

      // Warn if the browser doesn't support addEventListener or the Page
      // Visibility API.
      if (typeof document.addEventListener === 'undefined' || typeof document[hidden] === 'undefined') {
        console.warn('The browser does not support the Page Visibility API.');
      }
      else {
        // Handle page visibility change.
        document.addEventListener(visibilityChange, handleVisibilityChange, false);
      }
    },

    detach: function (context, settings, trigger) {
    }
  };
})(jQuery, Drupal, window);
