define([
    'jquery'
], function ($) {
    'use strict';

    const client = {
        /**
         * Initialize client.
         */
        initialize: function () {
            this.jwtToken = window.checkoutConfig.bold.jwtToken;
            this.url = window.checkoutConfig.bold.url;
        },

        /**
         * Post data to Bold API.
         *
         * @param path string
         * @param data object
         * @return object
         */
        post: function (path, data) {
            return $.ajax({
                url: this.url + path,
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + this.jwtToken,
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify(data)
            });
        },

        /**
         * Get data from Bold API.
         *
         * @param path string
         * @return object
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
