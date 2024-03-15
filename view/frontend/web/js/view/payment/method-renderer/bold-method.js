  define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Bold_Checkout/js/model/bold_frontend_client',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'uiRegistry',
        'underscore',
        'ko'
    ], function (
        Component,
        boldClient,
        quote,
        loader,
        registry,
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
                this._super(); //call Magento_Checkout/js/view/payment/default::initialize()
                this.subscribeToPIGI();
                this.customerIsGuest = !!Number(window.checkoutConfig.bold.customerIsGuest);
                this.awaitingRefreshBeforePlacingOrder = false;
                this.iframeSrc(window.checkoutConfig.bold.payment.iframeSrc);
                this.messageContainer.errorMessages.subscribe(function (errorMessages) {
                    if (errorMessages.length > 0) {
                        loader.stopLoader();
                    }
                });

                const sendRefreshOrder = _.debounce(
                    function () {
                        this.refreshOrder();
                    }.bind(this),
                    500
                );

                sendRefreshOrder();

                quote.billingAddress.subscribe(function () {
                    sendRefreshOrder();
                }, this);
                const email = registry.get('index = customer-email');
                if (email) {
                    email.email.subscribe(function () {
                        if (email.validateEmail()) {
                            sendRefreshOrder();
                        }
                    }.bind(this));
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
            refreshAndAddPayment: function() {
              if (this.iframeWindow) {
                const refreshAction = {actionType: 'PIGI_REFRESH_ORDER'};
                this.awaitingRefreshBeforePlacingOrder = true;
                this.iframeWindow.postMessage(refreshAction, '*');        
              }
            },

            /**
             * @inheritDoc
             */
            placeOrder: function (data, event) {
                loader.startLoader();
                if (!this.iframeWindow) {
                    return false;
                }
                if (!this.paymentType) {
                  const clearAction = {actionType: 'PIGI_CLEAR_ERROR_MESSAGES'};
                  this.iframeWindow.postMessage(clearAction, '*');
                  this.refreshAndAddPayment();
                  return false;
                }
                const originalPlaceOrder = this._super;
                this.processBoldOrder().then(() => {
                    const orderPlacementResult = originalPlaceOrder.call(this, data, event);//call Magento_Checkout/js/view/payment/default::placeOrder()
                    if (!orderPlacementResult) {
                        loader.stopLoader()
                    }
                    return orderPlacementResult;
                }).catch((error) => {
                    this.displayErrorMessage(error);
                    loader.stopLoader();
                    return false;
                });
            },
            /**
             * Refresh the order to get the recent cart updates, calculate taxes and authorize|capture payment on Bold side.
             *
             * @return {Promise<void>}
             */
            processBoldOrder: async function () {
                const refreshResult = await boldClient.get('refresh');
                const taxesResult = await boldClient.post('taxes');
                const processOrderResult = await boldClient.post('process_order');
                if (refreshResult.errors || taxesResult.errors || processOrderResult.errors) {
                    throw new Error('An error occurred while processing your payment. Please try again.');
                }
            },
            /**
             * Display error message in PIGI iframe.
             *
             * @private
             * @param {string} message
             */
            displayErrorMessage: function (message) {
                const iframeElement = document.getElementById('PIGI');
                const iframeWindow = iframeElement.contentWindow;
                const action = {
                    actionType: 'PIGI_DISPLAY_ERROR_MESSAGE',
                    payload: {
                        error: {
                            message: message,
                            sub_type: 'string_to_categorize_error',
                        }
                    }
                };
                iframeWindow.postMessage(action, '*');
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
                    const addPaymentAction = {actionType: 'PIGI_ADD_PAYMENT'};
                    if (responseType) {
                        switch (responseType) {
                            case 'PIGI_UPDATE_HEIGHT':
                                if (iframeElement.height === Math.round(data.payload.height) + 'px') {
                                    return;
                                }
                                iframeElement.height = Math.round(data.payload.height) + 'px';
                                break;
                            case 'PIGI_INITIALIZED':
                                if (data.payload && data.payload.height && iframeElement) {
                                    iframeElement.height = Math.round(data.payload.height) + 'px';
                                }
                                this.iframeWindow = iframeElement ? iframeElement.contentWindow : null;
                                break;
                            case 'PIGI_REFRESH_ORDER':
                                if(this.awaitingRefreshBeforePlacingOrder){
                                  this.iframeWindow.postMessage(addPaymentAction, '*');
                                  this.awaitingRefreshBeforePlacingOrder = false;
                                }
                                break;
                            case 'PIGI_ADD_PAYMENT':
                                this.messageContainer.errorMessages([]);
                                loader.stopLoader();
                                if (!data.payload.success) {
                                    this.paymentType = null;
                                    return;
                                }
                                this.paymentType = data.payload.paymentType;
                                this.placeOrder({}, null);
                        }
                    }
                });
            },

            /**
             * Sync Magento order with Bold.
             *
             * @private
             * @returns {void}
             */
            refreshOrder() {
                boldClient.get('refresh').then(
                    function (response) {
                        this.messageContainer.errorMessages([]);
                        if (!this.isRadioButtonVisible() && !quote.shippingMethod()) {
                            return this.selectPaymentMethod(); // some one-step checkout updates shipping lines only after payment method is selected.
                        }

                        if (
                            response &&
                            response.data &&
                            response.data.application_state &&
                            response.data.application_state.customer &&
                            response.data.application_state.customer.email_address
                        ) {
                            if (this.iframeWindow) {
                                this.iframeWindow.postMessage({actionType: 'PIGI_REFRESH_ORDER'}, '*');
                            }
                        }
                    }.bind(this)
                );
            },
        });
    });
