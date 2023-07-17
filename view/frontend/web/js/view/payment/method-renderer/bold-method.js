define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Bold_Checkout/js/model/client',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'uiRegistry',
        'checkoutData',
        'underscore',
        'ko'
    ], function (
        Component,
        boldClient,
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
                isVisible: ko.observable(true),
                iframeSrc: ko.observable(null),
            },

            /**
             * @inheritDoc
             */
            initialize: function () {
                if (window.checkoutConfig.bold === undefined) {
                    this.isVisible(false);
                    return;
                }
                this._super();
                this.subscribeToPIGI();
                this.customerIsGuest = !!Number(window.checkoutConfig.bold.customerIsGuest);
                this.iframeSrc(window.checkoutConfig.bold.payment.iframeSrc);
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
                if (checkoutData.getSelectedPaymentMethod() === 'bold') {
                    checkoutData.setSelectedPaymentMethod(null);
                    quote.paymentMethod(null);
                }
                if (!this.isRadioButtonVisible()) {
                    this.selectPaymentMethod();
                }
            },

            /**
             * @inheritDoc
             */
            selectPaymentMethod: function () {
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
                loader.stopLoader();
                return this._super(data, event)
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
                boldClient.post('customer').then(
                    function () {
                        this.messageContainer.errorMessages([]);
                        if (this.iframeWindow) {
                            this.iframeWindow.postMessage({actionType: 'PIGI_REFRESH_ORDER'}, '*');
                        }
                    }.bind(this)
                );
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
                    const iframeElement = document.getElementById('PIGI');
                    if (responseType) {
                        switch (responseType) {
                            case 'PIGI_UPDATE_HEIGHT':
                                if (iframeElement.style.height === Math.round(data.payload.height) + 'px') {
                                    return;
                                }
                                iframeElement.style.height = Math.round(data.payload.height) + 'px';
                                break;
                            case 'PIGI_INITIALIZED':
                                this.iframeWindow = iframeElement ? iframeElement.contentWindow : null;
                                break;
                            case 'PIGI_REFRESH_ORDER':
                                break;
                            case 'PIGI_ADD_PAYMENT':
                                this.messageContainer.errorMessages([]);
                                if (!data.payload.success) {
                                    loader.stopLoader();
                                    this.paymentType = null;
                                    return;
                                }
                                this.paymentType = data.payload.paymentType;
                                loader.startLoader();
                                this.placeOrder({}, null);
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
                boldClient.post('address').then(
                    function () {
                        this.messageContainer.errorMessages([]);
                        if (this.iframeWindow) {
                            this.iframeWindow.postMessage({actionType: 'PIGI_REFRESH_ORDER'}, '*');
                        }
                    }.bind(this)
                );
            },
        });
    });
