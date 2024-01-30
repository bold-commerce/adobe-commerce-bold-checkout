define([
    'jquery',
    'underscore',
    'Bold_Checkout/js/model/address',
    'Bold_Checkout/js/model/customer'
], function ($, _, boldAddress, boldCustomer) {
    'use strict';

    let requestInProgress = false;
    let requestQueue = [];

    /**
     * Compare two addresses to reduce api calls.
     *
     * @return {boolean}
     * @private
     * @param newPayload {object}
     * @param dataType {string}
     */
    function payloadCompare(newPayload, dataType) {
        const savedPayload = window.checkoutConfig.bold[dataType] || {};
        let result = true;
        _.each(newPayload, function (value, key) {
            if (savedPayload[key] !== value && key !== 'id') {
                result = false;
                return false;
            }
        });
        return result;
    }

    /**
     * Process next request in queue.
     *
     * @return void
     * @private
     */
    function processNextRequest() {
        if (requestInProgress || requestQueue.length === 0) {
            return;
        }
        requestInProgress = true;
        const nextRequest = requestQueue.shift();
        let newPayload = nextRequest.body;
        let shouldCompare = false;
        // address and customer should be updated only if they are changed and the most recent payload should be gotten just before request.
        // if payload is not changed, we should not send request to Bold.
        // other data types should be updated every time and payload is gotten from nextRequest object.
        switch (nextRequest.path) {
            case 'addresses/billing' :
                shouldCompare = true;
                newPayload = boldAddress.getBillingAddress();
                break;
            case 'customer/guest' :
                shouldCompare = true;
                newPayload = boldCustomer.getCustomer();
                break;
        }
        if (shouldCompare && (!newPayload || payloadCompare(newPayload, nextRequest.dataType))) {
            requestInProgress = false;
            processNextRequest();
            return;
        }
        $.ajax({
            url: client.url + nextRequest.path,
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + client.jwtToken,
                'Content-Type': 'application/json',
            },
            data: JSON.stringify(newPayload)
        }).done(function (result) {
            window.checkoutConfig.bold[nextRequest.dataType] = result.data[nextRequest.dataType];
            nextRequest.resolve(result);
            requestInProgress = false;
            processNextRequest();
        }).fail(function (error) {
            nextRequest.reject(error);
            requestInProgress = false;
            processNextRequest();
        });
    }

    /**
     * Bold http client.
     * @type {object}
     */
    const client = {
        /**
         * Initialize client.
         *
         * @return void
         */
        initialize: function () {
            if (window.checkoutConfig.bold === undefined) {
                return;
            }
            this.jwtToken = window.checkoutConfig.bold.jwtToken;
            this.url = window.checkoutConfig.bold.url;
        },

        /**
         * Post data to Bold API.
         *
         * @param path string
         * @param body object
         * @return {Promise}
         */
        post: function (path, body = {}) {
            return new Promise((resolve, reject) => {
                requestQueue.push({
                    resolve: resolve,
                    reject: reject,
                    path: path,
                    body: body
                });
                processNextRequest();
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
                url: this.url + path,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + this.jwtToken,
                    'Content-Type': 'application/json'
                }
            });
        }
    };

    client.initialize();
    return client;
});
