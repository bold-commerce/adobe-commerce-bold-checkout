define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Bold_Checkout/js/model/address',
    'Bold_Checkout/js/model/client',
], function ($, quote, address, client) {
    'use strict';

    const shippingData = {
        defaults: {
            shippingMethod: null,
        },

        /**
         * Subscribe to quote shipping method.
         */
        initialize: function () {
            if (window.checkoutConfig.bold === undefined) {
                return;
            }
            quote.shippingMethod.subscribe(function (shippingMethod) {
                if (this.shippingMethod === shippingMethod.method_code) {
                    return;
                }
                this.shippingMethod = shippingMethod.method_code;
                this.updateShippingAndTaxes(shippingMethod.method_code);
            }.bind(this));
        },

        /**
         * Send shipping and tax data to Bold.
         *
         * @return void
         * @private
         */
        updateShippingAndTaxes: function (shippingMethod) {
            if (!shippingMethod) {
                return;
            }
            client.get(
                'shipping_lines'
            ).done(function (response) {
                client.post(
                    'shipping_lines',
                    {'index': this.getShippingLineIndex(response)}
                );
            });
            client.post(
                'taxes',
                {}
            );
        },

        /**
         * Get shipping line index.
         *
         * @return int|null
         * @private
         */
        getShippingLineIndex: function (response) {
            if (!response.shipping_lines) {
                return null;
            }
            response.shipping_lines.forEach(function (shippingLine) {
                if (shippingLine.code === quote.shippingMethod().method_code) {
                    return shippingLine.id;
                }
            });
            return null;
        },
    };

    shippingData.initialize();
    return shippingData;
});
