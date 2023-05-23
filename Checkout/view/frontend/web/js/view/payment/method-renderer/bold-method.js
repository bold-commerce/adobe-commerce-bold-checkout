define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko'
    ],
    function (Component, ko) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Bold_Checkout/payment/bold.html',
                iframeSrc: ko.observable(window.checkoutConfig.payment.bold.iframeSrc),
            },

            placeOrder: function (data, event) {
                this._super(data, event);
            }
        });
    }
);
