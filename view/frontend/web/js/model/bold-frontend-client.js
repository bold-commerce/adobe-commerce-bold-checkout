define([
    'jquery',
    'underscore',
    'Bold_Checkout/js/model/address',
    'Bold_Checkout/js/model/customer'
], function (
    $,
    _,
    boldAddress,
    boldCustomer
) {
    'use strict';

    /**
     * Bold http client.
     * @type {object}
     */
    return {
        requestInProgress: false,
        requestQueue: [],

        /**
         * Post data to Bold API.
         *
         * @param path string
         * @param body object
         * @return {Promise}
         */
        post: function (path, body = {}) {
            return new Promise((resolve, reject) => {
                this.requestQueue.push({
                    resolve: resolve,
                    reject: reject,
                    path: path,
                    body: body
                });
                this.processNextRequest();
            });
        },

        /**
         * Get data from Bold API.
         *
         * @param path
         * @return {*}
         */
        get: function (path) {
            return $.ajax({
                url: window.checkoutConfig.bold.url + path,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + window.checkoutConfig.bold.jwtToken,
                    'Content-Type': 'application/json'
                }
            });
        },
        /**
         * Process next request in queue.
         *
         * @return void
         * @private
         */
        processNextRequest: function () {
            if (this.requestInProgress || this.requestQueue.length === 0) {
                return;
            }
            this.requestInProgress = true;
            const nextRequest = this.requestQueue.shift();
            let requestData;
            switch (nextRequest.path) {
                case 'addresses/billing' :
                    requestData = boldAddress.getBillingAddress();
                    if (!requestData) {
                        this.requestInProgress = false;
                        this.processNextRequest();
                        return;
                    }
                    break;
                case 'customer/guest' :
                    requestData = boldCustomer.getCustomer();
                    if (!requestData) {
                        this.requestInProgress = false;
                        this.processNextRequest();
                        return;
                    }
                    break;
                default:
                    requestData = nextRequest.body;
                    break;
            }
            $.ajax({
                url: window.checkoutConfig.bold.url + nextRequest.path,
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + window.checkoutConfig.bold.jwtToken,
                    'Content-Type': 'application/json',
                },
                data: JSON.stringify(requestData)
            }).done(function (result) {
                nextRequest.resolve(result);
                this.requestInProgress = false;
                this.processNextRequest();
            }.bind(this)).fail(function (error) {
                nextRequest.reject(error);
                this.requestInProgress = false;
                this.processNextRequest();
            }.bind(this));
        },
    }
});
