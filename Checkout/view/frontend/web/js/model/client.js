define([
    'jquery',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage'
], function ($, urlBuilder, storage) {
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
        const url = urlBuilder.createUrl(nextRequest.path, {});
        requestInProgress = true;
        storage.post(url, JSON.stringify(nextRequest.data)).done(function (result) {
            nextRequest.resolve(result);
            requestInProgress = false;
            processNextRequest();
        }).fail(function (error) {
            nextRequest.reject(error);
            requestInProgress = false;
            processNextRequest();
        });
    }

    return {
        /**
         * Post data to server.
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
        }
    };
});
