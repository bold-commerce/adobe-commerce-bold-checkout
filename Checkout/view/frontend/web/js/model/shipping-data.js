define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'Bold_Checkout/js/model/address',
    'Bold_Checkout/js/model/client'
], function ($, customerData, quote, address, client) {
    'use strict';

    const shippingData = {
        defaults: {
            shippingLines: [],
        },

        /**
         * Get shipping address from quote.
         */
        initialize: function () {
            quote.shippingMethod.subscribe(function () {
                this.sendShippingData();
            }.bind(this));
        },

        /**
         * Send shipping data to Bold.
         */
        sendShippingData: function () {
            const shippingAddress = address.getShippingAddress();
            let shippingLineIndex = null;
            client.post(shippingAddress).done(function () {
                this.shippingLines.forEach(function (shippingLine) {
                    if (shippingLine.code === quote.shippingMethod().method_code) {
                        shippingLineIndex = shippingLine.id;
                    }
                });
                if (chippingLineIndex !== null) {
                    client.post('shippingLine', {'index': shippingLineIndex}).done(function () {
                        client.post('taxes', {}).fail(function () {
                            window.checkoutConfig.bold.payment.iframeSrc = null;
                        });
                    });
                    return;
                }
                client.get('shipping-lines').done(function (response) {
                    this.shippingLines = response;
                    this.shippingLInes.forEach(function (shippingLine) {
                        if (shippingLine.code === quote.shippingMethod().method_code) {
                            client.post('shippingLine', {'index': shippingLine.id}).done(function () {
                                client.post('taxes', {}).fail(function () {
                                    window.checkoutConfig.bold.payment.iframeSrc = null;
                                });
                            });
                        }
                    });
                }.bind(this));
            });
        },
    };
    shippingData.initialize();
    return shippingData;
});
