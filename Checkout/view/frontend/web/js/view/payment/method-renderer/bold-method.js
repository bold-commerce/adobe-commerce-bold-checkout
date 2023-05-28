define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Bold_Checkout/js/model/client',
        'Bold_Checkout/js/model/address',
        'Magento_Checkout/js/model/quote',
        'uiRegistry',
        'underscore',
        'ko'
    ], function (Component, boldClient, boldAddress, quote, registry, _, ko) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Bold_Checkout/payment/bold.html',
                customerIsGuest: !!Number(window.checkoutConfig.bold.customerIsGuest),
                billingAddressComponent: registry.get('index = validator'),
                iframeHeight: ko.observable('0px'),
                isBillingAddressSynced: false,
                isShippingAddressSynced: false,
                billingAddressPayload: {},
                shippingAddressPayload: {},
            },

            /**
             * @inheritDoc
             */
            initialize: function () {
                if (window.checkoutConfig.bold === undefined) {
                    return;
                }
                this._super();
                this.sendShippingAddress(quote.shippingAddress());
                this.sendBillingAddress(quote.billingAddress());
                this.iframeSrc = ko.observable(
                    this.isBillingAddressSynced && this.isShippingAddressSynced ?
                        window.checkoutConfig.bold.payment.iframeSrc
                        : null
                );
                quote.billingAddress.subscribe(function () {
                    const sendBillingAddress = _.debounce(
                        function (billingAddress) {
                            this.sendBillingAddress(billingAddress);
                        }.bind(this),
                        1000);
                    sendBillingAddress(quote.billingAddress());
                }, this);
                quote.shippingAddress.subscribe(function () {
                    const sendShippingAddress = _.debounce(
                        function (shippingAddress) {
                            this.sendShippingAddress(shippingAddress);
                        }.bind(this),
                        1000);
                    sendShippingAddress(quote.shippingAddress());
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
                ).catch(function () {
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
                                this.messageContainer.errorMessages([]);
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
                this.isBillingAddressSynced = false;
                const billingAddress = registry.get('index = billingAddress');
                if (billingAddress && !billingAddress.validate()) {
                    return;
                }
                const payload = boldAddress.convertAddress(address);
                if (!payload) {
                    return;
                }
                if (this.compareAddressesPayload(payload, this.billingAddressPayload)) {
                    return;
                }
                this.billingAddressPayload = payload;
                boldClient.post('addresses/billing', payload).then(function () {
                    this.isBillingAddressSynced = true;
                }.bind(this)).catch(function () {
                    this.isBillingAddressSynced = false;
                }.bind(this));
            },

            /**
             * Send shipping address to Bold.
             *
             * @param address object
             * @private
             * @returns {void}
             */
            sendShippingAddress(address) {
                this.isShippingAddressSynced = false;
                const shippingAddress = registry.get('index = shippingAddress');
                if (shippingAddress && !shippingAddress.validate()) {
                    return;
                }
                const payload = boldAddress.convertAddress(address);
                if (!payload) {
                    return;
                }
                if (this.compareAddressesPayload(payload, this.shippingAddressPayload)) {
                    return;
                }
                this.shippingAddressPayload = payload;
                boldClient.post('addresses/shipping', payload).then(function () {
                    this.isShippingAddressSynced = true;
                }.bind).catch(function () {
                    this.isShippingAddressSynced = false;
                }.bind(this));
            },

            /**
             * Compare two addresses to reduce api calls.
             *
             * @param address1 object
             * @param address2 object
             * @return {boolean}
             * @private
             */
            compareAddressesPayload(address1, address2) {
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
