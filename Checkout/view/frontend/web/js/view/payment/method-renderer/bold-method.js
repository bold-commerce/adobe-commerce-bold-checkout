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
                iframeHeight: ko.observable('0px'),
                isBillingAddressSynced: false,
                guestCustomerSynced: true,
                billingAddressPayload: {},
                guestCustomerPayload: {},
            },

            /**
             * @inheritDoc
             */
            initialize: function () {
                if (window.checkoutConfig.bold === undefined) {
                    return;
                }
                this._super();
                this.iframeSrc = ko.observable(
                    this.isBillingAddressSynced && this.guestCustomerSynced
                        ? window.checkoutConfig.bold.payment.iframeSrc
                        : null
                );
                this.iframeSrc.subscribe(function (iframeSrc) {
                    this.subscribeToPIGI(iframeSrc);
                }.bind(this));
                this.syncBillingData(quote.shippingAddress());
                quote.billingAddress.subscribe(function () {
                    const sendBillingAddress = _.debounce(
                        function (billingAddress) {
                            this.syncBillingData(billingAddress);
                        }.bind(this),
                        1000);
                    sendBillingAddress(quote.billingAddress());
                }, this);
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
                const firstname = billingAddress.firstname;
                const lastname = billingAddress.lastname;
                const payload = {
                    'email_address': quote.guestEmail,
                    'first_name': firstname,
                    'last_name': lastname,
                }
                if (!payload.email_address || !payload.first_name || !payload.last_name) {
                    this.guestCustomerSynced = false;
                    return;
                }
                if (this.payloadCompare(payload, this.guestCustomerPayload)) {
                    return;
                }
                boldClient.post('customer/guest', payload).then(
                    function () {
                        this.guestCustomerSynced = true;
                    }.bind(this)
                ).catch(function () {
                    this.guestCustomerSynced = false;
                }.bind(this));
            },

            /**
             * Subscribe to PIGI events.
             *
             * @private
             * @param iframeSrc string
             * @returns {void}
             */
            subscribeToPIGI(iframeSrc) {
                if (iframeSrc === null) {
                    return;
                }
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
            syncBillingData(address) {
                this.isBillingAddressSynced = false;
                const billingAddress = registry.get('index = billingAddress');
                if (billingAddress && !billingAddress.validate()) {
                    return;
                }
                const payload = boldAddress.convertAddress(address);
                if (!payload) {
                    return;
                }
                if (this.payloadCompare(payload, this.billingAddressPayload)) {
                    return;
                }
                this.billingAddressPayload = payload;
                boldClient.post('addresses/billing', payload).then(function () {
                    this.isBillingAddressSynced = true;
                    if (this.customerIsGuest) {
                        this.sendGuestCustomerInfo();
                    }
                }.bind(this)).catch(function () {
                    this.isBillingAddressSynced = false;
                }.bind(this));
            },

            /**
             * Compare two addresses to reduce api calls.
             *
             * @param newPayload object
             * @param savedPayload object
             * @return {boolean}
             * @private
             */
            payloadCompare(newPayload, savedPayload) {
                let result = true;
                _.each(newPayload, function (value, key) {
                    if (savedPayload[key] !== value) {
                        result = false;
                        return false;
                    }
                });
                return result;
            }
        });
    });
