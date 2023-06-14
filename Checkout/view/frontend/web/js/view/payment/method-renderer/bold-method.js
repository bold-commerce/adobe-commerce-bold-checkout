define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Bold_Checkout/js/model/client',
        'Bold_Checkout/js/model/address',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'uiRegistry',
        'checkoutData',
        'underscore',
        'ko'
    ], function (
        Component,
        boldClient,
        boldAddress,
        quote,
        loader,
        registry,
        checkoutData,
        _,
        ko
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Bold_Checkout/payment/bold.html',
                paymentType: null,
                iframeSrc: ko.observable(null),
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
                this.iframeSrc = ko.observable(window.checkoutConfig.bold.payment.iframeSrc);
                this.customerIsGuest = !!Number(window.checkoutConfig.bold.customerIsGuest);
                if (checkoutData.getSelectedPaymentMethod() === 'bold') {
                    checkoutData.setSelectedPaymentMethod(null);
                    quote.paymentMethod(null);
                }
                this.syncBillingData();
                quote.billingAddress.subscribe(function () {
                    const sendBillingAddress = _.debounce(
                        function () {
                            this.syncBillingData();
                        }.bind(this),
                        1000
                    );
                    sendBillingAddress();
                }, this);
                const email = registry.get('index = customer-email');
                if (email) {
                    email.email.subscribe(function () {
                        if (email.validateEmail()) {
                            const sendGuestCustomerInfo = _.debounce(
                                function () {
                                    this.sendGuestCustomerInfo();
                                }.bind(this),
                                1000
                            );
                            sendGuestCustomerInfo();
                        }
                    }.bind(this));
                }
                this.subscribeToPIGI();
            },

            /**
             * @inheritDoc
             */
            selectPaymentMethod: function () {
                const iframeElement = document.getElementById('PIGI');
                this.iframeWindow = iframeElement ? iframeElement.contentWindow : null;
                this._super();
                if (this.iframeWindow) {
                    this.iframeWindow.postMessage({actionType: 'PIGI_REFRESH_ORDER'}, '*');
                }
                return true;
            },

            /**
             * @inheritDoc
             */
            placeOrder: function (data, event) {
                if (!this.iframeWindow) {
                    return;
                }
                if (!this.paymentType) {
                    loader.startLoader();
                    const clearAction = {actionType: 'PIGI_CLEAR_ERROR_MESSAGES'};
                    const addPaymentAction = {actionType: 'PIGI_ADD_PAYMENT'};
                    this.iframeWindow.postMessage(clearAction, '*');
                    this.iframeWindow.postMessage(addPaymentAction, '*');
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
                if (!this.customerIsGuest) {
                    return;
                }
                const billingAddress = quote.billingAddress();
                const firstname = billingAddress.firstname;
                const lastname = billingAddress.lastname;
                const payload = {
                    'email_address': quote.guestEmail,
                    'first_name': firstname,
                    'last_name': lastname,
                }
                if (!payload.email_address || !payload.first_name || !payload.last_name) {
                    return;
                }
                if (this.payloadCompare(payload, this.guestCustomerPayload)) {
                    return;
                }
                this.guestCustomerPayload = payload;
                boldClient.post('customer/guest', payload).then(
                    function () {
                        if (this.iframeWindow) {
                            this.iframeWindow.postMessage({actionType: 'PIGI_REFRESH_ORDER'}, '*');
                        }
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
                    const responseType = data.responseType;
                    if (responseType) {
                        switch (responseType) {
                            case 'PIGI_UPDATE_HEIGHT':
                                const iframeElement = document.querySelector('iframe#PIGI');
                                if (iframeElement.style.height === Math.round(data.payload.height) + 'px') {
                                    return;
                                }
                                iframeElement.style.height = Math.round(data.payload.height) + 'px';
                                break;
                            case 'PIGI_INITIALIZED':
                                break;
                            case 'PIGI_REFRESH_ORDER':
                                break;
                            case 'PIGI_ADD_PAYMENT':
                                this.messageContainer.erroMessages([]);
                                if (!data.payload.success) {
                                    this.messageContainer.erroMessages(
                                        [
                                            'Please verify your payment information and try again.'
                                        ]
                                    );
                                    loader.stopLoader();
                                    this.paymentType = null;
                                    return;
                                }
                                this.paymentType = data.payload.paymentType;
                                if (this.paymentType !== 'paypal') {
                                    loader.startLoader();
                                    this.placeOrder({}, null);
                                    return;
                                }
                                this.messageContainer.successMessages(['Success']);
                        }
                    }
                });
            },

            /**
             * Send billing address to Bold.
             *
             * @private
             * @returns {void}
             */
            syncBillingData() {
                this.sendGuestCustomerInfo();
                const payload = boldAddress.getBillingAddress();
                if (!payload) {
                    return;
                }
                if (this.payloadCompare(payload, this.billingAddressPayload)) {
                    return;
                }
                this.billingAddressPayload = payload;
                boldClient.post('addresses/billing', payload).then(function () {
                    if (this.iframeWindow) {
                        this.iframeWindow.postMessage({actionType: 'PIGI_REFRESH_ORDER'}, '*');
                    }
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
