define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko'
    ], function (Component, ko) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Bold_Checkout/payment/bold.html',
            },
            initialize: function () {
                this._super();
                this.iframeSrc = ko.observable(window.checkoutConfig.bold.payment.iframeSrc);
                this.iframeHeight = ko.observable('350px');
                this.getIframeHeight();
            },
            getIframeHeight: function () {
                const self = this;
                window.addEventListener('message', ({data}) => {
                    const newHeight = data.height ? data.height.round() + 'px' : self.iframeHeight();
                    self.iframeHeight(newHeight);
                });
            },
            placeOrder: function (data, event) {
                this._super(data, event);
            }
        });
    });
