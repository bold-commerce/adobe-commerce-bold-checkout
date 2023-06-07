define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Bold_Checkout/js/model/client',
        'Bold_Checkout/js/model/address',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'checkoutData',
        'uiRegistry',
        'underscore',
        'ko'
    ], function (Component, boldClient, boldAddress, quote, loader, checkoutData, registry, _, ko) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Bold_Checkout/payment/bold.html',
                customerIsGuest: !!Number(window.checkoutConfig.bold.customerIsGuest),
                iframeHeight: ko.observable('500px'),
                isBillingDataSynced: ko.observable(false),
                paymentType: null,
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
                if (checkoutData.getSelectedPaymentMethod() === 'bold') {
                    checkoutData.setSelectedPaymentMethod(null);
                    quote.paymentMethod(null);
                }
                this.iframeSrc = ko.computed(function () {
                    return this.isBillingDataSynced() ? window.checkoutConfig.bold.payment.iframeSrc : null;
                }.bind(this));
                this.syncBillingData(quote.billingAddress());
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
            selectPaymentMethod: function () {
                this.subscribeToPIGI();
                return this._super();
            },

            /**
             * @inheritDoc
             */
            placeOrder: function (data, event) {
                if (!this.paymentType) {
                    loader.startLoader();
                    const iframeElement = document.getElementById('PIGI');
                    if (!iframeElement) {
                        return;
                    }
                    const iframeWindow = iframeElement.contentWindow;
                    const clearAction = {actionType: 'PIGI_CLEAR_ERROR_MESSAGES'};
                    const action = {actionType: 'PIGI_ADD_PAYMENT'};
                    iframeWindow.postMessage(clearAction, '*');
                    iframeWindow.postMessage(action, '*');
                    return;
                }
                this.paymentType = null;
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
                    this.isBillingDataSynced(false);
                    return;
                }
                if (this.payloadCompare(payload, this.guestCustomerPayload)) {
                    return;
                }
                boldClient.post('customer/guest', payload).then(
                    function () {
                        this.isBillingDataSynced(true);
                    }.bind(this)
                ).catch(function () {
                    this.isBillingDataSynced(false);
                }.bind(this));
            },

            /**
             * Subscribe to PIGI events.
             *
             * @private
             * @returns {void}
             */
            subscribeToPIGI() {
                const iframeElement = document.getElementById('PIGI');
                if (!iframeElement) {
                    return;
                }
                const iframeWindow = iframeElement.contentWindow;
                iframeWindow.postMessage({actionType: 'PIGI_REFRESH_ORDER'}, '*');
                window.addEventListener('message', ({data}) => {
                    console.log('data', data);
                    const responseType = data.responseType;
                    if (responseType) {
                        switch (responseType) {
                            case 'PAYMENT_GATEWAY_FRAME_HEIGHT_UPDATED':
                                if (data.height) {
                                    this.iframeHeight(data.height + 'px')
                                }
                                break;
                            case 'PIGI_INITIALIZED':
                                break;
                            case 'PIGI_REFRESH_ORDER':
                                break;
                            case 'PIGI_ADD_PAYMENT':
                                loader.stopLoader();
                                if (!data.payload.success) {
                                    this.paymentType = null;
                                    return;
                                }
                                this.paymentType = data.payload.paymentType;
                                this.placeOrder();
                        }
                    }
                });
            },

            /**
             * Send billing address to Bold.
             *
             * @param address object|null
             * @private
             * @returns {void}
             */
            syncBillingData(address) {
                if (!address) {
                    return;
                }
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
                this.isBillingDataSynced(false);
                boldClient.post('addresses/billing', payload).then(function () {
                    this.isBillingDataSynced(true);
                    if (this.customerIsGuest) {
                        this.sendGuestCustomerInfo();
                    }
                }.bind(this)).catch(function () {
                    this.isBillingDataSynced(false);
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
