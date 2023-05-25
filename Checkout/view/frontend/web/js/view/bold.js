define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data',
], function (Component,quote, customerData) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();
            /*this.observeShippingAddress();
            this.observeBillingAddress();*/
            return this;
        },
        observeShippingAddress: function () {
            quote.shippingAddress.subscribe(
                function (newAddress) {
                    // Here is where you can handle the shipping address change
                    console.log(newAddress);
                }
            );
        },

        observeBillingAddress: function () {
            const countryId = newAddress.countryId;
            const countryName = countryData()[countryId] !== undefined ? countryData()[countryId].name : '';
            if (!countryName || !countryId) {
                return;
            }
            if (quote.billingAddress === newAddress) {
                return;
            }
            $.ajax({
                url: window.checkoutConfig.bold.billingAddressUrl,
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + window.checkoutConfig.bold.jwt,
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify(
                    {
                        'business_name': newAddress.company ? newAddress.company : '',
                        'country_code': countryId,
                        'country': countryName,
                        'city': newAddress.city ? newAddress.city : '',
                        'first_name': newAddress.firstname ? newAddress.firstname : '',
                        'last_name': newAddress.lastname ? newAddress.lastname : '',
                        'phone_number': newAddress.telephone ? newAddress.telephone : '',
                        'postal_code': newAddress.postcode ? newAddress.postcode : '',
                        'province': newAddress.region ? newAddress.region : '',
                        'province_code': newAddress.regionCode ? newAddress.regionCode : '',
                        'address_line_1': newAddress.street !== undefined && newAddress.street[0] ? newAddress.street[0] : '',
                        'address_line_2': newAddress.street !== undefined && newAddress.street[1] ? newAddress.street[1] : '',
                    }
                )
            }).done(function (response) {
                console.log(response);
            }).fail(function (response) {
                console.log(response);
            });
        },
    });
});
