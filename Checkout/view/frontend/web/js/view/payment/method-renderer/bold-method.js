define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Bold_Checkout/js/model/client',
        'Bold_Checkout/js/model/address',
        'Magento_Checkout/js/model/quote',
        'underscore',
        'jquery',
        'ko'
    ], function (Component, boldClient, boldAddress, quote, _, $, ko) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Bold_Checkout/payment/bold.html',
                customerIsGuest: !!Number(window.checkoutConfig.bold.customerIsGuest),
                iframeSrc: ko.observable(null),
                iframeHeight: ko.observable('0px'),
                billingAddress: {},
                shippingAddress: {}
            },

            /**
             * @inheritDoc
             */
            initialize: function () {
                if (window.checkoutConfig.bold === undefined) {
                    return;
                }
                this._super();
                quote.billingAddress.subscribe(function (billingAddress) {
                    this.sendBillingAddress(billingAddress);
                }, this);
                quote.shippingAddress.subscribe(function (shippingAddress) {
                    this.sendShippingAddress(shippingAddress);
                }, this);
                this.iframeSrc.subscribe(function (iframeSrc) {
                    if (iframeSrc === null) {
                        return;
                    }
                    if (this.customerIsGuest) {
                        this.sendGuestCustomerInfo();
                    }
                    this.subscribeToPIGI();
                }.bind(this));
            },

            /**
             * @inheritDoc
             */
            placeOrder: function (data, event) {
                this._super(data, event);
            },

            /**
             * Send guest customer info to Bold.
             *
             * @private
             * @returns void
             */
            sendGuestCustomerInfo: function () {
                const billingAddress = quote.billingAddress();
                const shippingAddress = quote.shippingAddress();
                const firstname = billingAddress.firstname ? billingAddress.firstname : shippingAddress.firstname;
                const lastname = billingAddress.lastname ? billingAddress.lastname : shippingAddress.lastname;
                boldClient.post(
                    'customer/guest',
                    {
                        'email_address': quote.guestEmail,
                        'first_name': firstname,
                        'last_name': lastname,
                    }
                ).fail(function () {
                    this.messageContainer.errorMessages(['Something went wrong. Please try again.']);
                    this.iframeSrc(null);
                }.bind(this));
            },

            /**
             * Subscribe to PIGI events.
             *
             * @private
             * @returns {void}
             */
            subscribeToPIGI() {
                window.addEventListener('message', ({data}) => {
                    if (data.height) {
                        this.iframeHeight(data.height + 10 + 'px')
                    }
                    console.log('data', data);
                    const responseType = data.responseType;
                    if (responseType) {
                        switch (responseType) {
                            case 'PIGI_INITIALIZED':
                                break;
                            case 'PIGI_REFRESH_ORDER':
                                break;
                            case 'PIGI_ADD_PAYMENT':
                                break;
                        }
                    }
                });
            },

            /**
             * Send billing address to Bold.
             *
             * @param address object
             * @private
             * @returns {void}
             */
            sendBillingAddress(address) {
                if (this.compareAddresses(address, this.billingAddress)) {
                    return;
                }
                this.messageContainer.errorMessages([]);
                this.billingAddress = address;
                try {
                    const billingAddress = boldAddress.convertAddress(address, 'billing');
                    boldClient.post('addresses/billing', billingAddress).fail(function () {
                        this.messageContainer.errorMessages(['Something went wrong. Please try again.']);
                        this.iframeSrc(null);
                    }.bind(this)).done(function () {
                        this.iframeSrc(window.checkoutConfig.bold.payment.iframeSrc);
                    }.bind(this));
                } catch (e) {
                    this.messageContainer.errorMessages([e.message]);
                }
            },

            /**
             * Send shipping address to Bold.
             *
             * @param address object
             * @private
             * @returns {void}
             */
            sendShippingAddress(address) {
                if (this.compareAddresses(address, this.shippingAddress)) {
                    return;
                }
                this.messageContainer.errorMessages([]);
                this.shippingAddress = address;
                try {
                    const shippingAddress = boldAddress.convertAddress(address, 'shipping');
                    boldClient.post('addresses/shipping', shippingAddress).fail(function () {
                        this.messageContainer.errorMessages(['Something went wrong. Please try again.']);
                        this.iframeSrc(null);
                    }.bind(this)).done(function () {
                        this.iframeSrc(window.checkoutConfig.bold.payment.iframeSrc);
                    }.bind(this));
                } catch (e) {
                    this.messageContainer.errorMessages([e.message]);
                }
            },

            /**
             * Compare two addresses to reduce api calls.
             *
             * @param address1 object
             * @param address2 object
             * @return {boolean}
             * @private
             */
            compareAddresses(address1, address2) {
                let result = true;
                _.each(address1, function (value, key) {
                    if (address2[key] !== value) {
                        result = false;
                        return false;
                    }
                });
                return result;
            }
        });
    });
