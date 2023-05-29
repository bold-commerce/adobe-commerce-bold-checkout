define([
    'jquery'
], function ($) {
    'use strict';

    let requestInProgress = false;
    let requestQueue = [];

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
        const nextRequest = requestQueue.shift();
        requestInProgress = true;
        $.ajax({
            url: client.url + nextRequest.path,
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + client.jwtToken,
                'Content-Type': 'application/json',
            },
            data: JSON.stringify(nextRequest.data)
        }).done(function (result) {
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
         * @param data object
         * @return {Promise}
         */
        post: function (path, data) {
            return new Promise((resolve, reject) => {
                requestQueue.push({
                    path: path,
                    data: data,
                    resolve: resolve,
                    reject: reject,
                    errorLog: path + ' Error'
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
