define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Bold_Checkout/js/model/client',
        'Bold_Checkout/js/model/address',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'ko'
    ], function (Component, client, address, quote, $, ko) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Bold_Checkout/payment/bold.html',
            },

            /**
             * @inheritDoc
             */
            initialize: function () {
                this._super();
                this.isAvailable = ko.observable(false);
                this.iframeSrc = ko.observable(null);
                this.customerIsGuest = ko.observable(window.checkoutConfig.bold.customerIsGuest);
                this.errorMessage = ko.observable('Something went wrong. Please try again');
                this.iframeHeight = ko.observable('350px');
                this.sendBillingData();
                this.getIframeHeight();
            },

            /**
             * Get iframe height from Bold.
             *
             * @returns {void}
             */
            getIframeHeight: function () {
                window.addEventListener('message', ({data}) => {
                    const newHeight = data.height ? data.height.round() + 'px' : this.iframeHeight();
                    this.iframeHeight(newHeight);
                });
            },


            /**
             * Send billing data to Bold.
             *
             * @returns {void}
             * @private
             */
            sendBillingData: function () {
                if (window.checkoutConfig.bold.customerIsGuest) {
                    this.sendGuestCustomerInfo();
                }
                if (this.iframeSrc() === null) {
                    return;
                }
                client.post(address.getBillingAddress()).fail(function () {
                    this.errorMessage('Something went wrong. Please try again.');
                    this.iframeSrc(null);
                }.bind(this)).done(function () {
                    this.iframeSrc(window.checkoutConfig.bold.payment.iframeSrc);
                }.bind(this));
            },

            /**
             * Send guest customer info to Bold.
             *
             * @returns {void}
             * @private
             */
            sendGuestCustomerInfo: function () {
                const billingAddress = quote.billingAddress();
                if (this.customerIsGuest && !quote.guestEmail) {
                    this.errorMessage('Please provide your email address.');
                    this.iframeSrc(null);
                    return;
                }
                client.post(
                    'customer/guest',
                    {
                        'email_address': quote.guestEmail,
                        'first_name': billingAddress.firstname,
                        'last_name': billingAddress.lastname,
                    }
                ).fail(function () {
                    this.errorMessage('Something went wrong. Please try again.');
                    this.iframeSrc(null);
                }.bind(this));
            },

            /**
             * @inheritDoc
             */
            placeOrder: function (data, event) {
                this._super(data, event);
            },
        });
    });
